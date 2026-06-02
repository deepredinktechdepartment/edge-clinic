<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helper\BrevoMailHelper;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\RegistrationFee;
use App\Mail\PaymentFailedMail;
use Illuminate\Support\Facades\Mail;
use App\Services\Sms\NettyfishSmsService;


class RazorpayController extends Controller
{
    /**
     * Show landing page
     */

    /**
     * Create Razorpay Order + store in DB (initiated only)
     */
    public function createOrder(Request $request)
    {


         $patientId=$request->patientId??0;
         $drKey=$request->drKey??0;
         $slotDate=$request->slotDate??'';
         $slotTime=$request->slotTime??'';
         $patient = Patient::findOrFail($patientId);
         $doctor = Doctor::where('drKey', $drKey)->first();

    // 🔴 ADD THIS BLOCK
        $exists = DB::table('appointments')
            ->where('doctor_id', $doctor->id)
            ->where('date', $slotDate)
            ->where('time_slot', $slotTime)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'error' => 'Selected slot already booked. Please choose another slot.'
            ]);
        }
        $validated = [
        'first_name' => $patient->name,
        'last_name'  => '',
        'email'      => $patient->email ?? '',
        'phone'      => $patient->mobile,
        'industry'   => 'hospital-clinic',
        'country_code' => $patient->country_code,
        'gender' => $patient->gender,
        'age' => $patient->age,
        'doctor_name' => $doctor->name,
        'doctor_id' => $doctor->id,
        'user_id' =>$patient->user_id,
        'doctor_key' => $doctor->drKey,
        'apt_date' => $slotDate,
        'apt_time' => $slotTime,
        'bookingfor' => $patient->bookingfor??'',
        'bkttoother' => $patient->other_reason??'',
        'patient_id' => $patient->id??0,
    ];



        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
        $amount = ((float) $request->total_amount) * 100; // ₹1.00 in paise

        try {
            // Create Razorpay order (initiated)
            $order = $api->order->create([
                'receipt' => uniqid('rcpt_'),
                'amount' => $amount,
                'currency' => 'INR',
                'payment_capture' => 0, // only initiated, not captured
                'notes' => [
                    'industry' => $validated['industry'] ?? '',
                    'customer_first_name' => $validated['first_name'],
                    'customer_last_name' => $validated['last_name']??'',
                    'customer_email' => $validated['email'],
                    'customer_phone' => $validated['phone'],
                    'ISD' => $validated['country_code'],
                    'doctor_key' => $validated['doctor_key'] ?? '',
                    'doctor_name' => $validated['doctor_name'] ?? '',
                    'apt_date' => $validated['apt_date'] ?? '',
                    'apt_time' => $validated['apt_time'] ?? '',
                    'gender' => $validated['gender'] ?? '',
                    'age' => $validated['age'] ?? '',
                    'bookingfor' => $validated['bookingfor'] ?? '',
                    'bkttoother' => $validated['bkttoother'] ?? '',
                    'patient_id' => $validated['patient_id'] ?? '',
                    'user_id' => $validated['user_id'] ?? '',
                    'doctor_id' => $validated['doctor_id'] ?? '',
                    'payment_choice' => $request->input('payment_choice', ''),
                    'total_due' => $request->input('total_due', ''),
                    'pay_now_amount' => $request->input('total_amount', ''),

                ]
            ]);

            // Store order in DB
            DB::table('orders')->insert([
                'patient_id' => $patient->id??0,
                'order_id' => $order['id'],
                'user_id' => $validated['user_id'],
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'doctor_fee'        => $request->doctor_fee,
                'registration_fee'  => $request->registration_fee, // ⭐ IMPORTANT
                'amount'            => $request->total_amount,
                'currency' => 'INR',
                'status' => 'created',
                'notes' => json_encode($order['notes']),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referrer' => $request->headers->get('referer'),
                'utm_source' => $request->input('utm_source', ''),
                'utm_medium' => $request->input('utm_medium', ''),
                'utm_campaign' => $request->input('utm_campaign', ''),
                'utm_term' => $request->input('utm_term', ''),
                'utm_content' => $request->input('utm_content', ''),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return view('razorpay.checkout', [
                'orderId' => $order['id'],
                'amount' => $amount,
                'customer' => $validated,
            ]);

        } catch (Exception $e) {
           Log::error('Razorpay order creation failed: '.$e->getMessage(), [
                'request' => $request->all()
            ]);
            return back()->withErrors(['error' => 'Order creation failed: ' . $e->getMessage()]);
        }
    }


    /**
     * Verify payment, capture, store payment in DB
     */
    public function verifyPayment(Request $request)
    {
        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

        $orderId = $request->razorpay_order_id;
        $paymentId = $request->razorpay_payment_id;

        try {
            $attributes = [
                'razorpay_order_id' => $orderId,
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => $request->razorpay_signature
            ];

            // Verify signature
            $api->utility->verifyPaymentSignature($attributes);

            $payment = $api->payment->fetch($paymentId);

            // Capture payment if authorized
            if ($payment['status'] === 'authorized') {
                $payment->capture(['amount' => $payment['amount']]);
            }

            // Prepare payment details
            $details = [
                'payment_id' => $payment['id'],
                'order_id' => $payment['order_id'],
                'amount' => $payment['amount'] / 100,
                'currency' => $payment['currency'],
                'status' => ucfirst($payment['status']),
                'first_name' => $payment['notes']['customer_first_name'] ?? '',
                'last_name' => $payment['notes']['customer_last_name'] ?? '',
                'email' => $payment['notes']['customer_email'] ?? '',
                'phone' => $payment['notes']['customer_phone'] ?? '',
                'patient_id' => $payment['notes']['patient_id'] ?? '',
                'user_id' => $payment['notes']['user_id'] ?? '',
                'doctor_id' => $payment['notes']['doctor_id'] ?? '',
                'dr' => $payment['notes']['doctor_key'] ?? '',
                'date' => $payment['notes']['apt_date'] ?? '',
                'start' => $payment['notes']['apt_time'] ?? '',
                'end' => !empty($payment['notes']['apt_time'])
                    ? \Carbon\Carbon::createFromFormat('H:i', $payment['notes']['apt_time'])
                        ->addMinutes(10)
                        ->format('H:i')
                    : '',
                'age' => $payment['notes']['age'] ?? '',
                'notes' => $payment['notes'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referrer' => $request->headers->get('referer'),
            ];

            // Update Orders table (current status)
            DB::table('orders')->updateOrInsert(
                ['order_id' => $payment['order_id']],
                [
                    'status' => $details['status'],
                    'updated_at' => now()
                ]
            );

            $orderRow = DB::table('orders')
            ->where('order_id', $payment['order_id'])
            ->first();

            $doctorFee = (float) ($orderRow->doctor_fee ?? 0);
            $registrationFee = (float) ($orderRow->registration_fee ?? 0);
            $totalAmount = $doctorFee + $registrationFee;
            $paidNowAmount = (float) ($details['amount'] ?? 0);
            $outstandingAmount = max($totalAmount - $paidNowAmount, 0);
            $appointmentPaymentStatus = $outstandingAmount > 0
                ? 'Pending'
                : $details['status'];


            // Insert new payment attempt
            DB::table('payments')->insert([
                'patient_id' => $details['patient_id']??0,
                'payment_id' => $payment['id'],
                'user_id' => $details['user_id'],
                'doctor_id' => $details['doctor_id'],
                'order_id' => $payment['order_id'],
                'amount' => $totalAmount,
                'currency' => $details['currency'],
                'status' => $appointmentPaymentStatus,
                'email' => $details['email'],
                'phone' => $details['phone'],
                'payment_mode' => 'online',
                'aptDate' => $details['date'],
                'aptTime' => $details['start'],
                'ip_address' => $details['ip_address'],
                'user_agent' => $details['user_agent'],
                'referrer' => $details['referrer'],
                'response' => json_encode($payment->toArray()),
                'notes' => json_encode([
                    'payment_choice' => $payment['notes']['payment_choice'] ?? null,
                    'pay_now_amount' => $paidNowAmount,
                    'total_due' => $totalAmount,
                    'outstanding_amount' => $outstandingAmount,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
                'doctor_fee'        => $doctorFee,
                'registration_fee' => $registrationFee,
            ]);



                // Handle status-based redirect
            $status = strtolower($details['status']);

            if ($status === 'captured' || $status === 'authorized') {

            // 1️⃣ Fetch order
            $orderRow = DB::table('orders')
                ->where('order_id', $payment['order_id'])
                ->first();

            // 2️⃣ Apply registration validity ONLY if paid
            if (
                $orderRow &&
                (float) $orderRow->registration_fee > 0 &&
                $paidNowAmount >= (float) $orderRow->registration_fee
            ) {

                $config = RegistrationFee::where('is_active', 1)->first();

                if ($config) {
                    Patient::where('id', $orderRow->patient_id)
                        ->update([
                            'registration_valid_till' =>
                                now()->addDays($config->validity_days),
                        ]);
                }
            }

            // 3️⃣ Update patient stage
            Patient::where('id', $details['patient_id'])
                ->update([
                    'stage' => 'payment_received',
                ]);

            /* ===============================
CREATE APPOINTMENT (REPLACES MOCDOC)
=============================== */

// 🔴 DOUBLE CHECK SLOT
$exists = DB::table('appointments')
    ->where('doctor_id', $details['doctor_id'])
    ->where('date', $details['date'])
    ->where('time_slot', $details['start'])
    ->exists();

if ($exists) {
    return redirect()->route('razorpay.failure', [
        'reason' => 'Slot already booked'
    ]);
}

$appointmentNo = 'APT' . now()->format('YmdHis') . strtoupper(\Str::random(3));

$appointmentId = DB::table('appointments')->insertGetId([
    'appointment_no' => $appointmentNo,
    'doctor_id'      => $details['doctor_id'],
    'patient_id'     => $details['patient_id'],
    'date'           => $details['date'],
    'time_slot'      => $details['start'],
    'fee'            => $totalAmount,
    'payment_id'     => $payment['id'],
    'payment_status' => $outstandingAmount > 0 ? 'pending' : 'success',
    'payment_method' => 'online',
    'currency'       => 'INR',
    'payment_date'   => now(),
    'created_at'     => now(),
]);

DB::table('appointment_status_logs')->insert([
    'appointment_no' => $appointmentNo,
    'appointment_id' => $appointmentId,
    'to_status'      => 'Booked',
    'changed_by'     => $details['patient_id'],
    'changedName'    => $details['first_name'],
    'ip_address'     => request()->ip(),
    'created_at'     => now(),
]);

/* UPDATE SESSION (IMPORTANT) */
session([
    'payment_details' => array_merge($details, [
        'appointment_no' => $appointmentNo,
        'total_due' => $totalAmount,
        'outstanding_amount' => $outstandingAmount,
    ])
]);



    if (!empty($appointmentNo)) {

        /* ===============================
        SEND APPOINTMENT CONFIRMATION SMS
        =============================== */

        $smsService = app(NettyfishSmsService::class);

        // 1️⃣ Appointment confirmation SMS
        $appointmentSms = $smsService->sendAppointmentConfirmation(
            $details['phone'],
            $details['first_name'] ?? 'Patient',
            'Edge Clinic',
            \Carbon\Carbon::parse($details['date'])->format('d M Y'),
            $details['start']
        );

        // 2️⃣ Invoice SMS
        // $invoiceSms = false;

        // if ($appointmentSms) {

        //     $invoiceUrl = route('invoice.appointment', [
        //         'paymentId' => $details['payment_id']
        //     ]);

        //     $invoiceSms = $smsService->sendInvoiceSms(
        //         $details['phone'],
        //         $details['first_name'] ?? 'Patient',
        //         $invoiceUrl,
        //         '6303258050',
        //         'Edge Clinic',
        //         'Doctor'
        //     );
        // }

        // Update SMS status
        DB::table('payments')
            ->where('payment_id', $details['payment_id'])
            ->update([
                'sms_delivered'        => $appointmentSms ? 1 : 0,
                'sms_sent_at'          => $appointmentSms ? now() : null,
            ]);

        return redirect()
            ->route('razorpay.success')
            ->with('success', 'Your booked ID is generated successfully');
    }

            } elseif ($status === 'failed') {

        $patient = Patient::find($details['patient_id']);

if ($patient && $patient->email) {
    Mail::to($patient->email)->send(new PaymentFailedMail(
        $patient->name,
        url('for-patients'),
        $details['dr'],             // Doctor name
        $details['aptDate'],        // Appointment date
        $details['aptTime']         // Appointment time
    ));
}

            return redirect()->route('razorpay.failure', ['reason' => 'Payment failed.']);
            } else {

          $patient = Patient::find($details['patient_id']);

if ($patient && $patient->email) {
    Mail::to($patient->email)->send(new PaymentFailedMail(
        $patient->name,
        url('for-patients'),
        $details['dr'],             // Doctor name
        $details['aptDate'],        // Appointment date
        $details['aptTime']         // Appointment time
    ));
}
            return redirect()->route('razorpay.failure', ['reason' => 'Payment pending.']);
            }

        } catch (Exception $e) {
            Log::error('Payment verification failed: '.$e->getMessage(), [
                'request' => $request->all()
            ]);

            // Insert as failed attempt
            // DB::table('payments')->insert([
            //     'payment_id' => $paymentId ?? null,
            //     'order_id' => $orderId ?? null,
            //     'amount' => $request->amount ?? 0,
            //     'currency' => 'INR',
            //     'status' => 'failed',
            //     'email' => $request->email ?? null,
            //     'phone' => $request->phone ?? null,
            //     'ip_address' => $request->ip(),
            //     'user_agent' => $request->userAgent(),
            //     'referrer' => $request->headers->get('referer'),
            //     'response' => json_encode(['error' => $e->getMessage()]),
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ]);

            return redirect()->route('razorpay.failure', ['reason' => $e->getMessage()]);
        }
    }

    public function bookMocdocAppointment(array $data)
{
    try {
        $entityKey = "jv-medi-clinic";
        $url = "https://mocdoc.com/api/bookappt/" . $entityKey;

        /*
        |---------------------------------------------------------
        | Build MocDoc Payload
        |---------------------------------------------------------
        */
        $postDataArray = [
            // REQUIRED
            'fname'          => $data['first_name'] ?? '',
            'phone'          => $data['phone'] ?? '',
            'dr'             => $data['dr'] ?? '',
            'date'           => $data['date'] ?? '',
            'start'          => $data['start'] ?? '',
            'end'            => $data['end'] ?? '',
            'entitykey'      => $entityKey,
            'entitylocation' => $data['entitylocation'] ?? 'location1',

            // TOKEN BASED (if any)
            'session'        => $data['session']    ?? '',
            'sessionval'     => $data['sessionval'] ?? '',
            'token_no'       => $data['token_no']   ?? '',

            // OPTIONAL
            'title'          => $data['title'] ?? '',
            'extphid'        => $data['extphid'] ?? '',
            'age'            => $data['age'] ?? '',
            'altphone'       => $data['altphone'] ?? '',
            'email'          => $data['email'] ?? '',
            'referred_by'    => $data['referred_by'] ?? '',
            'referredbykey'  => $data['referredbykey'] ?? '',
            'appnotes'       => $data['appnotes'] ?? '',
        ];

        // Remove empty values


        Log::info('MocDoc Booking Request', $postDataArray);

        // Form encoded body
        $body = json_encode($postDataArray);


        // HMAC headers
        $headers = app(\App\Http\Controllers\MocDocController::class)
        ->mocdocHmacHeaders($url, 'POST',"application/json");



        /*
        |---------------------------------------------------------
        | CURL CALL
        |---------------------------------------------------------
        */

        $response = null;
        $curlError = null;
        $httpCode = 0;

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $url,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $body,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 30,
            ]);

            $response   = curl_exec($ch);
            $curlError  = curl_error($ch);
            $httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            Log::info('MocDoc HTTP Code', ['code' => $httpCode, 'attempt' => $attempt]);
            Log::info('MocDoc Raw Response', ['response' => $response, 'attempt' => $attempt]);

            if ($httpCode !== 429) {
                break;
            }

            Log::warning('MocDoc rate limit hit, retrying booking request.', ['attempt' => $attempt]);
            usleep(800000);
        }

        if ($curlError) {
            Log::error('MocDoc CURL Error', ['error' => $curlError]);

            return [
                'status' => 'error',
                'message' => $curlError
            ];
        }

        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('MocDoc JSON Decode Failed', [
                'error' => json_last_error_msg(),
                'response' => $response
            ]);

            return [
                'status' => 'error',
                'message' => 'Invalid MocDoc response'
            ];
        }

        return $decoded;

    } catch (\Throwable $e) {

        Log::error('MocDoc Booking Exception', [
            'message' => $e->getMessage(),
            'trace'   => $e->getTraceAsString()
        ]);

        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}


    /**
     * Success page + send confirmation email
     */
    public function success(Request $request)
    {
        $paymentDetails = session('payment_details');

        if (!$paymentDetails) {
            return redirect()->route('doctors.list')
                ->withErrors('No payment details found.');
        }

        $details = [
            'name' => ($paymentDetails['first_name'] ?? '') . ' ' . ($paymentDetails['last_name'] ?? ''),
        ];

        $toEmail = $paymentDetails['email'] ?? null;
        $subject = 'Thank You! Your Appointment Is Confirmed';

        try {
            // $result = BrevoMailHelper::sendMail(
            //     $toEmail,
            //     $subject,
            //     'emails.seat_confirmation',
            //     ['details' => $details]
            // );

            if (empty($result['success'])) {
                Log::error('❌ Brevo email sending failed', [
                    'email' => $toEmail,
                    'subject' => $subject,
                    'error' => $result['error'] ?? 'Unknown error',
                    'response' => $result,
                ]);
            } else {
                Log::info('✅ Brevo email sent successfully', [
                    'email' => $toEmail,
                    'subject' => $subject,
                ]);
            }
        } catch (Exception $mailEx) {
            Log::error('🚨 Exception while sending Brevo mail', [
                'email' => $paymentDetails['email'] ?? 'unknown',
                'subject' => $subject,
                'error' => $mailEx->getMessage(),
                'trace' => $mailEx->getTraceAsString(),
            ]);
        }

        return view('razorpay.success', compact('paymentDetails'));
    }

    /**
     * Failure page
     */
    public function failure(Request $request)
    {
        $reason = $request->query('reason', 'Payment cancelled or failed.');
        return view('razorpay.failure', compact('reason'));
    }
}
