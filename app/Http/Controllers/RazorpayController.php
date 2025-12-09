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
    public function index(){
        
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
                    'industry' => $validated['industry'],
                    'designation' => $validated['designation'] ?? '',
                    'firmtype' => $validated['firmtype'] ?? '',
                    'businessname' => $validated['businessname'] ?? '',
                    'employees' => $validated['employees'] ?? '',
                    'customer_name' => $validated['fullname'],
                    'customer_email' => $validated['email'],
                    'customer_phone' => $validated['phone'],
                    'customer_phone_code' => $validated['country_code'],
                ]
            ]);
            
            
            
                $orderId = $order['id'];

        /** ✅ Push to LeadStore CRM (Status: Order Initiated) **/
        $leadData = [
            'firstName'   => $validated['fullname'],
            'lastName'    => '',
            'email'       => $validated['email'],
            'phoneNumber' => $validated['phone'],
            'countryCode' => $validated['country_code'] ?? '',
            'message'     => 'Order initiated via Razorpay checkout',
            'utm_source'  => $request->input('utm_source', ''),
            'utm_medium'  => $request->input('utm_medium', ''),
            'utm_campaign'=> $request->input('utm_campaign', ''),
            'utm_term'    => $request->input('utm_term', ''),
            'utm_content' => $request->input('utm_content', ''),
            'ip_address'  => $request->ip(),
            'sourceURL'   => url()->current(),
            'referrer'    => $request->headers->get('referer'),
            'userAgent'   => $request->userAgent(),
            'api_key'     => '96f89fe8-e21b-4c6a-9563-78aa38b6d214',
        ];

        /** ✅ UDF (Custom Fields) **/
        $udfFields = [
            ['fieldName' => 'Order ID', 'fieldValue' => $orderId],
            ['fieldName' => 'Amount', 'fieldValue' => number_format($amount / 100, 2)],
            ['fieldName' => 'Currency', 'fieldValue' => 'INR'],
            ['fieldName' => 'Status', 'fieldValue' => 'Order Initiated'],
            ['fieldName' => 'Industry', 'fieldValue' => $validated['industry']],
            ['fieldName' => 'Designation', 'fieldValue' => $validated['designation'] ?? ''],
            ['fieldName' => 'Firm Type', 'fieldValue' => $validated['firmtype'] ?? ''],
            ['fieldName' => 'Business Name', 'fieldValue' => $validated['businessname'] ?? ''],
            ['fieldName' => 'Employees', 'fieldValue' => $validated['employees'] ?? ''],
        ];

$leadData['UDF'] = array_values(array_filter($udfFields, function ($field) {
    return !empty($field['fieldValue']);
}));

// Send to CRM
//$response = $this->sendLeadToCRM($leadData);

            

            return view('razorpay.checkout', [
                'orderId' => $order['id'],
                'amount' => $amount,
                'customer' => $validated,
            ]);
        } catch (Exception $e) {
          dd($e->getmessage);
            return back()->withErrors(['error' => 'Order creation failed: ' . $e->getMessage()]);
        }
    }

  public function verifyPayment(Request $request)
{
   
    $signature = $request->input('razorpay_signature');
    $paymentId = $request->input('razorpay_payment_id');
    $orderId   = $request->input('razorpay_order_id');

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
        'name' => $payment['notes']['customer_name'] ?? '',
        'email' => $payment['notes']['customer_email'] ?? '',
        'phone' => $payment['notes']['customer_phone'] ?? '',
        'customer_phone_code' => $payment['notes']['customer_phone_code'] ?? '',
        'payment_id' => $paymentId,
        'order_id' => $orderId,
        'amount' => number_format($payment['amount'] / 100, 2),
        'currency' => $payment['currency'],
        'status' => ucfirst($payment['status']),
    ];

    // ✅ Send confirmation email

    

     /** ✅ Push data to LeadStore CRM */
