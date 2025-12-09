<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Connecting to Razorpay...</title>
  <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
  <style>
    body { font-family: Arial; text-align:center; padding:100px; background:#f7f7f7; }
    h2 { color:#2b8dfd; }
    .loading { font-size:18px; color:#555; margin-top:20px; }
  </style>
</head>
<body>

<h2>Please wait...</h2>
<p class="loading">
  <strong>Please do not close or refresh this page while we verify your payment.</strong>
</p>

<script>
window.onload = function() {
    var options = {
        "key": "{{ config('services.razorpay.key') }}",
        "amount": "{{ $amount }}",
        "currency": "INR",
        "name": "Edge Clinic",
        "description": "Bookanappointment Payment",
        "order_id": "{{ $orderId }}",
        "prefill": {
            "name": "{{ $customer['first_name'] }} {{ $customer['last_name'] }}",
            "email": "{{ $customer['email'] }}",
            "contact": "{{ $customer['phone'] }}"
        },
        "notes": {
            "industry": "{{ $customer['industry'] }}",
            "designation": "{{ $customer['designation'] ?? '' }}",
            "firmtype": "{{ $customer['firmtype'] ?? '' }}",
            "businessname": "{{ $customer['businessname'] ?? '' }}",
            "employees": "{{ $customer['employees'] ?? '' }}",

            "customer_first_name": "{{ $customer['first_name'] }}",
            "customer_last_name": "{{ $customer['last_name'] }}",
            "customer_email": "{{ $customer['email'] }}",
            "customer_phone": "{{ $customer['phone'] }}"
        },
        "theme": { "color": "#2b8dfd" },
        "handler": function (response){
            window.location.href = "{{ url('razorpay/verify') }}"
                + "?razorpay_payment_id=" + response.razorpay_payment_id
                + "&razorpay_order_id=" + response.razorpay_order_id
                + "&razorpay_signature=" + response.razorpay_signature;
        },
        "modal": {
            "ondismiss": function(){
                window.location.href = "{{ url('razorpay/failure') }}?reason=cancelled";
            }
        }
    };

    var rzp = new Razorpay(options);
    rzp.open();
};
</script>

</body>
</html>
