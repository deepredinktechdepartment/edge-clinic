<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\PartnerWebhookDeliveryLog;
use App\Models\PartnerWebhookIntegration;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Throwable;

class PartnerAppointmentWebhookService
{
    public function sendForStatus(Payment $payment, string $status, ?string $reason = null): void
    {
        $event = match (strtolower(trim($status))) {
            'cancelled' => 'cancelled',
            'not visited' => 'not_visited',
            'completed', 'visited' => 'visited',
            default => null,
        };

        if (! $event) {
            return;
        }

        $this->send($payment, $event, $reason);
    }

    public function sendForReschedule(Payment $payment, ?string $oldDate, ?string $oldTime, ?string $reason = null): void
    {
        $this->send($payment, 'rescheduled', $reason, $oldDate, $oldTime);
    }

    private function send(Payment $payment, string $event, ?string $reason = null, ?string $oldDate = null, ?string $oldTime = null): void
    {
        $integration = PartnerWebhookIntegration::query()
            ->where('source_id', $payment->source_id)
            ->where('is_enabled', true)
            ->first();

        if (! $integration) {
            return;
        }

        $appointment = Appointment::query()->where('payment_id', $payment->payment_id)->first();
        if (! $appointment) {
            return;
        }

        $payload = $this->payload($payment, $appointment, $event, $reason, $oldDate, $oldTime);
        $log = PartnerWebhookDeliveryLog::create([
            'partner_webhook_integration_id' => $integration->id,
            'payment_id' => $payment->id,
            'appointment_id' => $appointment->id,
            'event' => $event,
            'payload' => $payload,
        ]);

        try {
            $request = Http::acceptJson()
                ->asJson()
                ->timeout((int) $integration->timeout_seconds);

            if (filled($integration->basic_auth_username) || filled($integration->basic_auth_password)) {
                $request = $request->withBasicAuth(
                    (string) $integration->basic_auth_username,
                    (string) $integration->basic_auth_password
                );
            }

            $response = $request->post($integration->webhook_url, $payload);

            $log->update([
                'response_status' => $response->status(),
                'response_body' => mb_substr((string) $response->body(), 0, 5000),
                'delivered_at' => $response->successful() ? now() : null,
                'error_message' => $response->successful() ? null : 'Webhook returned an unsuccessful HTTP response.',
            ]);
        } catch (Throwable $exception) {
            $log->update([
                'error_message' => mb_substr($exception->getMessage(), 0, 5000),
            ]);
        }
    }

    private function payload(Payment $payment, Appointment $appointment, string $event, ?string $reason, ?string $oldDate, ?string $oldTime): array
    {
        $notes = json_decode((string) $payment->notes, true);
        $notes = is_array($notes) ? $notes : [];

        $base = [
            'partnerAppointmentId' => (string) ($notes['external_booking_id'] ?? $payment->reference_no ?? ''),
            'edgeClinicAppointmentId' => (string) $appointment->id,
        ];

        if ($event === 'rescheduled') {
            return $base + [
                'status' => 'rescheduled',
                'reason' => $reason ?: 'rescheduled',
                'appointmentDate' => $this->date($oldDate ?: $payment->aptDate),
                'appointmentTime' => $this->time($oldTime ?: $payment->aptTime),
                'rescheduledAppointmentDate' => $this->date($payment->aptDate),
                'rescheduledAppointmentTime' => $this->time($payment->aptTime),
            ];
        }

        if ($event === 'cancelled') {
            return $base + [
                'status' => 'cancelled',
                'reason' => $reason ?: 'Cancelled',
            ];
        }

        if ($event === 'not_visited') {
            return $base + [
                'status' => 'Not visited',
                'reason' => $reason ?: 'Patient did not visit the clinic',
            ];
        }

        return $base + [
            'status' => 'Visited',
            'prescriptionUrl' => $this->prescriptionUrl($appointment, $payment),
        ];
    }

    private function prescriptionUrl(Appointment $appointment, Payment $payment): string
    {
        $consultation = Consultation::query()
            ->where('appointment_id', $appointment->id)
            ->orWhere('payment_id', $payment->id)
            ->first();

        if (! $consultation || blank($consultation->case_sheet_front_path)) {
            return '';
        }

        return URL::temporarySignedRoute('prescriptions.share', now()->addDays(30), ['consultation' => $consultation->id]);
    }

    private function date(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            return preg_match('/^\d{8}$/', $value)
                ? Carbon::createFromFormat('Ymd', $value)->toDateString()
                : Carbon::parse($value)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }

    private function time(?string $value): ?string
    {
        return blank($value) ? null : (strlen($value) === 5 ? $value . ':00' : $value);
    }
}
