@component('mail::message')
# Hi {{ $name }},

Your recent payment attempt failed. You can try booking your appointment again by clicking the button below:

@component('mail::button', ['url' => $appointmentUrl])
Book Appointment
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