$leadData = [
    'firstName'   => $details['name'],
    'lastName'    => '',
    'email'       => $details['email'],
    'phoneNumber' => $details['phone'],
    'countryCode' => $details['customer_phone_code'],
    'message'     => 'Payment successful via Razorpay',

    'utm_source'  => $payment['notes']['utm_source'] ?? '',
    'utm_medium'  => $payment['notes']['utm_medium'] ?? '',
    'utm_campaign'=> $payment['notes']['utm_campaign'] ?? '',
    'utm_term'    => $payment['notes']['utm_term'] ?? '',
    'utm_content' => $payment['notes']['utm_content'] ?? '',

    'ip_address'  => request()->ip(),
    'sourceURL'   => url()->current(),
    'referrer'    => request()->headers->get('referer'),
    'userAgent'   => request()->userAgent(),
    'api_key'     => '96f89fe8-e21b-4c6a-9563-78aa38b6d214',
];

// ✅ Build dynamic UDF fields
$udfFields = [];

// Razorpay Transaction Details
$udfFields[] = ['fieldName' => 'Payment ID', 'fieldValue' => $paymentId];
$udfFields[] = ['fieldName' => 'Order ID', 'fieldValue' => $orderId];
$udfFields[] = ['fieldName' => 'Amount', 'fieldValue' => number_format($payment['amount'] / 100, 2)];
$udfFields[] = ['fieldName' => 'Currency', 'fieldValue' => $payment['currency']];
$udfFields[] = ['fieldName' => 'Status', 'fieldValue' => ucfirst($payment['status'])];


// Additional business details
if (!empty($payment['notes']['industry'])) {
    $udfFields[] = ['fieldName' => 'Industry', 'fieldValue' => $payment['notes']['industry']];
}
if (!empty($payment['notes']['designation'])) {
    $udfFields[] = ['fieldName' => 'Designation', 'fieldValue' => $payment['notes']['designation']];
}
if (!empty($payment['notes']['firmtype'])) {
    $udfFields[] = ['fieldName' => 'Firm Type', 'fieldValue' => $payment['notes']['firmtype']];
}
if (!empty($payment['notes']['businessname'])) {
    $udfFields[] = ['fieldName' => 'Business Name', 'fieldValue' => $payment['notes']['businessname']];
}
if (!empty($payment['notes']['employees'])) {
    $udfFields[] = ['fieldName' => 'Employees', 'fieldValue' => $payment['notes']['employees']];
}
if (!empty($request->input('area'))) {
    $udfFields[] = ['fieldName' => 'Location', 'fieldValue' => $request->input('area')];
}


// Customer Info
$udfFields[] = ['fieldName' => 'Customer Name', 'fieldValue' => $details['name'] ?? ''];
$udfFields[] = ['fieldName' => 'Customer Email', 'fieldValue' => $details['email'] ?? ''];
$udfFields[] = ['fieldName' => 'Customer Phone', 'fieldValue' => $details['phone'] ?? ''];


// ✅ Filter null/empty UDFs
$filteredUdfFields = array_filter($udfFields, function ($field) {
    return !empty($field['fieldValue']);
});

// ✅ Add to payload
if (!empty($filteredUdfFields)) {
    $leadData['UDF'] = array_values($filteredUdfFields);
}

