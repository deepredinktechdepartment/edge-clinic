# Partner Appointment API

This API lets external portals:

- list active doctors
- fetch doctor slots
- book appointments directly into Edge Clinic
- check a booked appointment later

All bookings created through this API are written into the local `patients`, `payments`, `appointments`, and `appointment_status_logs` tables, so they appear in the Edge Clinic admin application immediately.

## Security

Set one or more API keys in `.env`:

```env
PARTNER_API_KEYS=portal-a:change-this-key,portal-b:change-that-key
PARTNER_API_DEFAULT_COUNTRY_CODE=+91
PARTNER_API_SOURCE_PREFIX=API
```

Send the key in either of these ways:

- `Authorization: Bearer YOUR_KEY`
- `X-API-Key: YOUR_KEY`

## Base URLs

```text
https://edge.clinic/demos/edge-clinic-v1/api/partner/v1
```

Future production:

```text
https://edge.clinic/api/partner/v1
```

## Endpoints

### 1. Get doctors

```http
GET /doctors
GET /doctors?department_id=3
```

Response shape:

```json
{
  "data": [
    {
      "id": 38,
      "name": "Ananth Egoor",
      "designation": "Consultant",
      "appointment_fee": 1,
      "followup_days": 10,
      "online_payment": false,
      "department": {
        "id": 2,
        "name": "Cardiology"
      },
      "slots_public": true,
      "advance_booking_days": 120,
      "slot_duration": 15,
      "photo": "doctor.jpg",
      "photo_url": "https://edge.clinic/demos/edge-clinic-v1/uploads/doctors/doctor.jpg"
    }
  ]
}
```

### 2. Get slots for one doctor

```http
GET /doctors/38/slots
GET /doctors/38/slots?start_date=2026-05-21&end_date=2026-05-25
GET /doctors/38/slots?start_date=2026-05-21&end_date=2026-05-21&include_unavailable=1
```

Notes:

- by default only available slots are returned
- use `include_unavailable=1` if the portal wants to show booked or blocked slots in grey
- status values can be `available`, `booked`, `reserved`, `past`, `full`, `weekly_off`, or `non_practice_day`

Response shape:

```json
{
  "doctor_id": 38,
  "doctor_name": "Ananth Egoor",
  "slots_public": true,
  "start_date": "2026-05-21",
  "end_date": "2026-05-21",
  "dates": [
    {
      "date": "2026-05-21",
      "display_date": "21 May 2026",
      "status": "available",
      "available_slots": 4,
      "total_slots": 6,
      "slots": [
        {
          "time": "10:30",
          "display_time": "10:30 AM",
          "status": "booked",
          "is_available": false
        },
        {
          "time": "10:45",
          "display_time": "10:45 AM",
          "status": "available",
          "is_available": true
        }
      ]
    }
  ]
}
```

### 3. Book appointment

```http
POST /appointments
Content-Type: application/json
```

Request body:

```json
{
  "doctor_id": 38,
  "appointment_date": "2026-05-21",
  "slot_time": "10:45",
  "patient_name": "Krishnaveni",
  "mobile": "7207589349",
  "email": "patient@example.com",
  "gender": "female",
  "age": "32",
  "source_name": "Partner Portal",
  "discount_percentage": 0,
  "external_booking_id": "PORTAL-100045",
  "notes": "Booked from external website"
}
```

Required fields:

- `doctor_id`
- `appointment_date`
- `slot_time`
- `patient_name`
- `mobile`

Optional fields:

- `email`
- `gender`
- `dob`
- `age`
- `country_code`
- `source_name`
- `discount_percentage`
- `external_booking_id`
- `notes`

Behavior:

- reuses an existing patient if mobile or email already exists
- creates the patient automatically if not found
- checks registration fee and follow-up eligibility using the same app rules as Edge Clinic
- blocks double-booking if the slot is already taken
- creates local `payments` and `appointments` rows immediately
- marks the appointment as `Scheduled`
- stores the payment as `Pending` when money is still to be collected in clinic
- marks the payment as `Authorized` automatically when the final payable amount is `0`
- uses the authenticated partner client code from the API key as the booking source label in normal cases

Source handling:

- `source_name` can still be sent by the client as a descriptive field
- the stored source is normally resolved from the authenticated partner client code such as `newmi` or `mfin`
- if the `sources` table is unavailable in a deployment, booking still works

Success response:

```json
{
  "message": "Appointment booked successfully.",
  "data": {
    "appointment_id": 6,
    "appointment_no": "APT202605201230XYZ",
    "payment_row_id": 12,
    "payment_id": "pay_api_ab12cd1045",
    "doctor_id": 38,
    "doctor_name": "Ananth Egoor",
    "patient_id": 1,
    "patient_name": "Krishnaveni",
    "appointment_date": "2026-05-21",
    "slot_time": "10:45",
    "source": "newmi",
    "amounts": {
      "doctor_fee": 1,
      "registration_fee": 1,
      "gross_amount": 2,
      "discount_percentage": 0,
      "discount_amount": 0,
      "final_amount": 2
    },
    "payment_status": "Pending",
    "appointment_status": "Scheduled",
    "is_followup": false
  }
}
```

