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
use App\Mail\PaymentFailedMail;
use Illuminate\Support\Facades\Mail;


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
        $amount = 1 * 100; // ₹1.00 in paise

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
                'amount' => $amount / 100,
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
                'end' => $payment['notes']['apt_time'] ?? '',
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

            // Insert new payment attempt
            DB::table('payments')->insert([
                'patient_id' => $details['patient_id']??0,
                'payment_id' => $payment['id'],
                'user_id' => $details['user_id'],
                'doctor_id' => $details['doctor_id'],
                'order_id' => $payment['order_id'],
                'amount' => $details['amount'],
                'currency' => $details['currency'],
                'status' => $details['status'],
                'email' => $details['email'],
                'phone' => $details['phone'],
                'aptDate' => $details['date'],
                'aptTime' => $details['start'],
                'ip_address' => $details['ip_address'],
                'user_agent' => $details['user_agent'],
                'referrer' => $details['referrer'],
                'response' => json_encode($payment->toArray()),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

       

                // Handle status-based redirect
            $status = strtolower($details['status']);

            if ($status === 'captured' || $status === 'authorized') {


            $mocdocResponse=$this->bookMocdocAppointment($details);
         
            // 2️⃣ Store MocDoc response in payments table
            DB::table('payments')
            ->where('payment_id', $payment['id'])
            ->update([       
            'mocdoc_apptkey'  => $mocdocResponse['apptkey'] ?? null,
            'mocdoc_response'=> json_encode($mocdocResponse),
            'updated_at'      => now(),
            ]);

            
session([
    'payment_details' => array_merge($details, [
        'apptkey' => $mocdocResponse['apptkey'] ?? null,
        'api_status'  => $mocdocResponse['status'] ?? null,
    ])
]);

if (
    isset($mocdocResponse['status']) &&
    (int) $mocdocResponse['status'] === 200
) {
    if (!empty($mocdocResponse['apptkey'])) {
        return redirect()
            ->route('razorpay.success')
            ->with('success', 'Your booked ID is generated successfully');
    }

    // apptkey not present
    return redirect()->route('razorpay.success');
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

        Log::info('MocDoc HTTP Code', ['code' => $httpCode]);
        Log::info('MocDoc Raw Response', ['response' => $response]);

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
