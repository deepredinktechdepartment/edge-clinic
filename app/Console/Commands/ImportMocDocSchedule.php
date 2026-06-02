<?php

namespace App\Console\Commands;

use App\Http\Controllers\MocDocController;
use App\Models\Doctor;
use App\Models\DoctorNonPracticeDay;
use App\Models\DoctorSession;
use App\Models\DoctorSlotSetting;
use App\Models\DoctorTimeSlot;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportMocDocSchedule extends Command
{
    protected $signature = 'mocdoc:import-schedule
        {--entity=jv-medi-clinic : MocDoc entity key}
        {--location=location1 : MocDoc entity location}
        {--days=21 : Number of days to sample from MocDoc calendar}
        {--advance-days=120 : Advance booking days to store in slot settings}
        {--doctor=* : Import only these drKey values}
        {--throttle-ms=1200 : Delay between MocDoc calendar requests in milliseconds}
        {--overwrite : Replace existing doctor slot configuration}
        {--dry-run : Preview imported data without writing}';

    protected $description = 'One-time import of MocDoc doctors and slot calendars into the new doctor configuration tables';

    private const DB_DAY_MAP = [6 => 0, 0 => 1, 1 => 2, 2 => 3, 3 => 4, 4 => 5, 5 => 6];

    public function handle(): int
    {
        $entityKey = (string) $this->option('entity');
        $location = (string) $this->option('location');
        $sampleDays = max((int) $this->option('days'), 1);
        $advanceDays = min(max((int) $this->option('advance-days'), 0), 365);
        $throttleMs = max((int) $this->option('throttle-ms'), 0);
        $requestedDoctors = collect((array) $this->option('doctor'))
            ->filter(fn ($value) => is_string($value) && trim($value) !== '')
            ->map(fn ($value) => trim($value))
            ->values();
        $overwrite = (bool) $this->option('overwrite');
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Fetching doctors from MocDoc...');

        $apiDoctors = collect($this->fetchDoctors($entityKey, $location));

        if ($apiDoctors->isEmpty()) {
            $this->error('No doctors were returned from MocDoc.');
            return self::FAILURE;
        }

        if ($requestedDoctors->isNotEmpty()) {
            $apiDoctors = $apiDoctors->filter(
                fn (array $doctor) => $requestedDoctors->contains((string) ($doctor['drkey'] ?? ''))
            )->values();
        }

        if ($apiDoctors->isEmpty()) {
            $this->warn('No matching doctors found for the requested drKey filters.');
            return self::SUCCESS;
        }

        $startDate = Carbon::today();
        $endDate = Carbon::today()->copy()->addDays($sampleDays - 1);

        $this->line(sprintf(
            'Sampling calendars from %s to %s for %d doctor(s)%s.',
            $startDate->toDateString(),
            $endDate->toDateString(),
            $apiDoctors->count(),
            $dryRun ? ' in dry-run mode' : ''
        ));

        $rows = [];
        $writtenDoctors = 0;

        foreach ($apiDoctors as $apiDoctor) {
            $drKey = (string) ($apiDoctor['drkey'] ?? '');

            if ($drKey === '') {
                continue;
            }

            $doctor = $this->upsertDoctor($apiDoctor, $dryRun);
            try {
                $calendar = $this->fetchCalendar($entityKey, $drKey, $startDate, $endDate, $throttleMs);
            } catch (\Throwable $e) {
                $this->warn("Skipping {$drKey}: {$e->getMessage()}");

                $rows[] = [
                    'doctor' => Str::limit($doctor->name, 28),
                    'drKey' => $drKey,
                    'slot_duration' => '-',
                    'sessions' => 0,
                    'weekly_slots' => 0,
                    'exceptions' => 0,
                    'status' => 'calendar-error',
                ];

                continue;
            }

            $calendarSlots = $this->extractCalendarSlots($calendar, $location);

            if ($calendarSlots === []) {
                $this->warn("No calendar slots returned for {$drKey}; doctor row synced, configuration skipped.");

                $rows[] = [
                    'doctor' => $doctor->name,
                    'drKey' => $drKey,
                    'slot_duration' => '-',
                    'sessions' => 0,
                    'weekly_slots' => 0,
                    'exceptions' => 0,
                    'status' => 'no-calendar',
                ];

                continue;
            }

            $compiled = $this->compileSchedule($calendarSlots);

            if (! $dryRun) {
                $this->persistSchedule($doctor->id, $compiled, $advanceDays, $overwrite);
                $writtenDoctors++;
            }

            $rows[] = [
                'doctor' => Str::limit($doctor->name, 28),
                'drKey' => $drKey,
                'slot_duration' => $compiled['slot_duration'],
                'sessions' => count($compiled['sessions']),
                'weekly_slots' => count($compiled['slot_rows']),
                'exceptions' => count($compiled['non_practice_days']),
                'status' => $dryRun ? 'preview' : ($overwrite ? 'imported' : 'upserted'),
            ];
        }

        $this->table(
            ['Doctor', 'drKey', 'Slot Min', 'Sessions', 'Weekly Slots', 'Exceptions', 'Status'],
            $rows
        );

        $this->info(sprintf(
            '%s completed for %d doctor(s).',
            $dryRun ? 'Dry run' : 'Import',
            $dryRun ? count($rows) : $writtenDoctors
        ));

        return self::SUCCESS;
    }

    private function fetchDoctors(string $entityKey, string $location): array
    {
        $controller = app(MocDocController::class);
        $url = 'https://mocdoc.com/api/get/dr/' . $entityKey;
        $body = http_build_query([
            'entitylocation' => $location,
        ]);
        $headers = $controller->mocdocHmacHeaders($url, 'POST');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $rawResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $response = [
            'status' => $httpCode,
            'data' => json_decode((string) $rawResponse, true),
        ];

        if (($response['status'] ?? 0) !== 200) {
            throw new \RuntimeException('MocDoc doctor API returned HTTP ' . ($response['status'] ?? 'unknown') . '.');
        }

        $payload = $response['data'] ?? [];

        if ((string) ($payload['status'] ?? '') !== '200') {
            throw new \RuntimeException((string) ($payload['message'] ?? 'MocDoc doctor API error.'));
        }

        return (array) ($payload['dr'] ?? []);
    }

    private function fetchCalendar(string $entityKey, string $drKey, Carbon $startDate, Carbon $endDate, int $throttleMs = 0): array
    {
        $mergedSlots = [];
        $windowStart = $startDate->copy();

        while ($windowStart->lte($endDate)) {
            $windowEnd = $windowStart->copy()->addDays(4);
            if ($windowEnd->gt($endDate)) {
                $windowEnd = $endDate->copy();
            }

            $payload = $this->fetchCalendarWindow($entityKey, $drKey, $windowStart, $windowEnd, $throttleMs);
            $slotSets = (array) data_get($payload, 'slots', []);

            foreach ($slotSets as $location => $dateMap) {
                foreach ((array) $dateMap as $dateKey => $slots) {
                    $mergedSlots[$location][$dateKey] = array_values(array_unique((array) $slots));
                }
            }

            $windowStart = $windowEnd->copy()->addDay();
        }

        return ['slots' => $mergedSlots];
    }

    private function fetchCalendarWindow(string $entityKey, string $drKey, Carbon $startDate, Carbon $endDate, int $throttleMs = 0): array
    {
        $url = 'https://mocdoc.com/api/calendar/' . $entityKey;
        $body = http_build_query([
            'entitykey' => $entityKey,
            'drkey' => $drKey,
            'startdate' => $startDate->format('Ymd'),
            'enddate' => $endDate->format('Ymd'),
        ]);

        $controller = app(MocDocController::class);
        $headers = $controller->mocdocHmacHeaders($url, 'POST');
        $attempts = 0;

        while ($attempts < 4) {
            if ($throttleMs > 0) {
                usleep($throttleMs * 1000);
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $decoded = json_decode((string) $response, true);

                if (! is_array($decoded)) {
                    throw new \RuntimeException("MocDoc calendar payload could not be parsed for {$drKey}.");
                }

                if ((string) ($decoded['status'] ?? '') !== '200') {
                    throw new \RuntimeException((string) ($decoded['message'] ?? "MocDoc calendar API error for {$drKey}."));
                }

                return $decoded;
            }

            $attempts++;

            if ($httpCode !== 429) {
                throw new \RuntimeException("MocDoc calendar API returned HTTP {$httpCode} for {$drKey}.");
            }

            usleep(2000000);
        }

        throw new \RuntimeException("MocDoc calendar API kept returning HTTP 429 for {$drKey}.");
    }

    private function extractCalendarSlots(array $calendar, string $location): array
    {
        return (array) data_get($calendar, "data.slots.{$location}", data_get($calendar, "slots.{$location}", []));
    }

    private function upsertDoctor(array $apiDoctor, bool $dryRun): Doctor
    {
        $drKey = (string) ($apiDoctor['drkey'] ?? '');
        $name = trim((string) ($apiDoctor['name'] ?? ''));

        $doctor = Doctor::where('drKey', $drKey)->first();

        $attributes = [
            'name' => $name !== '' ? $name : ($doctor->name ?? 'MocDoc Doctor'),
            'qualification' => (string) ($apiDoctor['ug_degree'] ?? ''),
            'expertise' => implode(', ', (array) ($apiDoctor['speciality'] ?? [])),
            'sync_status' => $doctor ? 'Synced' : 'MocDoc_only',
            'is_active' => ! ((bool) ($apiDoctor['blocked'] ?? false)),
            'updated_at' => now(),
        ];

        if ($doctor) {
            if (! $dryRun) {
                DB::table('doctors')
                    ->where('id', $doctor->id)
                    ->update($attributes);

                $doctor = Doctor::find($doctor->id) ?? $doctor;
            } else {
                foreach ($attributes as $key => $value) {
                    $doctor->{$key} = $value;
                }
            }

            return $doctor;
        }

        $attributes['drKey'] = $drKey;
        $attributes['slug'] = Str::slug($attributes['name']) . '-' . Str::lower(Str::random(6));
        $attributes['created_at'] = now();

        if ($dryRun) {
            $doctor = new Doctor($attributes);
            $doctor->id = 0;
            $doctor->drKey = $drKey;
            return $doctor;
        }

        $doctorId = DB::table('doctors')->insertGetId($attributes);

        return Doctor::findOrFail($doctorId);
    }

    private function compileSchedule(array $calendarSlots): array
    {
        $occurrencesByDay = [];
        $allTimesByDate = [];

        foreach ($calendarSlots as $dateKey => $rawSlots) {
            $date = Carbon::createFromFormat('Ymd', (string) $dateKey);
            $dbDay = self::DB_DAY_MAP[$date->dayOfWeek];

            $times = collect((array) $rawSlots)
                ->filter(fn ($slot) => is_string($slot) && $slot !== '' && strtolower($slot) !== 'weeklyoff')
                ->map(fn ($slot) => Carbon::parse($slot)->format('H:i'))
                ->unique()
                ->sort()
                ->values()
                ->all();

            $isWeeklyOff = in_array('weeklyoff', array_map('strtolower', (array) $rawSlots), true) || $times === [];

            $occurrencesByDay[$dbDay][] = [
                'date' => $date->toDateString(),
                'times' => $times,
                'is_weekly_off' => $isWeeklyOff,
            ];

            if ($times !== []) {
                $allTimesByDate[$date->toDateString()] = $times;
            }
        }

        $slotDuration = $this->detectSlotDuration($allTimesByDate);
        $weeklyTemplates = $this->buildWeeklyTemplates($occurrencesByDay);
        $sessions = $this->buildSessionsFromOccurrences($occurrencesByDay, $slotDuration);
        $slotRows = $this->buildSlotRows($weeklyTemplates);
        $nonPracticeDays = $this->buildExceptionDays($occurrencesByDay, $weeklyTemplates);

        return [
            'slot_duration' => $slotDuration,
            'sessions' => $sessions,
            'slot_rows' => $slotRows,
            'non_practice_days' => $nonPracticeDays,
        ];
    }

    private function detectSlotDuration(array $allTimesByDate): int
    {
        $diffs = [];

        foreach ($allTimesByDate as $times) {
            for ($i = 1; $i < count($times); $i++) {
                $prev = Carbon::createFromFormat('H:i', $times[$i - 1]);
                $curr = Carbon::createFromFormat('H:i', $times[$i]);
                $diff = $prev->diffInMinutes($curr);

                if ($diff > 0 && $diff <= 120) {
                    $diffs[] = $diff;
                }
            }
        }

        if ($diffs === []) {
            return 15;
        }

        $counts = array_count_values($diffs);
        arsort($counts);

        return (int) array_key_first($counts);
    }

    private function buildWeeklyTemplates(array $occurrencesByDay): array
    {
        $templates = [];

        foreach ($occurrencesByDay as $day => $occurrences) {
            $patterns = [];

            foreach ($occurrences as $occurrence) {
                $key = $occurrence['is_weekly_off']
                    ? '__WEEKLY_OFF__'
                    : implode('|', $occurrence['times']);

                $patterns[$key] = ($patterns[$key] ?? 0) + 1;
            }

            arsort($patterns);
            $selectedKey = (string) array_key_first($patterns);

            $templates[$day] = [
                'is_weekly_off' => $selectedKey === '__WEEKLY_OFF__',
                'times' => $selectedKey === '__WEEKLY_OFF__' ? [] : array_values(array_filter(explode('|', $selectedKey))),
            ];
        }

        return $templates;
    }

    private function buildSessionsFromOccurrences(array $occurrencesByDay, int $slotDuration): array
    {
        $blocks = [];

        foreach ($occurrencesByDay as $occurrences) {
            foreach ($occurrences as $occurrence) {
                foreach ($this->splitIntoBlocks($occurrence['times'], $slotDuration) as $block) {
                    $key = implode('|', [$block['session_type'], $block['start_time'], $block['end_time']]);
                    $blocks[$key] = ($blocks[$key] ?? 0) + 1;
                }
            }
        }

        if ($blocks === []) {
            return [];
        }

        $sessions = [];
        foreach ($blocks as $key => $count) {
            if ($count < 2 && count($blocks) > 1) {
                continue;
            }

            [$sessionType, $startTime, $endTime] = explode('|', $key);
            $sessions[$key] = [
                'session_type' => $sessionType,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'break_enabled' => false,
                'break_start' => null,
                'break_end' => null,
            ];
        }

        if ($sessions === []) {
            foreach (array_keys($blocks) as $key) {
                [$sessionType, $startTime, $endTime] = explode('|', $key);
                $sessions[$key] = [
                    'session_type' => $sessionType,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'break_enabled' => false,
                    'break_start' => null,
                    'break_end' => null,
                ];
            }
        }

        uasort($sessions, fn ($a, $b) => strcmp($a['start_time'], $b['start_time']));

        return array_values($sessions);
    }

    private function splitIntoBlocks(array $times, int $slotDuration): array
    {
        if ($times === []) {
            return [];
        }

        $blocks = [];
        $current = [$times[0]];

        for ($i = 1; $i < count($times); $i++) {
            $prev = Carbon::createFromFormat('H:i', $times[$i - 1]);
            $curr = Carbon::createFromFormat('H:i', $times[$i]);

            if ($prev->diffInMinutes($curr) === $slotDuration) {
                $current[] = $times[$i];
                continue;
            }

            $blocks[] = $this->makeBlock($current, $slotDuration);
            $current = [$times[$i]];
        }

        $blocks[] = $this->makeBlock($current, $slotDuration);

        return $blocks;
    }

    private function makeBlock(array $times, int $slotDuration): array
    {
        $start = Carbon::createFromFormat('H:i', $times[0]);
        $end = Carbon::createFromFormat('H:i', $times[count($times) - 1])->addMinutes($slotDuration);

        return [
            'session_type' => $this->classifySessionType($start),
            'start_time' => $start->format('H:i:s'),
            'end_time' => $end->format('H:i:s'),
        ];
    }

    private function buildSlotRows(array $weeklyTemplates): array
    {
        $rows = [];

        foreach ($weeklyTemplates as $day => $template) {
            if ($template['is_weekly_off']) {
                $rows[] = [
                    'day_of_week' => $day,
                    'slot_time' => '00:00:00',
                    'session_type' => 'morning',
                    'is_reserved' => false,
                    'is_weekly_off' => true,
                ];
                continue;
            }

            foreach ($template['times'] as $time) {
                $rows[] = [
                    'day_of_week' => $day,
                    'slot_time' => Carbon::createFromFormat('H:i', $time)->format('H:i:s'),
                    'session_type' => $this->classifySessionType(Carbon::createFromFormat('H:i', $time)),
                    'is_reserved' => false,
                    'is_weekly_off' => false,
                ];
            }
        }

        return $rows;
    }

    private function buildExceptionDays(array $occurrencesByDay, array $weeklyTemplates): array
    {
        $rows = [];

        foreach ($occurrencesByDay as $day => $occurrences) {
            $template = $weeklyTemplates[$day] ?? ['is_weekly_off' => false, 'times' => []];

            foreach ($occurrences as $occurrence) {
                $currentKey = $occurrence['is_weekly_off']
                    ? '__WEEKLY_OFF__'
                    : implode('|', $occurrence['times']);
                $templateKey = $template['is_weekly_off']
                    ? '__WEEKLY_OFF__'
                    : implode('|', $template['times']);

                if ($currentKey === $templateKey) {
                    continue;
                }

                $rows[$occurrence['date']] = [
                    'marked_date' => $occurrence['date'],
                    'type' => 'non_practice',
                ];
            }
        }

        ksort($rows);

        return array_values($rows);
    }

    private function classifySessionType(Carbon $time): string
    {
        $hour = (int) $time->format('H');

        if ($hour < 12) {
            return 'morning';
        }

        if ($hour < 16) {
            return 'afternoon';
        }

        if ($hour < 20) {
            return 'evening';
        }

        return 'night';
    }

    private function persistSchedule(int $doctorId, array $compiled, int $advanceDays, bool $overwrite): void
    {
        DB::transaction(function () use ($doctorId, $compiled, $advanceDays, $overwrite) {
            if ($overwrite) {
                DoctorSession::where('doctor_id', $doctorId)->delete();
                DoctorTimeSlot::where('doctor_id', $doctorId)->delete();
                DoctorNonPracticeDay::where('doctor_id', $doctorId)->delete();
            }

            DoctorSlotSetting::updateOrCreate(
                ['doctor_id' => $doctorId],
                [
                    'slot_duration' => $compiled['slot_duration'],
                    'advance_booking_days' => $advanceDays,
                    'slots_private' => false,
                ]
            );

            if ($overwrite || ! DoctorSession::where('doctor_id', $doctorId)->exists()) {
                foreach ($compiled['sessions'] as $session) {
                    DoctorSession::create(array_merge($session, ['doctor_id' => $doctorId]));
                }
            }

            if ($overwrite || ! DoctorTimeSlot::where('doctor_id', $doctorId)->exists()) {
                foreach ($compiled['slot_rows'] as $slotRow) {
                    DoctorTimeSlot::create(array_merge($slotRow, ['doctor_id' => $doctorId]));
                }
            }

            if ($overwrite || ! DoctorNonPracticeDay::where('doctor_id', $doctorId)->exists()) {
                foreach ($compiled['non_practice_days'] as $day) {
                    DoctorNonPracticeDay::create([
                        'doctor_id' => $doctorId,
                        'marked_date' => $day['marked_date'],
                        'type' => $day['type'],
                    ]);
                }
            }
        });
    }
}