### 4. Get booking by payment id

```http
GET /appointments/pay_api_ab12cd1045
```

Response shape:

```json
{
  "data": {
    "payment_id": "pay_api_ab12cd1045",
    "appointment_no": "APT202605201230XYZ",
    "appointment_date": "2026-05-21",
    "slot_time": "10:45",
    "payment_status": "Pending",
    "appointment_status": "Scheduled",
    "amount": 2,
    "doctor": {
      "id": 38,
      "name": "Ananth Egoor"
    },
    "patient": {
      "id": 1,
      "name": "Krishnaveni",
      "mobile": "7207589349",
      "email": "patient@example.com"
    },
    "source": "newmi"
  }
}
```

Note:

- in booking-details response, `source` may be `null` on deployments where the `sources` table does not exist

## cURL example

```bash
curl -X GET "https://edge.clinic/demos/edge-clinic-v1/api/partner/v1/doctors" \
  -H "Authorization: Bearer change-this-key"
```

```bash
curl -X GET "https://edge.clinic/demos/edge-clinic-v1/api/partner/v1/doctors/38/slots?start_date=2026-05-21&end_date=2026-05-21&include_unavailable=1" \
  -H "Authorization: Bearer change-this-key"
```

```bash
curl -X POST "https://edge.clinic/demos/edge-clinic-v1/api/partner/v1/appointments" \
  -H "Authorization: Bearer change-this-key" \
  -H "Content-Type: application/json" \
  -d "{\"doctor_id\":38,\"appointment_date\":\"2026-05-21\",\"slot_time\":\"10:45\",\"patient_name\":\"Krishnaveni\",\"mobile\":\"7207589349\",\"email\":\"patient@example.com\",\"source_name\":\"Partner Portal\",\"external_booking_id\":\"PORTAL-100045\"}"
```

## JavaScript example

```js
const API_BASE = "https://edge.clinic/demos/edge-clinic-v1/api/partner/v1";
const API_KEY = "change-this-key";

async function getDoctors() {
  const res = await fetch(`${API_BASE}/doctors`, {
    headers: {
      Authorization: `Bearer ${API_KEY}`
    }
  });

  return res.json();
}

async function getSlots(doctorId, date) {
  const res = await fetch(
    `${API_BASE}/doctors/${doctorId}/slots?start_date=${date}&end_date=${date}&include_unavailable=1`,
    {
      headers: {
        Authorization: `Bearer ${API_KEY}`
      }
    }
  );

  return res.json();
}

async function bookAppointment(payload) {
  const res = await fetch(`${API_BASE}/appointments`, {
    method: "POST",
    headers: {
      Authorization: `Bearer ${API_KEY}`,
      "Content-Type": "application/json"
    },
    body: JSON.stringify(payload)
  });

  return res.json();
}
```

## PHP example

```php
<?php

$apiBase = 'https://edge.clinic/demos/edge-clinic-v1/api/partner/v1';
$apiKey = 'change-this-key';

$payload = [
    'doctor_id' => 38,
    'appointment_date' => '2026-05-21',
    'slot_time' => '10:45',
    'patient_name' => 'Krishnaveni',
    'mobile' => '7207589349',
    'email' => 'patient@example.com',
    'source_name' => 'Partner Portal',
    'external_booking_id' => 'PORTAL-100045',
];

$ch = curl_init($apiBase . '/appointments');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode($payload),
]);

$response = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo $status . PHP_EOL;
echo $response;
```

## Python example

```python
import requests

api_base = "https://edge.clinic/demos/edge-clinic-v1/api/partner/v1"
api_key = "change-this-key"

headers = {
    "Authorization": f"Bearer {api_key}",
    "Content-Type": "application/json",
}

payload = {
    "doctor_id": 38,
    "appointment_date": "2026-05-21",
    "slot_time": "10:45",
    "patient_name": "Krishnaveni",
    "mobile": "7207589349",
    "email": "patient@example.com",
    "source_name": "Partner Portal",
    "external_booking_id": "PORTAL-100045",
}

response = requests.post(f"{api_base}/appointments", headers=headers, json=payload, timeout=30)
print(response.status_code)
print(response.json())
```

## Important integration notes

- if you want the external portal to hide unavailable slots, call the slots API without `include_unavailable`
- if you want the external portal to show blocked times in grey, call the slots API with `include_unavailable=1` and grey out anything where `is_available` is `false`
- partner bookings created by this API will reserve the slot immediately inside Edge Clinic
- if the partner portal collects money separately, you can later update payment inside Edge Clinic admin or extend this API further for payment confirmation
- use the real partner key label such as `newmi` or `mfin` as the client identity; that identity is what the API normally uses for source tracking
- booking-details `source` can be `null` only on deployments that do not have the `sources` table
