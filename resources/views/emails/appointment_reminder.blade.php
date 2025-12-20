@component('mail::message')
# Appointment Reminder

Hi {{ $patient_name }},

This is a friendly reminder that you have an appointment scheduled for:

- **Date:** {{ $appointment_date }}
- **Time:** {{ $appointment_time }}
- **Doctor:** {{ $doctor_name }}
- **Location:** {{ $clinic_address }}

Please make sure to arrive on time. If you need to reschedule or cancel, you can do so using the link below:

@component('mail::button', ['url' => $appointment_link, 'color' => 'success'])
View / Manage Appointment
@endcomponent

We look forward to seeing you!

Thanks,<br>
**{{ $clinic_name }}**
@endcomponent
