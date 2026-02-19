<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\RegistrationFee;
use Carbon\Carbon;

class RegistrationFeeService
{
    public function check(?int $patientId): array
    {
        /*
        |------------------------------------------
        | RULE 1: EXISTING VALID REGISTRATION
        |------------------------------------------
        */
        if ($patientId) {
            $existing = Patient::where('id', $patientId)
                ->whereNotNull('registration_valid_till')
                ->whereDate('registration_valid_till', '>=', now())
                ->first();

            if ($existing) {
                return [
                    'apply'      => false,
                    'amount'     => 0,
                    'valid_till' => Carbon::parse($existing->registration_valid_till)
                        ->format('d M Y'),
                ];
            }
        }

        /*
        |------------------------------------------
        | RULE 2: APPLY REGISTRATION FEE
        |------------------------------------------
        */
        $config = RegistrationFee::where('is_active', 1)->first();

        if (!$config) {
            return [
                'apply'      => false,
                'amount'     => 0,
                'valid_till' => null,
            ];
        }

        return [
            'apply'      => true,
            'amount'     => (float) $config->amount,
            'valid_till' => now()
                ->addDays($config->validity_days)
                ->format('d M Y'),
        ];
    }
}
