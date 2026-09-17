<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\SmsNotificationLog;
use Throwable;

class NettyfishSmsService
{
    private const DLT_TEMPLATE_IDS = [
        'service_payment_received' => '1777178575615915137',
        'appointment_payment_received' => '1777178575426720951',
        'nps_feedback_request' => '1777178599490023804',
        'appointment_reminder_one_day' => '1777178575304036083',
        'followup_reminder_one_day' => '1777178575642370248',
        'followup_reminder_today' => '1777178575649172611',
        'appointment_reminder_today' => '1777178575397841225',
        'appointment_cancelled' => '1777178575541199166',
        'service_bill_link' => '1777178575601017906',
        'appointment_payment_failed' => '1777178575482404202',
        'prescription_ready' => '1777178599481681713',
    ];

    public function sendOtp(string $mobile, string $name, string $otp): bool
    {
        try {
            /* ===============================
             | 1️⃣ Build Message
             =============================== */
            $message = sprintf(
                'Dear %s, your OTP for registering at Edge Clinic is %s. Please enter this code to verify your mobile number. Do not share this OTP with anyone. Edge Clinic | +91-6303285050 Thank you EDGEJV',
                $name,
                $otp
            );

            $message = preg_replace("/\r|\n/", ' ', $message);

            /* ===============================
             | 2️⃣ API Parameters (AS PER PROVIDER)
             =============================== */
            $params = [
                'APIKEY'   => config('services.nettyfish.api_key'),
                'senderid' => config('services.nettyfish.sender_id'),
                'channel'  => 'Trans',
                'DCS'      => 0,
                'flashsms' => 0,
                'number'   => '91' . $mobile,
                'text'     => $message,
                'route'    => 1,
            ];


            /* ===============================
             | 3️⃣ Send Request
             =============================== */
            $response = Http::timeout(10)->get(
                config('services.nettyfish.url'),
                $params
            );

            /* ===============================
             | 4️⃣ Log for Checking
             =============================== */
            Log::info('Nettyfish SMS Debug', [
                'params' => $params,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            // 🔎 TEMP (remove later)
            // dd($params, $response->body());

            return $response->successful();

        } catch (Throwable $e) {

            Log::error('Nettyfish SMS Failed', [
                'mobile' => $mobile,
                'error'  => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function sendAppointmentConfirmation(
        string $mobile,
        string $name,
        string $clinic,
        string $date,
        string $time
    ): bool {
        try {

            /* ===============================
            | 1️⃣ Build Template Message
            =============================== */

            $message = sprintf(
                'Dear %s, Your appointment is confirmed. Please visit %s on %s %s, For help 6303258050 - EDGE CLINIC | +91-6303285050 www.edge.clinic Thank you EDGEJV',
                $name,
                $clinic,
                $date,
                $time
            );

            $message = preg_replace("/\r|\n/", ' ', $message);

            /* ===============================
            | 2️⃣ API Parameters
            =============================== */

            $params = [
                'APIKEY'   => config('services.nettyfish.api_key'),
                'senderid' => config('services.nettyfish.sender_id'),
                'channel'  => 'Trans',
                'DCS'      => 0,
                'flashsms' => 0,
                'number'   => '91' . $mobile,
                'text'     => $message,
                'route'    => 1,
            ];

            /* ===============================
            | 3️⃣ Send SMS
            =============================== */

            $response = Http::timeout(10)->get(
                config('services.nettyfish.url'),
                $params
            );

            Log::info('Appointment SMS', [
                'params' => $params,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return $response->successful();

        } catch (\Throwable $e) {

            Log::error('Appointment SMS Failed', [
                'mobile' => $mobile,
                'error'  => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function sendInvoiceSms(
        string $mobile,
        string $name,
        string $invoiceUrl,
        string $supportNumber,
        string $clinic,
        string $doctor
    ): bool {

        try {


            // Message (must match DLT template exactly)
            $message = sprintf(
                'Dear %s, Thank you for visiting us. Please click on the following link to download your bill %s. For help, please contact us at %s. Take Care, %s - %s. - EDGE CLINIC | +91-6303285050 www.edge.clinic Thank you EDGEJV',
                $name,
                $invoiceUrl,
                $supportNumber,
                $clinic,
                $doctor
            );

            // Remove line breaks
            $message = preg_replace('/\s+/', ' ', trim($message));

            $params = [
                'APIKEY'   => config('services.nettyfish.api_key'),
                'senderid' => config('services.nettyfish.sender_id'),
                'channel'  => 'Trans',
                'DCS'      => 0,
                'flashsms' => 0,
                'number'   => '91' . $mobile,
                'text'     => urlencode($message),
                'route'    => 1,
            ];

            Log::info('Invoice SMS Request', [
                'mobile' => $mobile,
                'message' => $message,
                'params' => $params
            ]);

            $response = Http::timeout(10)->get(
                config('services.nettyfish.url'),
                $params
            );

            Log::info('Invoice SMS Response', [
                'status' => $response->status(),
                'body'   => $response->body()
            ]);

            return $response->successful();

        } catch (\Throwable $e) {

            Log::error('Invoice SMS Failed', [
                'mobile' => $mobile,
                'error'  => $e->getMessage()
            ]);

            return false;
        }
    }

    public function sendPrescriptionSms(string $mobile, string $name, string $doctor, string $prescriptionUrl): bool
    {
        return $this->sendApproved('prescription_ready', "prescription-ready:{$prescriptionUrl}", $mobile, sprintf(
            'Dear %s, your prescription from Dr. %s is ready. View or download it here: %s. For help, call +91-6303285050 or visit www.edge.clinic. Thank you, EDGE CLINIC.',
            $name, $this->doctorName($doctor), $prescriptionUrl
        ));
    }

    public function sendServiceBillLink(string $key, string $mobile, string $name, string $invoiceUrl): bool
    {
        return $this->sendApproved('service_bill_link', "service-bill-link:{$key}", $mobile, sprintf(
            'Dear %s, your bill has been generated successfully. You can view or download it here: %s. For help, call +91-6303285050 or visit www.edge.clinic. Thank you, EDGE CLINIC.',
            $name, $invoiceUrl
        ));
    }

    public function sendServicePaymentReceived(string $key, string $mobile, string $name, float $amount, string $invoiceNumber, string $paymentMode, string $invoiceUrl): bool
    {
        return $this->sendApproved('service_payment_received', "service-payment-received:{$key}", $mobile, sprintf(
            'Dear %s, payment of Rs %s for Invoice %s has been received through %s. View or download your invoice here: %s. For help, call +91-6303285050 or visit www.edge.clinic. Thank you, EDGE CLINIC.',
            $name, $this->amount($amount), $invoiceNumber, $paymentMode, $invoiceUrl
        ));
    }

    public function sendAppointmentPaymentReceived(string $key, string $mobile, string $name, float $amount, string $paymentMode, string $receiptNumber, string $receiptUrl): bool
    {
        return $this->sendApproved('appointment_payment_received', "appointment-payment-received:{$key}", $mobile, sprintf(
            'Dear %s, payment of Rs %s for your appointment has been received through %s. Receipt No: %s. View your receipt: %s. For help, call +91-6303285050 or visit www.edge.clinic. Thank you, EDGE CLINIC.',
            $name, $this->amount($amount), $paymentMode, $receiptNumber, $receiptUrl
        ));
    }

    public function sendNpsFeedbackRequest(string $key, string $mobile, string $name, string $doctor, string $feedbackUrl): bool
    {
        return $this->sendApproved('nps_feedback_request', "nps-feedback-request:{$key}", $mobile, sprintf(
            'Dear %s, thank you for visiting Edge Clinic. Please share your feedback about your visit with Dr. %s: %s. For help, call +91-6303285050 or visit www.edge.clinic. Thank you, EDGE CLINIC.',
            $name, $this->doctorName($doctor), $feedbackUrl
        ));
    }

    public function sendAppointmentReminder(string $key, string $mobile, string $name, string $doctor, string $date, string $time, bool $today, bool $followup): bool
    {
        if ($followup) {
            $message = $today
                ? sprintf('Dear %s, this is a reminder that your follow-up appointment with Dr. %s is today at %s. For help, call +91-6303285050 or visit www.edge.clinic. Thank you, EDGE CLINIC.', $name, $this->doctorName($doctor), $time)
                : sprintf('Dear %s, this is a reminder for your follow-up appointment with Dr. %s on %s at %s. For help, call +91-6303285050 or visit www.edge.clinic. Thank you, EDGE CLINIC.', $name, $this->doctorName($doctor), $date, $time);
            return $this->sendApproved($today ? 'followup_reminder_today' : 'followup_reminder_one_day', "followup-reminder-" . ($today ? 'today' : 'one-day') . ":{$key}", $mobile, $message);
        }

        $message = $today
            ? sprintf('Dear %s, your appointment with Dr. %s is today at %s. Please arrive on time. For help, call +91-6303285050 or visit www.edge.clinic. Thank you, EDGE CLINIC.', $name, $this->doctorName($doctor), $time)
            : sprintf('Dear %s, this is a reminder for your appointment with Dr. %s on %s at %s. For help, contact 6303258050. Thank you, EDGE CLINIC.', $name, $this->doctorName($doctor), $date, $time);
        return $this->sendApproved($today ? 'appointment_reminder_today' : 'appointment_reminder_one_day', "appointment-reminder-" . ($today ? 'today' : 'one-day') . ":{$key}", $mobile, $message);
    }

    public function sendAppointmentCancelled(string $key, string $mobile, string $name, string $doctor, string $date, string $time): bool
    {
        return $this->sendApproved('appointment_cancelled', "appointment-cancelled:{$key}", $mobile, sprintf(
            'Dear %s, your appointment with Dr. %s scheduled on %s at %s has been cancelled. To rebook, call +91-6303285050 or visit www.edge.clinic. Thank you, EDGE CLINIC.',
            $name, $this->doctorName($doctor), $date, $time
        ));
    }

    public function sendAppointmentPaymentFailed(string $key, string $mobile, string $name, float $amount, string $doctor): bool
    {
        return $this->sendApproved('appointment_payment_failed', "appointment-payment-failed:{$key}", $mobile, sprintf(
            'Dear %s, your payment of Rs %s for the appointment with Dr. %s was unsuccessful. Please try again or for help, call +91-6303285050 or visit www.edge.clinic. Thank you, EDGE CLINIC.',
            $name, $this->amount($amount), $this->doctorName($doctor)
        ));
    }

    private function sendApproved(string $templateKey, string $notificationKey, string $mobile, string $message): bool
    {
        $message = preg_replace('/\s+/', ' ', trim($message));
        $mobile = preg_replace('/\D+/', '', $mobile);
        $mobile = preg_replace('/^91/', '', $mobile);

        if (blank($mobile)) {
            Log::warning('Approved SMS skipped because the patient mobile is missing.', compact('templateKey', 'notificationKey'));
            return false;
        }

        if (SmsNotificationLog::where('notification_key', $notificationKey)->where('status', 'sent')->exists()) {
            return true;
        }

        try {
            $response = Http::timeout(10)->get(config('services.nettyfish.url'), [
                'APIKEY' => config('services.nettyfish.api_key'), 'senderid' => config('services.nettyfish.sender_id'),
                'channel' => 'Trans', 'DCS' => 0, 'flashsms' => 0, 'number' => '91' . $mobile,
                'text' => $message, 'route' => 1,
            ]);
            SmsNotificationLog::updateOrCreate(['notification_key' => $notificationKey], [
                'template_key' => $templateKey, 'dlt_template_id' => self::DLT_TEMPLATE_IDS[$templateKey], 'mobile' => $mobile, 'message' => $message,
                'status' => $response->successful() ? 'sent' : 'failed', 'provider_response' => $response->body(),
                'sent_at' => $response->successful() ? now() : null,
            ]);
            Log::info('Approved DLT SMS sent.', ['template' => $templateKey, 'notification_key' => $notificationKey, 'status' => $response->status()]);
            return $response->successful();
        } catch (Throwable $e) {
            SmsNotificationLog::updateOrCreate(['notification_key' => $notificationKey], [
                'template_key' => $templateKey, 'dlt_template_id' => self::DLT_TEMPLATE_IDS[$templateKey], 'mobile' => $mobile, 'message' => $message,
                'status' => 'failed', 'provider_response' => $e->getMessage(), 'sent_at' => null,
            ]);
            Log::error('Approved DLT SMS failed.', ['template' => $templateKey, 'notification_key' => $notificationKey, 'error' => $e->getMessage()]);
            return false;
        }
    }

    private function doctorName(string $doctor): string
    {
        return preg_replace('/^Dr\.\s*/i', '', trim($doctor));
    }

    private function amount(float $amount): string
    {
        return rtrim(rtrim(number_format($amount, 2, '.', ''), '0'), '.');
    }
}