// Send to CRM
$response = $this->sendLeadToCRM($leadData);
            

        // ✅ Store payment details in session for the thank-you page
        session([
            'payment_details' => [
                'payment_id' => $paymentId,
                'order_id' => $orderId,
                'amount' => number_format($payment['amount'] / 100, 2),
                'currency' => $payment['currency'],
                'status' => ucfirst($payment['status']),
                'name' => $payment['notes']['customer_name'] ?? '',
                'email' => $payment['notes']['customer_email'] ?? '',
                'phone' => $payment['notes']['customer_phone'] ?? '',
                'customer_phone_code' => $payment['notes']['customer_phone_code'] ?? '',
                'industry' => $payment['notes']['industry'] ?? '',
                'designation' => $payment['notes']['designation'] ?? '',
                'firmtype' => $payment['notes']['firmtype'] ?? '',
                'businessname' => $payment['notes']['businessname'] ?? '',
                'employees' => $payment['notes']['employees'] ?? '',
            ]
        ]);

        return redirect()->route('razorpay.success');
    } catch (Exception $e) {
        return redirect()->route('razorpay.failure', ['reason' => $e->getMessage()]);
    }
}
  /**
     * ✅ Send Lead to LeadStore CRM
     */
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
    try {
        $paymentDetails = session('payment_details');

        if (!$paymentDetails) {
            return redirect()->route('razorpay.index')
                ->withErrors('No payment details found.');
        }

        $details = [
            'name' => $paymentDetails['name'] ?? '',
            'email' => $paymentDetails['email'] ?? '',
            'phone' => $paymentDetails['phone'] ?? '',
            'customer_phone_code' => $paymentDetails['customer_phone_code'] ?? '',
            'payment_id' => $paymentDetails['payment_id'] ?? '',
            'order_id' => $paymentDetails['order_id'] ?? '',
            'amount' => $paymentDetails['amount'] ?? '',
            'currency' => $paymentDetails['currency'] ?? '',
            'status' => ucfirst($paymentDetails['status'] ?? ''),
        ];

        // ✅ Try to send confirmation email
        try {
            
            // Data for email template
            $details = [
            'name'  => $paymentDetails['name'],
            ];
            
            // Email subject and recipient
            $toEmail = $paymentDetails['email'];
            $subject = 'Seat confirmed for Bridgegap Masterclass';
            
            // Send email via Brevo API helper
            $result = BrevoMailHelper::sendMail(
            $toEmail,
            $subject,
            'emails.seat_confirmation',
            ['details' => $details]
            );
            
    // ✅ Log based on response
    if (empty($result['success']) || $result['success'] === false) {
        Log::error('❌ Brevo email sending failed', [
            'email'   => $toEmail,
            'subject' => $subject,
            'error'   => $result['error'] ?? 'Unknown error',
            'response' => $result,
        ]);
    } else {
        Log::info('✅ Brevo email sent successfully', [
            'email'   => $toEmail,
            'subject' => $subject,
        ]);
    }
    
            
            } catch (Exception $mailEx) {
            
            // Optionally, you can notify admin or silently continue
            
            
            // ⚠️ Catch and log unexpected exceptions
            Log::error('🚨 Exception while sending Brevo mail', [
            'email'   => $paymentDetails['email'] ?? 'unknown',
            'subject' => 'Seat confirmed for Bridgegap Masterclass',
            'error'   => $mailEx->getMessage(),
            'trace'   => $mailEx->getTraceAsString(),
            ]);
    
    
            }

        return view('razorpay.success', compact('paymentDetails'));

    } catch (Exception $e) {
  

        return redirect()->route('razorpay.index')->withErrors([
            'error' => 'Something went wrong while processing your payment success. Please contact support.'
        ]);
    }
}

    public function failure(Request $request)
    {
        $reason = $request->query('reason', 'Payment cancelled or failed.');
        return view('razorpay.failure', compact('reason'));
    }
    
public function testmail()
{

   // Data for email template
        $details = [
            'name'  => 'John Doe',
        ];

        // Email subject and recipient
        $toEmail = 'venkat@deepredink.com';
        $subject = 'Seat confirmed for Bridgegap Masterclass';

        // Send email via Brevo API helper
        $result = BrevoMailHelper::sendMail(
            $toEmail,
            $subject,
            'emails.seat_confirmation',
            ['details' => $details]
        );

        // JSON response
        return response()->json($result, $result['success'] ? 200 : 500);


        
        dd("debug");
        
    try {
        Mail::raw('Test email from Laravel', function ($message) {
            $message->to('venkat@deepredink.com')
                    ->subject('SMTP Test');
        });

        // Check for failures (Laravel records failed recipients)
        if (count(Mail::failures()) > 0) {
            $failedRecipients = implode(', ', Mail::failures());
            Log::error("Mail sending failed to: {$failedRecipients}");
            return response()->json([
                'status'  => 'error',
                'message' => 'Mail sending failed',
                'failed'  => Mail::failures()
            ], 500);
        }

        // Success response
        return response()->json([
            'status'  => 'success',
            'message' => 'Mail sent successfully'
        ]);

    } catch (Exception $e) {
        // Log and return error
        Log::error('Mail sending error: ' . $e->getMessage());
        return response()->json([
            'status'  => 'error',
            'message' => 'Something went wrong while sending mail',
            'error'   => $e->getMessage()
        ], 500);
    }
}
    
}
