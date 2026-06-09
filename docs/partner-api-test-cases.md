# Edge Clinic Partner API Postman Testing

## Import Files

- Collection: `docs/partner-api-postman-collection.json`
- Environment: `docs/partner-api-postman-environment.json`

## Setup

1. Open Postman.
2. Import both files.
3. Select environment `Edge Clinic Partner API Local`.
4. Update `api_key` with the client's actual key.
5. Update `base_url` if your local URL is different.
6. Start with `01. Get Doctors`.

## Recommended Run Order

1. `01. Get Doctors`
2. `02. Get Slots`
3. `03. Book Appointment`
4. `04. Get Booking Details`
5. Negative scenarios

## Positive Scenarios

### Scenario 1: Get all doctors

- Request: `GET /doctors`
- Expected status: `200`
- Validate:
  - `data` is an array
  - each doctor has `id`, `name`, `appointment_fee`
  - if a doctor has `slots_public = true`, slots API can be tested

### Scenario 2: Filter doctors by department

- Request: `GET /doctors?department_id=...`
- Expected status: `200`
- Validate:
  - only matching doctors should be returned
  - empty array is valid if no doctors exist for that department

### Scenario 3: Get slots for one doctor

- Request: `GET /doctors/{doctor_id}/slots`
- Query:
  - `start_date`
  - `end_date`
  - `include_unavailable=1`
- Expected status: `200`
- Validate:
  - `doctor_id` matches request
  - `dates` array returned
  - each slot contains `time`, `display_time`, `status`, `is_available`

### Scenario 4: Get only available slots

- Request: `GET /doctors/{doctor_id}/slots`
- Query:
  - `start_date`
  - `end_date`
- Expected status: `200`
- Validate:
  - unavailable slots should be hidden
  - if no slots available, dates may be empty

### Scenario 5: Book appointment for new patient

- Request: `POST /appointments`
- Expected status: `201`
- Validate:
  - `message = Appointment booked successfully.`
  - response contains `payment_id`
  - response contains `appointment_no`
  - appointment status should be `Scheduled`

### Scenario 6: Book appointment for existing patient

- Reuse same `mobile` or `email`
- Change `external_booking_id`
- Expected status: `201`
- Validate:
  - booking should still succeed
  - patient should be reused/updated instead of failing

### Scenario 7: Get booking details after successful booking

- Request: `GET /appointments/{payment_id}`
- Expected status: `200`
- Validate:
  - `payment_id` matches booked record
  - doctor and patient details are present
  - appointment date and slot time are correct
  - `source` usually matches the authenticated partner client code when the `sources` table exists

## Negative Scenarios

### Scenario 8: Missing API key

- Request without `Authorization`
- Expected status: `401`
- Expected message:
  - `Unauthorized partner API request.`

### Scenario 9: Wrong API key

- Request with wrong bearer token
- Expected status: `401`
- Expected message:
  - `Invalid partner API key.`

### Scenario 10: Invalid doctor id in slots

- Request: `GET /doctors/999999/slots`
- Expected status: `404`

### Scenario 11: Missing mandatory fields while booking

- Remove `doctor_id`
- Remove `appointment_date`
- Remove `slot_time`
- Remove `patient_name`
- Remove `mobile`
- Expected status: `422`
- Validate:
  - Laravel validation errors are returned

### Scenario 12: Past appointment date

- Use a date before today
- Expected status: `422`

### Scenario 13: Invalid slot time format

- Example: `11.20` or `25:61`
- Expected status: `422`

### Scenario 14: Invalid gender

- Example: `abc`
- Expected status: `422`

### Scenario 15: Invalid email format

- Example: `wrong-email`
- Expected status: `422`

### Scenario 16: Duplicate external booking id

- Reuse same `external_booking_id`
- Expected status: `409`
- Expected message:
  - `External booking id already exists.`

### Scenario 17: Slot already booked

- Book same doctor, same date, same slot again
- Expected status:
  - `409` or `422`
- Validate:
  - API should reject duplicate occupancy

### Scenario 18: Invalid payment id for booking details

- Request: `GET /appointments/pay_api_invalid123`
- Expected status: `404`
- Expected message:
  - `Booking not found.`

## Edge Cases

### Scenario 19: Doctor is inactive

- Use inactive doctor id
- Expected status: `404` or `422` depending on endpoint

### Scenario 20: Slots are private

- Use doctor with `slots_private = 1`
- Expected:
  - slots API returns message that slots are private
  - booking API returns `422`

### Scenario 21: Include unavailable slots

- Use `include_unavailable=1`
- Validate statuses:
  - `available`
  - `booked`
  - `reserved`
  - `past`
  - `full`
  - `weekly_off`
  - `non_practice_day`

### Scenario 22: Follow-up patient with zero doctor fee

- Use a patient eligible for follow-up
- Expected:
  - booking succeeds
  - `amounts.doctor_fee = 0`
  - `is_followup = true`

### Scenario 23: Discount percentage applied

- Add `discount_percentage`
- Expected:
  - `discount_amount` calculated
  - `final_amount` reduced correctly

### Scenario 24: Registration fee included

- Use a patient where registration fee applies
- Expected:
  - `registration_fee` present in response
  - `gross_amount` includes registration fee

### Scenario 25: Source fallback when sources table is missing

- Applicable only on deployments without the `sources` table
- Expected:
  - booking still succeeds
  - booking-details response may return `"source": null`

## Sample Booking Payloads

### Basic

```json
{
  "doctor_id": 38,
  "appointment_date": "2026-05-28",
  "slot_time": "11:20",
  "patient_name": "Postman Test Patient",
  "mobile": "9000000001"
}
```

### Full Payload

```json
{
  "doctor_id": 38,
  "appointment_date": "2026-05-28",
  "slot_time": "11:20",
  "patient_name": "Postman Test Patient",
  "mobile": "9000000001",
  "email": "postman.test@example.com",
  "gender": "female",
  "dob": "1994-05-10",
  "age": "32",
  "country_code": "+91",
  "source_name": "Postman Partner",
  "discount_percentage": 10,
  "external_booking_id": "POSTMAN-10001",
  "notes": "Booked from Postman test"
}
```

## QA Notes

- Change `external_booking_id` before each fresh successful booking test.
- Use `02. Get Slots` before booking to avoid slot conflict.
- `03. Book Appointment` stores `payment_id` automatically for `04. Get Booking Details`.
- If `.env` keys were changed recently, run `php artisan optimize:clear` before testing.
