# Edge Clinic Partner API Postman Testing

## Import Files

- Collection: `docs/partner-api-postman-collection.json`
- Environment: `docs/partner-api-postman-environment.json`

## Setup

1. Open Postman.
2. Import both files.
3. Select environment `Edge Clinic Partner API Local`.
4. Update `api_key` with the partner's actual key.
5. Keep `base_url` as `http://localhost/edge-clinic/api/partner/v1` for local testing, or change it for another server.
6. Run the requests in the order shown below.

## Recommended Run Order

1. `01. Get Doctors`
2. `02. Get Slots`
3. `03. Book Appointment`
4. `04. Get Booking Details`
5. `05. Appointment Summary`
6. `06. Reschedule Appointment`
7. `07. Status Update`
8. `08. Cancel Appointment`
9. `13. Cancelled Booking Cannot Be Rescheduled`

## What The Collection Does

- Automatically captures a real `doctor_id` from the doctors API.
- Automatically captures a real free slot from the slots API.
- Automatically generates unique `external_booking_id`, `mobile`, and `email` values for booking.
- Automatically stores the returned `payment_id` for follow-up requests.
- Validates the main success and protection paths we tested live.

## Positive Scenarios

### Scenario 1: Get all doctors

- Request: `GET /doctors`
- Expected status: `200`
- Validate:
  - `data` is an array
  - at least one active doctor is returned
  - a public doctor id is captured for later requests

### Scenario 2: Get slots for one doctor

- Request: `GET /doctors/{doctor_id}/slots`
- Query:
  - `start_date`
  - `end_date`
  - `include_unavailable=1`
- Expected status: `200`
- Validate:
  - `dates` array returned
  - at least one `is_available = true` slot exists
  - the collection stores one slot for booking and another for reschedule

### Scenario 3: Book appointment

- Request: `POST /appointments`
- Expected status: `201`
- Validate:
  - booking is created
  - `payment_id` is returned
  - `appointment_no` is returned
  - `appointment_status = Scheduled`

### Scenario 4: Get booking details

- Request: `GET /appointments/{payment_id}`
- Expected status: `200`
- Validate:
  - same `payment_id` is returned
  - patient and doctor details are present
  - date and slot match the booking response

### Scenario 5: Appointment summary

- Request: `GET /appointments/summary`
- Query:
  - `external_booking_id`
  - `limit=10`
- Expected status: `200`
- Validate:
  - `data` is an array
  - the current booking is present in the summary list

### Scenario 6: Reschedule appointment

- Request: `POST /appointments/{payment_id}/reschedule`
- Expected status: `200`
- Validate:
  - updated booking object is returned
  - slot changes to the requested reschedule slot
  - new date and time are stored in collection variables

### Scenario 7: Status update

- Request: `GET /appointments/{payment_id}/status-update`
- Expected status: `200`
- Validate:
  - `payment_id` matches
  - current appointment status is returned
  - if consultation is available in the database, consultation data is returned too

### Scenario 8: Cancel appointment

- Request: `POST /appointments/{payment_id}/cancel`
- Expected status: `200`
- Validate:
  - updated booking object is returned
  - `appointment_status = Cancelled`

## Negative Scenarios

### Scenario 9: Missing API key

- Request without `Authorization`
- Expected status: `401`

### Scenario 10: Wrong API key

- Request with wrong bearer token
- Expected status: `401`

### Scenario 11: Duplicate external booking id

- Re-run booking with same `external_booking_id`
- Expected status: `409`
- Expected message:
  - `External booking id already exists.`

### Scenario 12: Invalid doctor id in slots

- Request: `GET /doctors/999999/slots`
- Expected status: `404`

### Scenario 13: Cancelled booking cannot be rescheduled

- Run after `08. Cancel Appointment`
- Request: `POST /appointments/{payment_id}/reschedule`
- Expected status: `422`
- Expected message:
  - `Cancelled appointments cannot be rescheduled.`

## Extra Manual Checks

These are not fully automated in the current collection, but they are useful:

- Missing mandatory fields in booking should return `422`
- Past appointment date should return `422`
- Invalid slot time format should return `422`
- Invalid email should return `422`
- Completed appointment should not allow cancel or reschedule
- Finalized consultation should appear in `status-update` with diagnosis, prescription, and investigation data

## Notes

- The collection is optimized for local verification with `http://localhost/edge-clinic`.
- If you want production or demo testing, only change `base_url` and `api_key`.
- `03. Book Appointment` generates unique values before sending, so you can safely rerun the flow.
- `13. Cancelled Booking Cannot Be Rescheduled` should be run only after the cancel request.
