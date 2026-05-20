<?php

namespace App\Services;

use App\Models\Payment;
use Carbon\Carbon;

class FollowupEligibilityService
{
    public function check(?int $doctorId, ?int $patientId, int $followupDays): array
    {
        $result = [
            'eligible' => false,
            'last_visit' => null,
            'valid_till' => null,
            'followup_count' => 0,
            'last_followup' => null,
            'main_visit_id' => null,
        ];

        if (! $doctorId || ! $patientId || $followupDays <= 0) {
            return $result;
        }

        $visitedStatuses = ['Checked-In', 'In-Consultation', 'Checked-Out', 'Completed'];

        $lastMainVisit = Payment::query()
            ->where('doctor_id', $doctorId)
            ->where('patient_id', $patientId)
            ->where('type', 'appointment')
            ->where('is_followup', 0)
            ->whereIn('appointment_status', $visitedStatuses)
            ->orderByDesc('aptDate')
            ->orderByDesc('id')
            ->first();

        if (! $lastMainVisit || empty($lastMainVisit->aptDate)) {
            return $result;
        }

        $mainVisitDate = Carbon::createFromFormat('Ymd', $lastMainVisit->aptDate);
        $validTill = $mainVisitDate->copy()->addDays($followupDays);

        $followups = Payment::query()
            ->where('doctor_id', $doctorId)
            ->where('patient_id', $patientId)
            ->where('type', 'appointment')
            ->where('is_followup', 1)
            ->where('main_visit_id', $lastMainVisit->id)
            ->whereIn('appointment_status', $visitedStatuses)
            ->orderBy('aptDate')
            ->orderBy('id')
            ->get();

        $result['last_visit'] = $mainVisitDate->format('d M Y');
        $result['valid_till'] = $validTill->format('d M Y');
        $result['followup_count'] = $followups->count();
        $result['main_visit_id'] = $lastMainVisit->id;

        if ($result['followup_count'] > 0) {
            $lastFollowup = $followups->last();

            if (! empty($lastFollowup->aptDate)) {
                $result['last_followup'] = Carbon::createFromFormat('Ymd', $lastFollowup->aptDate)
                    ->format('d M Y');
            }
        }

        $result['eligible'] = Carbon::today()->lessThanOrEqualTo($validTill);

        return $result;
    }
}
