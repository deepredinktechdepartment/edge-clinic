<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\DoctorSession;
use App\Models\DoctorSlotSetting;
use App\Models\DoctorTimeSlot;
use App\Models\DoctorNonPracticeDay;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AppointmentConfigController extends Controller
{
    /**
     * Show the appointment configuration page.
     */
    public function index(Request $request)
    {
        // Join departments so we can show dept name alongside doctor
        $doctors = Doctor::select(
                'doctors.id',
                'doctors.name',
                'doctors.designation',
                'doctors.photo',
                'departments.dept_name as department_name'
            )
            ->leftJoin('departments', 'departments.id', '=', 'doctors.department_id')
            ->where('doctors.is_active', 1)
            ->orderBy('doctors.name')
            ->get();

        if ($doctors->isEmpty()) {
            return redirect()->back()->with('error', 'No active doctors found. Please add doctors first.');
        }

        $selectedDoctorId = $request->query('doctor_id', $doctors->first()->id);
        $doctor           = $doctors->firstWhere('id', $selectedDoctorId) ?? $doctors->first();

        $pageTitle = "Appointment Configuaration";

        return view('appointment.config', compact('doctors', 'doctor','pageTitle'));
    }

    /**
     * Load all configuration data for a specific doctor (AJAX).
     */
    public function loadConfig(int $doctorId): JsonResponse
    {
        $doctor = Doctor::select(
                'doctors.id',
                'doctors.name',
                'doctors.designation',
                'departments.dept_name as department_name'
            )
            ->leftJoin('departments', 'departments.id', '=', 'doctors.department_id')
            ->where('doctors.id', $doctorId)
            ->firstOrFail();

        $sessions = DoctorSession::where('doctor_id', $doctorId)
            ->orderBy('start_time')
            ->get()
            ->map(function ($s) {
                return [
                    'id'                  => $s->id,
                    'session_type'        => $s->session_type,
                    'start_time'          => $s->start_time,
                    'end_time'            => $s->end_time,
                    'start_minutes'       => $s->start_minutes,
                    'end_minutes'         => $s->end_minutes,
                    'break_enabled'       => $s->break_enabled,
                    'break_start'         => $s->break_start,
                    'break_end'           => $s->break_end,
                    'break_start_minutes' => $s->break_start_minutes,
                    'break_end_minutes'   => $s->break_end_minutes,
                ];
            });

        $settings = DoctorSlotSetting::where('doctor_id', $doctorId)->first();

        // Group slots by day_of_week
        $slotRows = DoctorTimeSlot::where('doctor_id', $doctorId)
            ->orderBy('day_of_week')
            ->orderBy('slot_time')
            ->get();

        $slots = [];
        foreach ($slotRows as $row) {
            $slots[$row->day_of_week][] = [
                'id'            => $row->id,
                'slot_time'     => substr($row->slot_time, 0, 5),
                'session_type'  => $row->session_type,
                'is_reserved'   => $row->is_reserved,
                'is_weekly_off' => $row->is_weekly_off,
            ];
        }

        // Weekly off flags per day (one flag per day index)
        $weeklyOff = $slotRows->groupBy('day_of_week')
            ->map(fn($rows) => (bool) $rows->first()->is_weekly_off);

        $nonPracticeDays = DoctorNonPracticeDay::where('doctor_id', $doctorId)
            ->get()
            ->mapWithKeys(fn($row) => [$row->marked_date->format('Y-m-d') => $row->type]);

        return response()->json([
            'doctor' => [
                'id'          => $doctor->id,
                'name'        => $doctor->name,
                'designation' => $doctor->designation ?? '',
                'department'  => $doctor->department_name ?? '',
            ],
            'sessions'          => $sessions,
            'settings'          => $settings
                ? $settings->only(['slot_duration', 'advance_booking_days', 'slots_private'])
                : ['slot_duration' => 15, 'advance_booking_days' => 30, 'slots_private' => false],
            'slots'             => $slots,
            'weekly_off'        => $weeklyOff,
            'non_practice_days' => $nonPracticeDays,
        ]);
    }

    /**
     * Save everything in one transaction.
     */
    public function saveConfig(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'doctor_id'                 => 'required|exists:doctors,id',
            'slot_duration'             => 'required|integer|min:5|max:120',
            'advance_booking_days'      => 'required|integer|min:0|max:365',
            'slots_private'             => 'boolean',
            'sessions'                  => 'array',
            'sessions.*.session_type'   => ['required', Rule::in(['morning', 'afternoon', 'evening', 'night'])],
            'sessions.*.start_time'     => 'required|date_format:H:i',
            'sessions.*.end_time'       => 'required|date_format:H:i',
            'sessions.*.break_enabled'  => 'boolean',
            'sessions.*.break_start'    => 'nullable|date_format:H:i',
            'sessions.*.break_end'      => 'nullable|date_format:H:i',
            'slots'                     => 'array',
            'slots.*'                   => 'array',
            'slots.*.*.slot_time'       => 'required|date_format:H:i',
            'slots.*.*.session_type'    => ['required', Rule::in(['morning', 'afternoon', 'evening', 'night'])],
            'slots.*.*.is_reserved'     => 'boolean',
            'slots.*.*.is_weekly_off'   => 'boolean',
            'non_practice_days'         => 'array',
            'non_practice_days.*'       => ['required', Rule::in(['holiday', 'non_practice'])],
        ]);

        $doctorId = $validated['doctor_id'];

        DB::transaction(function () use ($validated, $doctorId) {

            // 1. Slot settings
            DoctorSlotSetting::updateOrCreate(
                ['doctor_id' => $doctorId],
                [
                    'slot_duration'        => $validated['slot_duration'],
                    'advance_booking_days' => $validated['advance_booking_days'],
                    'slots_private'        => $validated['slots_private'] ?? false,
                ]
            );

            // 2. Sessions — full replace
            DoctorSession::where('doctor_id', $doctorId)->delete();
            foreach ($validated['sessions'] ?? [] as $sess) {
                DoctorSession::create([
                    'doctor_id'     => $doctorId,
                    'session_type'  => $sess['session_type'],
                    'start_time'    => $sess['start_time'],
                    'end_time'      => $sess['end_time'],
                    'break_enabled' => $sess['break_enabled'] ?? false,
                    'break_start'   => ($sess['break_enabled'] ?? false) ? ($sess['break_start'] ?? null) : null,
                    'break_end'     => ($sess['break_enabled'] ?? false) ? ($sess['break_end'] ?? null) : null,
                ]);
            }

            // 3. Time slots — full replace
            DoctorTimeSlot::where('doctor_id', $doctorId)->delete();
            foreach ($validated['slots'] ?? [] as $dayIdx => $daySlots) {
                foreach ($daySlots as $slot) {
                    DoctorTimeSlot::create([
                        'doctor_id'     => $doctorId,
                        'day_of_week'   => $dayIdx,
                        'slot_time'     => $slot['slot_time'],
                        'session_type'  => $slot['session_type'],
                        'is_reserved'   => $slot['is_reserved']   ?? false,
                        'is_weekly_off' => $slot['is_weekly_off'] ?? false,
                    ]);
                }
            }

            // 4. Non-practice days — full replace
            DoctorNonPracticeDay::where('doctor_id', $doctorId)->delete();
            foreach ($validated['non_practice_days'] ?? [] as $date => $type) {
                DoctorNonPracticeDay::create([
                    'doctor_id'   => $doctorId,
                    'marked_date' => $date,
                    'type'        => $type,
                ]);
            }
        });

        return response()->json(['message' => 'Configuration saved successfully!']);
    }

    /**
     * Delete a single slot (AJAX).
     */
    public function deleteSlot(int $slotId): JsonResponse
    {
        DoctorTimeSlot::findOrFail($slotId)->delete();
        return response()->json(['message' => 'Slot deleted.']);
    }

    /**
     * Toggle reserved status of a slot (AJAX).
     */
    public function toggleReserved(int $slotId): JsonResponse
    {
        $slot = DoctorTimeSlot::findOrFail($slotId);
        $slot->update(['is_reserved' => !$slot->is_reserved]);
        return response()->json(['is_reserved' => $slot->is_reserved]);
    }

    /**
     * Toggle weekly off for a day (AJAX).
     */
    public function toggleWeeklyOff(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'doctor_id'   => 'required|exists:doctors,id',
            'day_of_week' => 'required|integer|min:0|max:6',
            'is_off'      => 'required|boolean',
        ]);

        DoctorTimeSlot::where('doctor_id', $validated['doctor_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->update(['is_weekly_off' => $validated['is_off']]);

        return response()->json(['message' => 'Weekly off updated.']);
    }

    /**
     * Add override slot for a specific day (AJAX).
     */
    public function addOverrideSlot(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'doctor_id'   => 'required|exists:doctors,id',
            'day_of_week' => 'required|integer|min:0|max:6',
            'slot_time'   => 'required|date_format:H:i',
        ]);

        $slot = DoctorTimeSlot::create([
            'doctor_id'     => $validated['doctor_id'],
            'day_of_week'   => $validated['day_of_week'],
            'slot_time'     => $validated['slot_time'],
            'session_type'  => 'morning',
            'is_reserved'   => false,
            'is_weekly_off' => false,
        ]);

        return response()->json(['id' => $slot->id, 'slot_time' => substr($slot->slot_time, 0, 5)]);
    }

    /**
     * Toggle non-practice day: none → holiday → non_practice → delete.
     */
    public function toggleNonPracticeDay(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'date'      => 'required|date_format:Y-m-d',
        ]);

        $existing = DoctorNonPracticeDay::where('doctor_id', $validated['doctor_id'])
            ->where('marked_date', $validated['date'])
            ->first();

        if (!$existing) {
            DoctorNonPracticeDay::create([
                'doctor_id'   => $validated['doctor_id'],
                'marked_date' => $validated['date'],
                'type'        => 'holiday',
            ]);
            return response()->json(['type' => 'holiday']);
        } elseif ($existing->type === 'holiday') {
            $existing->update(['type' => 'non_practice']);
            return response()->json(['type' => 'non_practice']);
        } else {
            $existing->delete();
            return response()->json(['type' => null]);
        }
    }
}
