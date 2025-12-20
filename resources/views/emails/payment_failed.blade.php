@component('mail::message')
# Payment Failed

Hi {{ $patient_name }},

We noticed that your payment for your appointment on **{{ $appointment_date }} at {{ $appointment_time }}** was not successful.

No worries! You can easily try booking your appointment again.

@component('mail::button', ['url' => $booking_link, 'color' => 'primary'])
Book Again
@endcomponent

If you have any questions or need assistance, feel free to reply to this email or contact our support team.

Thanks,<br>
**{{ $clinic_name }}**
@endcomponent
