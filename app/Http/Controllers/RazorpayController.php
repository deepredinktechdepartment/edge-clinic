<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\Http;
use Exception;
use Illuminate\Support\Facades\Mail;
use App\Mail\SeatConfirmationMail;
use Illuminate\Support\Facades\Log;
use App\Helper\BrevoMailHelper;

class RazorpayController extends Controller
{
    public function index()
    {
        return view('lpindex');
    }

    public function createOrder(Request $request)
    {
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

        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
        $amount = 1 * 100; // ₹1.00 in paise

        try {
            $order = $api->order->create([
                'receipt' => uniqid('rcpt_'),
                'amount' => $amount,
                'currency' => 'INR',
                'payment_capture' => 1,
                'notes' => [
                    'industry' => $validated['industry'] ?? '',
                    'designation' => $validated['designation'] ?? '',
                    'firmtype' => $validated['firmtype'] ?? '',
                    'businessname' => $validated['businessname'] ?? '',
                    'employees' => $validated['employees'] ?? '',
                    'customer_first_name' => $validated['first_name'],
                    'customer_last_name' => $validated['last_name'],
                    'customer_email' => $validated['email'],
                    'customer_phone' => $validated['phone'],
                    'customer_phone_code' => $validated['country_code'] ?? '',
                ]
            ]);

            $orderId = $order['id'];

            /** ✅ Push to LeadStore CRM (Status: Order Initiated) **/
            $leadData = [
                'firstName' => $validated['first_name'],
                'lastName' => $validated['last_name'],
                'email' => $validated['email'],
                'phoneNumber' => $validated['phone'],
                'countryCode' => $validated['country_code'] ?? '',
                'message' => 'Order initiated via Razorpay checkout',
                'utm_source' => $request->input('utm_source', ''),
                'utm_medium' => $request->input('utm_medium', ''),
                'utm_campaign' => $request->input('utm_campaign', ''),
                'utm_term' => $request->input('utm_term', ''),
                'utm_content' => $request->input('utm_content', ''),
                'ip_address' => $request->ip(),
                'sourceURL' => url()->current(),
                'referrer' => $request->headers->get('referer'),
                'userAgent' => $request->userAgent(),
                'api_key' => '96f89fe8-e21b-4c6a-9563-78aa38b6d214',
            ];

            /** ✅ UDF (Custom Fields) **/
            $udfFields = [
                ['fieldName' => 'Order ID', 'fieldValue' => $orderId],
                ['fieldName' => 'Amount', 'fieldValue' => number_format($amount / 100, 2)],
                ['fieldName' => 'Currency', 'fieldValue' => 'INR'],
                ['fieldName' => 'Status', 'fieldValue' => 'Order Initiated'],
                ['fieldName' => 'Industry', 'fieldValue' => $validated['industry'] ?? ''],
                ['fieldName' => 'Designation', 'fieldValue' => $validated['designation'] ?? ''],
                ['fieldName' => 'Firm Type', 'fieldValue' => $validated['firmtype'] ?? ''],
                ['fieldName' => 'Business Name', 'fieldValue' => $validated['businessname'] ?? ''],
                ['fieldName' => 'Employees', 'fieldValue' => $validated['employees'] ?? ''],
            ];

            $leadData['UDF'] = array_values(array_filter($udfFields, function ($field) {
                return !empty($field['fieldValue']);
            }));

            // Send to CRM
            // $response = $this->sendLeadToCRM($leadData);

            return view('razorpay.checkout', [
                'orderId' => $order['id'],
                'amount' => $amount,
                'customer' => $validated,
            ]);

        } catch (Exception $e) {
            dd($e->getMessage());
            return back()->withErrors(['error' => 'Order creation failed: ' . $e->getMessage()]);
        }
    }

