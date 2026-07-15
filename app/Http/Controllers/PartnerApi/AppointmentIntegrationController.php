<?php

namespace App\Http\Controllers\PartnerApi;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentStatusLog;
use App\Models\Doctor;
use App\Models\DoctorNonPracticeDay;
use App\Models\DoctorSession;
use App\Models\DoctorSlotSetting;
use App\Models\DoctorTimeSlot;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\RegistrationFee;
use App\Models\Source;
use App\Services\AppointmentPaymentStateService;
use App\Services\FollowupEligibilityService;
use App\Services\RegistrationFeeService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class AppointmentIntegrationController extends Controller
{
    public function doctors(Request $request): JsonResponse
    {
        $query = Doctor::query()
            ->with(['department', 'user'])
            ->where('is_active', 1)
            ->orderByRaw("TRIM(REPLACE(name, 'Dr. ', '')) ASC");

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->integer('department_id'));
        }

        $doctors = $query->get()->map(function (Doctor $doctor) {
            $slotSetting = DoctorSlotSetting::where('doctor_id', $doctor->id)->first();

            return [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'slug' => $doctor->slug,
                'designation' => $doctor->designation,
                'qualification' => $doctor->qualification,
                'experience' => $doctor->experience,
                'expertise' => $doctor->expertise,
                'appointment_fee' => (float) ($doctor->appointment_fee ?? 0),
                'followup_days' => (int) ($doctor->followup_days ?? 0),
                'online_payment' => (bool) ($doctor->online_payment ?? false),
                'department' => [
                    'id' => $doctor->department?->id,
                    'name' => $doctor->department?->name,
                ],
                'slots_public' => ! (bool) ($slotSetting?->slots_private ?? false),
                'advance_booking_days' => (int) ($slotSetting?->advance_booking_days ?? 0),
                'slot_duration' => (int) ($slotSetting?->slot_duration ?? 0),
                'photo' => $doctor->photo,
                'photo_url' => $doctor->photo ? URL::to('uploads/doctors/' . ltrim($doctor->photo, '/')) : null,
            ];
        })->values();

        return response()->json([
            'data' => $doctors,
        ]);
    }

    public function slots(Request $request, Doctor $doctor): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'include_unavailable' => 'nullable|boolean',
        ]);

        if ((int) $doctor->is_active !== 1) {
            return response()->json([
                'message' => 'Doctor is inactive.',
            ], 404);
        }

        $slotSetting = DoctorSlotSetting::where('doctor_id', $doctor->id)->first();

        if (! $slotSetting) {
            return response()->json([
                'doctor_id' => $doctor->id,
                'doctor_name' => $doctor->name,
                'slots_public' => false,
                'dates' => [],
                'message' => 'No slot configuration found for this doctor.',
            ]);
        }

        if ($slotSetting->slots_private) {
            return response()->json([
                'doctor_id' => $doctor->id,
                'doctor_name' => $doctor->name,
                'slots_public' => false,
                'dates' => [],
                'message' => 'Slots for this doctor are private and not available on partner APIs.',
            ]);
        }

        $includeUnavailable = (bool) ($validated['include_unavailable'] ?? false);

        $startDate = isset($validated['start_date'])
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : Carbon::today();

        $endDate = isset($validated['end_date'])
            ? Carbon::parse($validated['end_date'])->startOfDay()
            : Carbon::today()->copy()->addDays((int) $slotSetting->advance_booking_days);

        return response()->json([
            'doctor_id' => $doctor->id,
            'doctor_name' => $doctor->name,
            'slots_public' => true,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'dates' => $this->buildAvailabilityCalendar($doctor, $startDate, $endDate, $includeUnavailable),
        ]);
    }

    public function book(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'slot_time' => 'required|date_format:H:i',
            'patient_name' => 'required|string|max:100',
            'mobile' => 'required|string|max:15',
            'email' => 'nullable|email|max:100',
            'gender' => 'nullable|in:male,female,other,Male,Female,Other',
            'dob' => 'nullable|date|before:today',
            'age' => 'nullable|string|max:5',
            'country_code' => 'nullable|string|max:6',
            'source_name' => 'nullable|string|max:255',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'external_booking_id' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $doctor = Doctor::findOrFail($validated['doctor_id']);
        $slotSetting = DoctorSlotSetting::where('doctor_id', $doctor->id)->first();

        if ((int) $doctor->is_active !== 1 || ! $slotSetting || $slotSetting->slots_private) {
            return response()->json([
                'message' => 'Doctor is not available for partner booking.',
            ], 422);
        }

        $partnerClient = (string) $request->attributes->get('partner_client', 'partner');

        if (! empty($validated['external_booking_id'])) {
            $existingExternalBooking = Payment::query()
                ->where('payment_mode', 'partner_api')
                ->where('reference_no', $validated['external_booking_id'])
                ->first();

            if ($existingExternalBooking) {
                return response()->json([
                    'message' => 'External booking id already exists.',
                    'payment_id' => $existingExternalBooking->payment_id,
                ], 409);
            }
        }

        $appointmentDate = Carbon::parse($validated['appointment_date'])->startOfDay();
        $slotAvailability = collect($this->buildAvailabilityCalendar($doctor, $appointmentDate, $appointmentDate, true))
            ->firstWhere('date', $appointmentDate->toDateString());

        $requestedSlot = collect($slotAvailability['slots'] ?? [])
            ->firstWhere('time', $validated['slot_time']);

        if (! $requestedSlot || ! ($requestedSlot['is_available'] ?? false)) {
            return response()->json([
                'message' => 'Selected slot is not available.',
                'slot' => $requestedSlot,
            ], 422);
        }

        $patient = $this->findOrCreatePatient($validated, $partnerClient);
        $source = $this->resolveSource($validated['source_name'] ?? null, $partnerClient);

        $regData = app(RegistrationFeeService::class)->check($patient->id);
        $followup = app(FollowupEligibilityService::class)
            ->check($doctor->id, $patient->id, (int) $doctor->followup_days);

        $doctorFee = $followup['eligible'] ? 0 : (float) ($doctor->appointment_fee ?? 0);
        $registrationFee = (float) ($regData['amount'] ?? 0);
        $grossAmount = round($doctorFee + $registrationFee, 2);
        $discountPercentage = round((float) ($validated['discount_percentage'] ?? 0), 2);
        $discountAmount = round(($grossAmount * $discountPercentage) / 100, 2);
        $finalAmount = round(max($grossAmount - $discountAmount, 0), 2);
        $isFollowup = $followup['eligible'] ? 1 : 0;
        $mainVisitId = $followup['eligible'] ? $followup['main_visit_id'] : null;
        $resolvedPaymentState = app(AppointmentPaymentStateService::class)->resolve(
            $source?->id,
            $partnerClient,
            $finalAmount <= 0 ? 'Authorized' : 'Pending',
            $finalAmount <= 0 ? 'success' : 'initiated',
            $finalAmount <= 0 ? now() : null
        );
        $paymentStatus = $resolvedPaymentState['payment_status'];
        $appointmentPaymentStatus = $resolvedPaymentState['appointment_payment_status'];
        $paymentDate = $resolvedPaymentState['payment_date'];

        $timeKey = str_replace(':', '', $validated['slot_time']);
        $paymentId = 'pay_api_' . strtolower(Str::random(6)) . $timeKey;
        $orderId = 'API_' . strtoupper(Str::random(6)) . $timeKey;
        $appointmentNo = 'APT' . now()->format('YmdHis') . strtoupper(Str::random(3));
        $referenceNo = $validated['external_booking_id'] ?? ('API_REF_' . strtoupper(Str::random(8)));
        $notesPayload = [
            'partner_client' => $partnerClient,
            'external_booking_id' => $validated['external_booking_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ];

        DB::beginTransaction();

        try {
            $alreadyBooked = Appointment::query()
                ->where('doctor_id', $doctor->id)
                ->whereDate('date', $appointmentDate->toDateString())
                ->where('time_slot', $validated['slot_time'])
                ->where('appointment_status', '!=', 'Cancelled')
                ->lockForUpdate()
                ->exists();

            if ($alreadyBooked) {
                DB::rollBack();

                return response()->json([
                    'message' => 'Selected slot was just booked by someone else.',
                ], 409);
            }

            $paymentRowId = DB::table('payments')->insertGetId([
                'mocdoc_apptkey' => $appointmentNo,
                'user_id' => (int) ($patient->user_id ?? 0),
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'source_id' => $source?->id,
                'payment_id' => $paymentId,
                'order_id' => $orderId,
                'amount' => $finalAmount,
                'currency' => 'INR',
                'status' => $paymentStatus,
                'email' => $patient->email,
                'phone' => $patient->mobile,
                'payment_mode' => 'partner_api',
                'aptDate' => $appointmentDate->format('Ymd'),
                'aptTime' => $validated['slot_time'],
                'reference_no' => $referenceNo,
                'type' => 'appointment',
                'notes' => json_encode($notesPayload),
                'appointment_status' => 'Scheduled',
                'doctor_fee' => $doctorFee,
                'is_followup' => $isFollowup,
                'main_visit_id' => $mainVisitId,
                'registration_fee' => $registrationFee,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $discountAmount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $appointmentId = DB::table('appointments')->insertGetId([
                'appointment_no' => $appointmentNo,
                'doctor_id' => $doctor->id,
                'patient_id' => $patient->id,
                'date' => $appointmentDate->toDateString(),
                'time_slot' => $validated['slot_time'],
                'fee' => $finalAmount,
                'payment_id' => $paymentId,
                'payment_status' => $appointmentPaymentStatus,
                'appointment_status' => 'Scheduled',
                'payment_method' => 'partner_api',
                'currency' => 'INR',
                'payment_date' => $paymentDate,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            AppointmentStatusLog::create([
                'appointment_no' => $appointmentNo,
                'appointment_id' => $appointmentId,
                'to_status' => 'Booked',
                'remarks' => 'Booked via partner API',
                'changed_by' => null,
                'changedName' => $partnerClient,
                'ip_address' => $request->ip(),
            ]);

            if ($regData['apply']) {
                $validityDays = (int) RegistrationFee::where('is_active', 1)->value('validity_days');

                if ($validityDays > 0) {
                    $patient->update([
                        'registration_valid_till' => now()->addDays($validityDays)->toDateString(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Appointment booked successfully.',
                'data' => [
                    'appointment_id' => $appointmentId,
                    'appointment_no' => $appointmentNo,
                    'payment_row_id' => $paymentRowId,
                    'payment_id' => $paymentId,
                    'doctor_id' => $doctor->id,
                    'doctor_name' => $doctor->name,
                    'patient_id' => $patient->id,
                    'patient_name' => $patient->name,
                    'appointment_date' => $appointmentDate->toDateString(),
                    'slot_time' => $validated['slot_time'],
                    'source' => $source?->name ?? $partnerClient,
                    'amounts' => [
                        'doctor_fee' => $doctorFee,
                        'registration_fee' => $registrationFee,
                        'gross_amount' => $grossAmount,
                        'discount_percentage' => $discountPercentage,
                        'discount_amount' => $discountAmount,
                        'final_amount' => $finalAmount,
                    ],
                    'payment_status' => $paymentStatus,
                    'appointment_status' => 'Scheduled',
                    'is_followup' => (bool) $isFollowup,
                ],
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function booking(Request $request, string $paymentId): JsonResponse
    {
        $payment = $this->findPartnerPaymentByPublicId($paymentId);

        if (! $payment || ! $this->partnerCanAccessPayment($request, $payment->notes ?? null)) {
            return response()->json([
                'message' => 'Booking not found.',
            ], 404);
        }

        return response()->json([
            'data' => $this->transformPartnerPayment($payment),
        ]);
    }

    public function reschedule(Request $request, string $paymentId): JsonResponse
    {
        $validated = $request->validate([
            'appointment_date' => 'required|date|after_or_equal:today',
            'slot_time' => 'required|date_format:H:i',
            'reason' => 'nullable|string|max:255',
        ]);

        $payment = Payment::query()
            ->where('payment_id', $paymentId)
            ->where('payment_mode', 'partner_api')
            ->first();

        if (! $payment || ! $this->partnerCanAccessPayment($request, $payment->notes)) {
            return response()->json([
                'message' => 'Booking not found.',
            ], 404);
        }

        if (($payment->appointment_status ?? 'Scheduled') === 'Completed') {
            return response()->json([
                'message' => 'Completed appointments cannot be rescheduled.',
            ], 422);
        }

        if (($payment->appointment_status ?? 'Scheduled') === 'Cancelled') {
            return response()->json([
                'message' => 'Cancelled appointments cannot be rescheduled.',
            ], 422);
        }

        $doctor = Doctor::find($payment->doctor_id);
        if (! $doctor || (int) $doctor->is_active !== 1) {
            return response()->json([
                'message' => 'Doctor is not available for rescheduling.',
            ], 422);
        }

        $appointmentRow = Appointment::query()
            ->where('payment_id', $payment->payment_id)
            ->first();

        if (! $appointmentRow) {
            return response()->json([
                'message' => 'Linked appointment record not found.',
            ], 404);
        }

        $requestedDate = Carbon::parse($validated['appointment_date'])->startOfDay();
        $slotAvailability = collect($this->buildAvailabilityCalendar($doctor, $requestedDate, $requestedDate, true))
            ->firstWhere('date', $requestedDate->toDateString());

        $requestedSlot = collect($slotAvailability['slots'] ?? [])
            ->firstWhere('time', $validated['slot_time']);

        if (! $requestedSlot || ! ($requestedSlot['is_available'] ?? false)) {
            return response()->json([
                'message' => 'Selected slot is not available for this doctor.',
                'slot' => $requestedSlot,
            ], 422);
        }

        DB::beginTransaction();

        try {
            $oldDate = $payment->aptDate;
            $oldTime = $payment->aptTime;
            $reason = trim((string) ($validated['reason'] ?? ''));

            $payment->update([
                'aptDate' => $requestedDate->format('Ymd'),
                'aptTime' => $validated['slot_time'],
                'remarks' => trim('Rescheduled via partner API on ' . now()->format('d M Y h:i A') . ($reason !== '' ? '. ' . $reason : '')),
                'updated_at' => now(),
            ]);

            $appointmentRow->update([
                'date' => $requestedDate->toDateString(),
                'time_slot' => $validated['slot_time'],
                'updated_at' => now(),
            ]);

            AppointmentStatusLog::create([
                'appointment_no' => $appointmentRow->appointment_no ?? $payment->mocdoc_apptkey,
                'appointment_id' => $appointmentRow->id,
                'from_status' => $payment->appointment_status ?? 'Scheduled',
                'to_status' => $payment->appointment_status ?? 'Scheduled',
                'remarks' => 'Rescheduled from ' . $this->formatAppointmentDateTime($oldDate, $oldTime) . ' to ' . $this->formatAppointmentDateTime($requestedDate->toDateString(), $validated['slot_time']) . ($reason !== '' ? ' - ' . $reason : ''),
                'changed_by' => null,
                'changedName' => 'Partner API',
                'ip_address' => $request->ip(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $payment = $this->findPartnerPaymentByPublicId($paymentId);

        return response()->json([
            'message' => 'Appointment rescheduled successfully.',
            'data' => $this->transformPartnerPayment($payment),
        ]);
    }

    public function cancel(Request $request, string $paymentId): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $payment = Payment::query()
            ->where('payment_id', $paymentId)
            ->where('payment_mode', 'partner_api')
            ->first();

        if (! $payment || ! $this->partnerCanAccessPayment($request, $payment->notes)) {
            return response()->json([
                'message' => 'Booking not found.',
            ], 404);
        }

        if (($payment->appointment_status ?? 'Scheduled') === 'Completed') {
            return response()->json([
                'message' => 'Completed appointments cannot be cancelled.',
            ], 422);
        }

        if (($payment->appointment_status ?? 'Scheduled') === 'Cancelled') {
            return response()->json([
                'message' => 'Appointment is already cancelled.',
                'data' => $this->transformPartnerPayment($this->findPartnerPaymentByPublicId($paymentId)),
            ]);
        }

        $appointmentRow = Appointment::query()
            ->where('payment_id', $payment->payment_id)
            ->first();

        $oldStatus = $payment->appointment_status ?? 'Scheduled';
        $reason = trim((string) ($validated['reason'] ?? 'Cancelled via partner API'));

        DB::beginTransaction();

        try {
            $payment->update([
                'appointment_status' => 'Cancelled',
                'remarks' => $reason,
                'updated_at' => now(),
            ]);

            if ($appointmentRow) {
                $appointmentRow->update([
                    'appointment_status' => 'Cancelled',
                    'updated_at' => now(),
                ]);
            }

            AppointmentStatusLog::create([
                'appointment_no' => $appointmentRow->appointment_no ?? $payment->mocdoc_apptkey,
                'appointment_id' => $appointmentRow->id ?? null,
                'from_status' => $oldStatus,
                'to_status' => 'Cancelled',
                'remarks' => $reason,
                'changed_by' => null,
                'changedName' => 'Partner API',
                'ip_address' => $request->ip(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $payment = $this->findPartnerPaymentByPublicId($paymentId);

        return response()->json([
            'message' => 'Appointment cancelled successfully.',
            'data' => $this->transformPartnerPayment($payment),
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|string|max:50',
            'doctor_id' => 'nullable|integer|exists:doctors,id',
            'external_booking_id' => 'nullable|string|max:100',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Payment::query()
            ->with(['doctor', 'patient'])
            ->where('payment_mode', 'partner_api')
            ->orderByDesc('id');

        if (! empty($validated['start_date'])) {
            $query->where('aptDate', '>=', Carbon::parse($validated['start_date'])->format('Ymd'));
        }

        if (! empty($validated['end_date'])) {
            $query->where('aptDate', '<=', Carbon::parse($validated['end_date'])->format('Ymd'));
        }

        if (! empty($validated['status'])) {
            $query->where('appointment_status', $validated['status']);
        }

        if (! empty($validated['doctor_id'])) {
            $query->where('doctor_id', $validated['doctor_id']);
        }

        if (! empty($validated['external_booking_id'])) {
            $query->where('reference_no', $validated['external_booking_id']);
        }

        $payments = $query
            ->limit((int) ($validated['limit'] ?? 50))
            ->get();

        $partnerClient = strtolower((string) $request->attributes->get('partner_client', ''));

        if ($partnerClient !== '') {
            $payments = $payments->filter(function (Payment $payment) use ($partnerClient) {
                return strtolower((string) data_get($this->decodePartnerNotes($payment->notes), 'partner_client', '')) === $partnerClient;
            })->values();
        }

        $appointmentNumbers = Appointment::query()
            ->whereIn('payment_id', $payments->pluck('payment_id')->filter()->all())
            ->pluck('appointment_no', 'payment_id');

        return response()->json([
            'filters' => [
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'status' => $validated['status'] ?? null,
                'doctor_id' => $validated['doctor_id'] ?? null,
                'external_booking_id' => $validated['external_booking_id'] ?? null,
                'limit' => (int) ($validated['limit'] ?? 50),
            ],
            'count' => $payments->count(),
            'data' => $payments->map(function (Payment $payment) use ($appointmentNumbers) {
                $notes = $this->decodePartnerNotes($payment->notes);

                return [
                    'payment_id' => $payment->payment_id,
                    'appointment_no' => $appointmentNumbers[$payment->payment_id] ?? $payment->mocdoc_apptkey,
                    'appointment_date' => $this->formatAppointmentDate($payment->aptDate),
                    'slot_time' => $payment->aptTime,
                    'appointment_status' => $payment->appointment_status,
                    'payment_status' => $payment->status,
                    'amount' => (float) ($payment->amount ?? 0),
                    'doctor' => [
                        'id' => $payment->doctor?->id,
                        'name' => $payment->doctor?->name,
                    ],
                    'patient' => [
                        'id' => $payment->patient?->id,
                        'name' => $payment->patient?->name,
                        'mobile' => $payment->patient?->mobile,
                    ],
                    'external_booking_id' => $notes['external_booking_id'] ?? $payment->reference_no,
                    'partner_client' => $notes['partner_client'] ?? null,
                ];
            })->values(),
        ]);
    }

    public function statusUpdate(Request $request, string $paymentId): JsonResponse
    {
        $payment = Payment::query()
            ->with([
                'doctor',
                'patient',
                'consultation.diagnoses',
                'consultation.prescriptions',
                'consultation.investigations',
            ])
            ->where('payment_id', $paymentId)
            ->where('payment_mode', 'partner_api')
            ->first();

        if (! $payment || ! $this->partnerCanAccessPayment($request, $payment->notes)) {
            return response()->json([
                'message' => 'Booking not found.',
            ], 404);
        }

        $consultation = $payment->consultation;

        return response()->json([
            'data' => [
                'payment_id' => $payment->payment_id,
                'appointment_date' => $this->formatAppointmentDate($payment->aptDate),
                'slot_time' => $payment->aptTime,
                'appointment_status' => $payment->appointment_status,
                'payment_status' => $payment->status,
                'attended' => in_array((string) $payment->appointment_status, ['Checked-In', 'In-Consultation', 'Completed'], true),
                'doctor' => [
                    'id' => $payment->doctor?->id,
                    'name' => $payment->doctor?->name,
                ],
                'patient' => [
                    'id' => $payment->patient?->id,
                    'name' => $payment->patient?->name,
                    'mobile' => $payment->patient?->mobile,
                ],
                'consultation' => $consultation ? [
                    'id' => $consultation->id,
                    'status' => $consultation->status,
                    'visit_date' => optional($consultation->visit_date)->toDateString(),
                    'visit_time' => $consultation->visit_time,
                    'advice' => $consultation->advice,
                    'follow_up_label' => $consultation->follow_up_label,
                    'follow_up_date' => optional($consultation->follow_up_date)->toDateString(),
                    'diagnoses' => $consultation->diagnoses->pluck('diagnosis_name')->filter()->values(),
                    'investigations' => $consultation->investigations->pluck('test_name')->filter()->values(),
                    'prescriptions' => $consultation->prescriptions->map(function ($prescription) {
                        return [
                            'medicine_name' => $prescription->medicine_name,
                            'pack' => $prescription->pack,
                            'frequency' => $prescription->frequency,
                            'duration' => $prescription->duration,
                            'instruction' => $prescription->instruction,
                            'details' => $prescription->details,
                        ];
                    })->values(),
                ] : null,
            ],
        ]);
    }

    private function buildAvailabilityCalendar(Doctor $doctor, Carbon $startDate, Carbon $endDate, bool $includeUnavailable): array
    {
        $slotSetting = DoctorSlotSetting::where('doctor_id', $doctor->id)->first();
        $sessions = DoctorSession::where('doctor_id', $doctor->id)->get();
        $weeklySlots = DoctorTimeSlot::where('doctor_id', $doctor->id)->get();
        $nonPracticeDays = DoctorNonPracticeDay::where('doctor_id', $doctor->id)
            ->pluck('marked_date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->toArray();

        if (! $slotSetting || $sessions->isEmpty()) {
            return [];
        }

        $dates = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayOfWeekMap = [6 => 0, 0 => 1, 1 => 2, 2 => 3, 3 => 4, 4 => 5, 5 => 6];
            $dbDay = $dayOfWeekMap[$date->dayOfWeek];
            $dateString = $date->toDateString();
            $dateKey = $date->format('Ymd');

            if (in_array($dateString, $nonPracticeDays, true)) {
                if ($includeUnavailable) {
                    $dates[] = [
                        'date' => $dateString,
                        'display_date' => $date->format('d M Y'),
                        'status' => 'non_practice_day',
                        'available_slots' => 0,
                        'total_slots' => 0,
                        'slots' => [],
                    ];
                }
                continue;
            }

            $isWeeklyOff = $weeklySlots
                ->where('day_of_week', $dbDay)
                ->contains(fn ($slot) => (int) $slot->is_weekly_off === 1);

            if ($isWeeklyOff) {
                if ($includeUnavailable) {
                    $dates[] = [
                        'date' => $dateString,
                        'display_date' => $date->format('d M Y'),
                        'status' => 'weekly_off',
                        'available_slots' => 0,
                        'total_slots' => 0,
                        'slots' => [],
                    ];
                }
                continue;
            }

            $reservedSlots = $weeklySlots
                ->where('day_of_week', $dbDay)
                ->where('is_weekly_off', 0)
                ->where('is_reserved', 1)
                ->map(fn ($slot) => Carbon::createFromFormat('H:i:s', $slot->slot_time)->format('H:i'))
                ->values()
                ->all();

            $bookedSlots = Appointment::query()
                ->where('doctor_id', $doctor->id)
                ->whereDate('date', $dateString)
                ->where('appointment_status', '!=', 'Cancelled')
                ->pluck('time_slot')
                ->map(function ($time) {
                    try {
                        return Carbon::parse($time)->format('H:i');
                    } catch (\Throwable $e) {
                        return substr((string) $time, 0, 5);
                    }
                })
                ->unique()
                ->values()
                ->all();

            $slots = [];

            foreach ($sessions as $session) {
                $cursor = Carbon::createFromFormat('H:i:s', $session->start_time);
                $sessionEnd = Carbon::createFromFormat('H:i:s', $session->end_time);

                while ($cursor->lt($sessionEnd)) {
                    $slotTime = $cursor->format('H:i');
                    $status = 'available';

                    if ($date->isToday() && $cursor->lt(now())) {
                        $status = 'past';
                    }

                    if ($session->break_enabled && $session->break_start && $session->break_end) {
                        $breakStart = Carbon::createFromFormat('H:i:s', $session->break_start);
                        $breakEnd = Carbon::createFromFormat('H:i:s', $session->break_end);

                        if ($cursor->gte($breakStart) && $cursor->lt($breakEnd)) {
                            $cursor->addMinutes((int) $slotSetting->slot_duration);
                            continue;
                        }
                    }

                    if (in_array($slotTime, $reservedSlots, true)) {
                        $status = 'reserved';
                    }

                    if (in_array($slotTime, $bookedSlots, true)) {
                        $status = 'booked';
                    }

                    $slot = [
                        'time' => $slotTime,
                        'display_time' => Carbon::createFromFormat('H:i', $slotTime)->format('h:i A'),
                        'status' => $status,
                        'is_available' => $status === 'available',
                    ];

                    if ($includeUnavailable || $slot['is_available']) {
                        $slots[$slotTime] = $slot;
                    }

                    $cursor->addMinutes((int) $slotSetting->slot_duration);
                }
            }

            ksort($slots);
            $slotValues = array_values($slots);
            $availableCount = collect($slotValues)->where('is_available', true)->count();

            if ($availableCount === 0 && ! $includeUnavailable) {
                continue;
            }

            $dates[] = [
                'date' => $dateString,
                'display_date' => $date->format('d M Y'),
                'status' => $availableCount > 0 ? 'available' : 'full',
                'available_slots' => $availableCount,
                'total_slots' => count($slotValues),
                'slots' => $slotValues,
            ];
        }

        return $dates;
    }

    private function findOrCreatePatient(array $validated, string $partnerClient): Patient
    {
        $patient = Patient::query()
            ->when(
                ! empty($validated['mobile']),
                fn ($query) => $query->where('mobile', $validated['mobile'])
            )
            ->when(
                ! empty($validated['email']),
                fn ($query) => $query->orWhere('email', $validated['email'])
            )
            ->first();

        $payload = [
            'name' => $validated['patient_name'],
            'mobile' => $validated['mobile'],
            'email' => $validated['email'] ?? null,
            'gender' => isset($validated['gender']) ? ucfirst(strtolower($validated['gender'])) : null,
            'dob' => $validated['dob'] ?? null,
            'age' => $validated['age'] ?? null,
            'country_code' => $validated['country_code'] ?? config('partner_api.default_country_code', '+91'),
            'bookingfor' => 'self',
            'registration_source' => 'partner_api',
            'createdBy' => $partnerClient,
            'createdId' => 0,
            'stage' => 'patient_created',
        ];

        if ($patient) {
            $patient->fill(array_filter($payload, fn ($value) => $value !== null && $value !== ''));
            $patient->save();
            return $patient;
        }

        return Patient::create($payload + [
            'user_id' => 0,
            'is_primary_account' => 1,
            'ipAddress' => request()->ip(),
            'stages' => json_encode([
                [
                    'stage' => 'patient_created',
                    'at' => now()->toDateTimeString(),
                    'source' => $partnerClient,
                ],
            ]),
        ]);
    }

    private function resolveSource(?string $sourceName, string $partnerClient): ?Source
    {
        if (! Schema::hasTable('sources')) {
            return null;
        }

        $resolvedName = trim($partnerClient);

        if ($resolvedName === '') {
            $resolvedName = trim((string) $sourceName);
        }

        if ($resolvedName === '') {
            $resolvedName = trim(config('partner_api.source_prefix', 'API') . ' - ' . $partnerClient);
        }

        return Source::firstOrCreate(
            ['name' => $resolvedName],
            [
                'description' => 'Created automatically from partner API booking.',
                'status' => true,
            ]
        );
    }

    private function findPartnerPaymentByPublicId(string $paymentId): ?object
    {
        $paymentQuery = Payment::query()
            ->leftJoin('doctors', 'doctors.id', '=', 'payments.doctor_id')
            ->leftJoin('patients', 'patients.id', '=', 'payments.patient_id')
            ->leftJoin('appointments', 'appointments.payment_id', '=', 'payments.payment_id')
            ->where('payments.payment_id', $paymentId)
            ->where('payments.payment_mode', 'partner_api');

        $select = [
            'payments.payment_id',
            'payments.amount',
            'payments.status as payment_status',
            'payments.aptDate',
            'payments.aptTime',
            'payments.appointment_status',
            'payments.reference_no',
            'payments.notes',
            'patients.id as patient_id',
            'patients.name as patient_name',
            'patients.mobile as patient_mobile',
            'patients.email as patient_email',
            'doctors.id as doctor_id',
            'doctors.name as doctor_name',
            'appointments.appointment_no',
        ];

        if (Schema::hasTable('sources')) {
            $paymentQuery->leftJoin('sources', 'sources.id', '=', 'payments.source_id');
            $select[] = 'sources.name as source_name';
        }

        return $paymentQuery
            ->select($select)
            ->first();
    }

    private function transformPartnerPayment(object $payment): array
    {
        $notes = $this->decodePartnerNotes($payment->notes ?? null);

        return [
            'payment_id' => $payment->payment_id,
            'appointment_no' => $payment->appointment_no,
            'appointment_date' => $this->formatAppointmentDate($payment->aptDate ?? null),
            'slot_time' => $payment->aptTime,
            'payment_status' => $payment->payment_status,
            'appointment_status' => $payment->appointment_status,
            'amount' => (float) ($payment->amount ?? 0),
            'doctor' => [
                'id' => $payment->doctor_id,
                'name' => $payment->doctor_name,
            ],
            'patient' => [
                'id' => $payment->patient_id,
                'name' => $payment->patient_name,
                'mobile' => $payment->patient_mobile,
                'email' => $payment->patient_email,
            ],
            'source' => $payment->source_name ?? null,
            'external_booking_id' => $notes['external_booking_id'] ?? $payment->reference_no ?? null,
            'partner_client' => $notes['partner_client'] ?? null,
        ];
    }

    private function decodePartnerNotes(?string $notes): array
    {
        if (! is_string($notes) || trim($notes) === '') {
            return [];
        }

        $decoded = json_decode($notes, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function partnerCanAccessPayment(Request $request, ?string $notes): bool
    {
        $partnerClient = strtolower((string) $request->attributes->get('partner_client', ''));

        if ($partnerClient === '') {
            return true;
        }

        $notesPartnerClient = strtolower((string) data_get($this->decodePartnerNotes($notes), 'partner_client', ''));

        if ($notesPartnerClient === '') {
            return true;
        }

        return $notesPartnerClient === $partnerClient;
    }

    private function formatAppointmentDate(?string $aptDate): ?string
    {
        if (! $aptDate) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Ymd', $aptDate)->toDateString();
        } catch (\Throwable $e) {
            try {
                return Carbon::parse($aptDate)->toDateString();
            } catch (\Throwable $e) {
                return null;
            }
        }
    }

    private function formatAppointmentDateTime(?string $date, ?string $time): string
    {
        $formattedDate = $date ? ($this->formatAppointmentDate(preg_match('/^\d{8}$/', $date) ? $date : Carbon::parse($date)->format('Ymd')) ?? $date) : '-';
        $formattedTime = $time ?: '-';

        try {
            $formattedTime = Carbon::parse($time)->format('h:i A');
        } catch (\Throwable $e) {
            $formattedTime = $time ?: '-';
        }

        return trim($formattedDate . ' ' . $formattedTime);
    }
}
