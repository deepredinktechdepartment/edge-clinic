<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Services\Sms\NettyfishSmsService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    protected $signature = 'sms:send-appointment-reminders {--date= : Date to process (Y-m-d), for a controlled retry}';
    protected $description = 'Send approved DLT appointment and follow-up reminder SMS messages.';

    public function handle(NettyfishSmsService $sms): int
    {
        $today = Carbon::parse($this->option('date') ?: today())->startOfDay();
        $sent = 0;

        foreach ([[$today, true], [$today->copy()->addDay(), false]] as [$date, $isToday]) {
            $payments = Payment::query()
                ->with(['patient', 'doctor'])
                ->where('type', 'appointment')
                ->whereIn('aptDate', [$date->format('Y-m-d'), $date->format('Ymd')])
                ->whereNotIn('appointment_status', ['Cancelled', 'Completed', 'Not Visited'])
                ->get();

            foreach ($payments as $payment) {
                if (blank($payment->patient?->mobile)) {
                    continue;
                }
                $sent += (int) $sms->sendAppointmentReminder(
                    (string) $payment->id,
                    $payment->patient->mobile,
                    $payment->patient->name ?? 'Patient',
                    $payment->doctor?->name ?? 'Doctor',
                    $date->format('d M Y'),
                    (string) $payment->aptTime,
                    $isToday,
                    (bool) $payment->is_followup,
                );
            }
        }

        $this->info("Processed {$sent} approved DLT reminder SMS message(s).");
        return self::SUCCESS;
    }
}