    public function verifyPayment(Request $request)
    {
        $signature = $request->input('razorpay_signature');
        $paymentId = $request->input('razorpay_payment_id');
        $orderId = $request->input('razorpay_order_id');

        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

        try {
            $attributes = [
                'razorpay_order_id' => $orderId,
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => $signature
            ];

            // Verify signature
            $api->utility->verifyPaymentSignature($attributes);

            // Fetch payment details
            $payment = $api->payment->fetch($paymentId);

            $details = [
                'first_name' => $payment['notes']['customer_first_name'] ?? '',
                'last_name' => $payment['notes']['customer_last_name'] ?? '',
                'email' => $payment['notes']['customer_email'] ?? '',
                'phone' => $payment['notes']['customer_phone'] ?? '',
                'customer_phone_code' => $payment['notes']['customer_phone_code'] ?? '',
                'payment_id' => $paymentId,
                'order_id' => $orderId,
                'amount' => number_format($payment['amount'] / 100, 2),
                'currency' => $payment['currency'],
                'status' => ucfirst($payment['status']),
            ];

            /** ✅ Push data to LeadStore CRM **/
            $leadData = [
                'firstName' => $details['first_name'],
                'lastName' => $details['last_name'],
                'email' => $details['email'],
                'phoneNumber' => $details['phone'],
                'countryCode' => $details['customer_phone_code'],
                'message' => 'Payment successful via Razorpay',
                'utm_source' => $payment['notes']['utm_source'] ?? '',
                'utm_medium' => $payment['notes']['utm_medium'] ?? '',
                'utm_campaign' => $payment['notes']['utm_campaign'] ?? '',
                'utm_term' => $payment['notes']['utm_term'] ?? '',
                'utm_content' => $payment['notes']['utm_content'] ?? '',
                'ip_address' => request()->ip(),
                'sourceURL' => url()->current(),
                'referrer' => request()->headers->get('referer'),
                'userAgent' => request()->userAgent(),
                'api_key' => '96f89fe8-e21b-4c6a-9563-78aa38b6d214',
            ];

            // Build dynamic UDF fields
            $udfFields = [
                ['fieldName' => 'Payment ID', 'fieldValue' => $paymentId],
                ['fieldName' => 'Order ID', 'fieldValue' => $orderId],
                ['fieldName' => 'Amount', 'fieldValue' => number_format($payment['amount'] / 100, 2)],
                ['fieldName' => 'Currency', 'fieldValue' => $payment['currency']],
                ['fieldName' => 'Status', 'fieldValue' => ucfirst($payment['status'])],
                ['fieldName' => 'Industry', 'fieldValue' => $payment['notes']['industry'] ?? ''],
                ['fieldName' => 'Designation', 'fieldValue' => $payment['notes']['designation'] ?? ''],
                ['fieldName' => 'Firm Type', 'fieldValue' => $payment['notes']['firmtype'] ?? ''],
                ['fieldName' => 'Business Name', 'fieldValue' => $payment['notes']['businessname'] ?? ''],
                ['fieldName' => 'Employees', 'fieldValue' => $payment['notes']['employees'] ?? ''],
                ['fieldName' => 'Customer First Name', 'fieldValue' => $details['first_name'] ?? ''],
                ['fieldName' => 'Customer Last Name', 'fieldValue' => $details['last_name'] ?? ''],
                ['fieldName' => 'Customer Email', 'fieldValue' => $details['email'] ?? ''],
                ['fieldName' => 'Customer Phone', 'fieldValue' => $details['phone'] ?? ''],
            ];

            if (!empty($request->input('area'))) {
                $udfFields[] = ['fieldName' => 'Location', 'fieldValue' => $request->input('area')];
            }

            // Filter null/empty UDFs
            $leadData['UDF'] = array_values(array_filter($udfFields, function ($field) {
                return !empty($field['fieldValue']);
            }));

            // Send to CRM
            $response = $this->sendLeadToCRM($leadData);

            // Store payment details in session
            session(['payment_details' => $details]);

            return redirect()->route('razorpay.success');

        } catch (Exception $e) {
            return redirect()->route('razorpay.failure', ['reason' => $e->getMessage()]);
        }
    }

    private function sendLeadToCRM($leadData)
    {
        $apiUrl = "https://leadstore.in/api/leads/handle-external-post";
        $jsonParams = json_encode($leadData);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonParams,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-API-KEY: 96f89fe8-e21b-4c6a-9563-78aa38b6d214',
            ],
        ]);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            $error = curl_error($curl);
            Log::error('LeadStore CRM Error: ' . $error);
            $response = json_encode(['error' => $error]);
        }

        curl_close($curl);
        return $response;
    }

    public function success(Request $request)
    {
        $paymentDetails = session('payment_details');

        if (!$paymentDetails) {
            return redirect()->route('razorpay.index')
                ->withErrors('No payment details found.');
        }

        // Send confirmation email via Brevo
        try {
            $details = [
                'name' => $paymentDetails['first_name'] . ' ' . $paymentDetails['last_name'],
            ];
            $toEmail = $paymentDetails['email'];
            $subject = 'Seat confirmed for Bridgegap Masterclass';

            $result = BrevoMailHelper::sendMail(
                $toEmail,
                $subject,
                'emails.seat_confirmation',
                ['details' => $details]
            );

            if (empty($result['success']) || $result['success'] === false) {
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
                'subject' => 'Seat confirmed for Bridgegap Masterclass',
                'error' => $mailEx->getMessage(),
                'trace' => $mailEx->getTraceAsString(),
            ]);
        }

        return view('razorpay.success', compact('paymentDetails'));
    }

    public function failure(Request $request)
    {
        $reason = $request->query('reason', 'Payment cancelled or failed.');
        return view('razorpay.failure', compact('reason'));
    }

    public function testmail()
    {
        $details = ['name' => 'John Doe'];
        $toEmail = 'venkat@deepredink.com';
        $subject = 'Seat confirmed for Bridgegap Masterclass';

        $result = BrevoMailHelper::sendMail(
            $toEmail,
            $subject,
            'emails.seat_confirmation',
            ['details' => $details]
        );

        return response()->json($result, $result['success'] ? 200 : 500);
    }
}
