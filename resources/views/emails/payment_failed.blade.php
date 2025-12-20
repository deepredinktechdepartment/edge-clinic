@component('mail::message')
# Hi {{ $name }},

Your recent payment attempt for an appointment failed. You can try booking your appointment again by clicking the button below.

---

**Appointment Details:**  

- **Doctor:** {{ $doctorName }}
- **Date:** {{ $appointmentDate }}
- **Time:** {{ $appointmentTime }}

@component('mail::button', ['url' => $appointmentUrl])
Book Appointment
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
