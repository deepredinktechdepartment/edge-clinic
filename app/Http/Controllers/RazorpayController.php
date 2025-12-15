<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helper\BrevoMailHelper;
use App\Models\Patient;
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
     

        $patientId=$request->patintId??0;
         $patient = Patient::findOrFail($patientId);

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email',
            'phone' => 'required|string|max:15',
            'industry' => 'nullable|string|max:100',
            'designation' => 'nullable|string|max:100',
            'firmtype' => 'nullable|string|max:100',
            'businessname' => 'nullable|string|max:150',
            'employees' => 'nullable|string|max:50',
            'country_code' => 'nullable|max:50'
        ]);

        $validated = [
        'first_name' => $patient->name,
        'last_name'  => '',
        'email'      => $patient->email ?? '',
        'phone'      => $patient->mobile,
        'industry'   => 'hospital-clinic',
        'country_code' => $patient->country_code,
        'gender' => $patient->gender,
        'age' => $patient->age,
        'doctor_name' => $patient->doctor_name,
        'doctor_key' => $patient->doctor_key,
        'apt_date' => $patient->apt_date,
        'apt_time' => $patient->apt_time,
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
                    'customer_last_name' => $validated['last_name'],
                    'customer_email' => $validated['email'],
                    'customer_phone' => $validated['phone'],
                    'customer_phone_code' => $validated['country_code'] ?? '',
                ]
            ]);

            // Store order in DB
            DB::table('orders')->insert([
                'order_id' => $order['id'],
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
                'payment_id' => $payment['id'],
                'order_id' => $payment['order_id'],
                'amount' => $details['amount'],
                'currency' => $details['currency'],
                'status' => $details['status'],
                'email' => $details['email'],
                'phone' => $details['phone'],
                'ip_address' => $details['ip_address'],
                'user_agent' => $details['user_agent'],
                'referrer' => $details['referrer'],
                'response' => json_encode($payment->toArray()),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            session(['payment_details' => $details]);

                // Handle status-based redirect
            $status = strtolower($details['status']);

            if ($status === 'captured' || $status === 'authorized') {
            return redirect()->route('razorpay.success');
            } elseif ($status === 'failed') {
            return redirect()->route('razorpay.failure', ['reason' => 'Payment failed.']);
            } else {
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
