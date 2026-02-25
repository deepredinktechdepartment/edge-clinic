<?php

namespace App\Services;

use App\Models\Doctor;
use App\Http\Controllers\MocDocController;

class DoctorSyncService
{
    protected $entityKey = "jv-medi-clinic";

    public function compareDoctors(): array
    {
        $mocdocController = app(MocDocController::class);
        $response = $mocdocController->sendHmacRequest($this->entityKey);

        if ($response['status'] !== 200) {
            return [
                'error' => true,
                'message' => 'Failed to fetch doctors from MocDoc API'
            ];
        }

        $mocdocDoctors = collect($response['data']['dr'] ?? []);
        $localDoctors = Doctor::select('id', 'name', 'drKey')->get();

        $localDrKeys = $localDoctors->pluck('drKey')->filter()->toArray();
        $mocdocDrKeys = $mocdocDoctors->pluck('drkey')->toArray();

        // Add status flag to MocDoc doctors
        $mocdocDoctors = $mocdocDoctors->map(function ($doctor) use ($localDrKeys) {
            $doctor['exists_in_local'] = in_array($doctor['drkey'], $localDrKeys);
            return $doctor;
        });

        // Add status flag to Local doctors
        $localDoctors = $localDoctors->map(function ($doctor) use ($mocdocDrKeys) {
            $doctor->exists_in_mocdoc = in_array($doctor->drKey, $mocdocDrKeys);
            return $doctor;
        });

        return [
            'error' => false,
            'mocdocDoctors' => $mocdocDoctors,
            'localDoctors'  => $localDoctors,
        ];
    }
}