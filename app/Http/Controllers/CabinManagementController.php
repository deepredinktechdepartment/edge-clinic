<?php

namespace App\Http\Controllers;

use App\Models\Cabin;
use App\Models\CabinBooking;
use App\Models\CabinFacility;
use App\Models\CabinSetting;
use App\Models\CabinSubscription;
use App\Models\CabinInvoice;
use App\Models\CabinInvoiceItem;
use App\Models\Doctor;
use App\Models\DoctorSession;
use App\Models\DoctorSlotSetting;
use App\Models\DoctorTimeSlot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;

class CabinManagementController extends Controller
{
    public function dashboard()
    {
        $today = Carbon::today();
        $now = Carbon::now();
        $upcomingRenewals = $this->getUpcomingSubscriptionRenewals($today);

        $cabins = Cabin::with([
            'bookings' => function ($query) use ($today) {
                $query->whereDate('booking_date', $today)
                    ->whereIn('status', ['booked', 'completed'])
                    ->orderBy('start_time');
            },
            'bookings.doctor',
            'subscriptions' => function ($query) use ($today) {
                $query->where('status', 'active')
                    ->whereDate('start_date', '<=', $today)
                    ->whereDate('end_date', '>=', $today);
            },
            'subscriptions.doctor',
        ])->orderBy('cabin_code')->get();

        $timeline = CabinBooking::with(['cabin', 'doctor'])
            ->whereDate('booking_date', $today)
            ->whereIn('status', ['booked', 'completed'])
            ->orderBy('start_time')
            ->get();

        $activeSubscriptions = CabinSubscription::with(['cabin', 'doctor'])
            ->where('status', 'active')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->orderBy('end_date')
            ->get();

        $totals = [
            'cabins' => $cabins->count(),
            'available' => 0,
            'booked' => 0,
            'monthly' => $activeSubscriptions->count(),
            'maintenance' => 0,
        ];

        $cabinCards = $cabins->map(function (Cabin $cabin) use ($now, &$totals) {
            $status = $this->resolveCabinStatus($cabin, $now);
            $totals[$status['bucket']]++;

            return [
                'model' => $cabin,
                'status' => $status,
            ];
        });

        $monthlyRevenue = CabinBooking::whereMonth('booking_date', $today->month)
            ->whereYear('booking_date', $today->year)
            ->whereIn('status', ['booked', 'completed'])
            ->sum('total_amount')
            + CabinSubscription::whereMonth('start_date', $today->month)
            ->whereYear('start_date', $today->year)
            ->whereIn('status', ['active', 'expired'])
            ->sum('total_amount');

        $pageTitle = 'Cabin Dashboard';

        return view('cabins.dashboard', compact(
            'pageTitle',
            'cabins',
            'cabinCards',
            'timeline',
            'activeSubscriptions',
            'upcomingRenewals',
            'totals',
            'monthlyRevenue'
        ));
    }

    public function index()
    {
        $cabins = Cabin::with('facilities')
            ->withCount(['bookings', 'subscriptions'])
            ->orderBy('cabin_code')
            ->get();

        $pageTitle = 'Cabins';
        $addlink = route('admin.cabins.create');

        return view('cabins.index', compact('cabins', 'pageTitle', 'addlink'));
    }

    public function create()
    {
        $pageTitle = 'Add Cabin';
        $settings = $this->getSettings();

        return view('cabins.form', [
            'pageTitle' => $pageTitle,
            'settings' => $settings,
            'facilities' => CabinFacility::where('status', 'active')->orderBy('sort_order')->orderBy('name')->get(),
            'cabin' => new Cabin([
                'booking_mode' => 'both',
                'status' => 'available',
                'capacity' => 1,
                'available_from' => now()->toDateString(),
                'operating_start_time' => substr($settings->clinic_open_time, 0, 5),
                'operating_end_time' => substr($settings->clinic_close_time, 0, 5),
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateCabin($request);
        $facilityIds = $data['facility_ids'] ?? [];
        unset($data['facility_ids']);

        DB::transaction(function () use ($data, $facilityIds) {
            $cabin = Cabin::create($data);
            $cabin->facilities()->sync($facilityIds);
        });

        return redirect()
            ->route('admin.cabins.index')
            ->with('success', 'Cabin created successfully.');
    }

    public function edit(Cabin $cabin)
    {
        $pageTitle = 'Edit Cabin';
        $settings = $this->getSettings();
        $cabin->load('facilities');

        return view('cabins.form', [
            'pageTitle' => $pageTitle,
            'cabin' => $cabin,
            'settings' => $settings,
            'facilities' => CabinFacility::where('status', 'active')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Cabin $cabin)
    {
        $data = $this->validateCabin($request, $cabin->id);
        $facilityIds = $data['facility_ids'] ?? [];
        unset($data['facility_ids']);

        DB::transaction(function () use ($cabin, $data, $facilityIds) {
            $cabin->update($data);
            $cabin->facilities()->sync($facilityIds);
        });

        return redirect()
            ->route('admin.cabins.index')
            ->with('success', 'Cabin updated successfully.');
    }

    public function show(Cabin $cabin)
    {
        $cabin->load([
            'facilities',
            'bookings' => fn ($query) => $query->with('doctor')->latest('booking_date')->latest('start_time')->limit(10),
            'subscriptions' => fn ($query) => $query->with('doctor')->latest('start_date')->limit(10),
        ]);

        $pageTitle = 'Cabin View';

        return view('cabins.show', compact('pageTitle', 'cabin'));
    }

    public function destroy(Cabin $cabin)
    {
        if ($cabin->bookings()->exists() || $cabin->subscriptions()->exists()) {
            return back()->with('error', 'This cabin already has bookings or subscriptions, so it cannot be deleted.');
        }

        $cabin->delete();

        return redirect()
            ->route('admin.cabins.index')
            ->with('success', 'Cabin deleted successfully.');
    }

    public function bookings()
    {
        $bookings = CabinBooking::with(['cabin', 'doctor'])
            ->orderByDesc('booking_date')
            ->orderBy('start_time')
            ->get();

        $pageTitle = 'Cabin Bookings';
        $addlink = route('admin.cabins.bookings.create');

        return view('cabins.bookings.index', compact('bookings', 'pageTitle', 'addlink'));
    }

    public function createBooking()
    {
        $pageTitle = 'Add Cabin Booking';
        $settings = $this->getSettings();

        return view('cabins.bookings.form', [
            'pageTitle' => $pageTitle,
            'booking' => new CabinBooking([
                'booking_date' => now()->toDateString(),
                'start_time' => substr($settings->clinic_open_time, 0, 5),
                'end_time' => substr($settings->clinic_close_time, 0, 5),
                'booking_type' => 'hourly',
                'payment_choice' => 'pay_later',
                'payment_status' => 'Pending',
                'status' => 'booked',
            ]),
            'cabins' => Cabin::orderBy('cabin_code')->get(),
            'doctors' => Doctor::orderBy('name')->get(),
            'settings' => $settings,
        ]);
    }

    public function bookingAvailability(Request $request)
    {
        $validated = $request->validate([
            'cabin_id' => 'required|exists:cabins,id',
            'booking_date' => 'required|date',
            'booking_id' => 'nullable|exists:cabin_bookings,id',
        ]);

        $settings = $this->getSettings();
        $cabin = Cabin::findOrFail($validated['cabin_id']);
        $date = Carbon::parse($validated['booking_date'])->toDateString();
        $windowStart = substr((string) ($cabin->operating_start_time ?: $settings->clinic_open_time), 0, 5);
        $windowEnd = substr((string) ($cabin->operating_end_time ?: $settings->clinic_close_time), 0, 5);

        $bookings = CabinBooking::with('doctor')
            ->where('cabin_id', $cabin->id)
            ->whereDate('booking_date', $date)
            ->whereIn('status', ['booked', 'completed'])
            ->when($validated['booking_id'] ?? null, fn ($query, $bookingId) => $query->where('id', '!=', $bookingId))
            ->orderBy('start_time')
            ->get();

        $subscriptions = CabinSubscription::with('doctor')
            ->where('cabin_id', $cabin->id)
            ->where('status', 'active')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->orderBy('subscription_start_time')
            ->get();

        $segments = [];
        $cursor = Carbon::createFromFormat('H:i', $windowStart);
        $windowClose = Carbon::createFromFormat('H:i', $windowEnd);

        while ($cursor->lt($windowClose)) {
            $next = $cursor->copy()->addHour();
            if ($next->gt($windowClose)) {
                $next = $windowClose->copy();
            }

            $segmentStart = $cursor->format('H:i');
            $segmentEnd = $next->format('H:i');

            $blockingBooking = $bookings->first(function (CabinBooking $booking) use ($segmentStart, $segmentEnd) {
                return $this->timeRangesOverlap(
                    $segmentStart,
                    $segmentEnd,
                    substr((string) $booking->start_time, 0, 5),
                    substr((string) $booking->end_time, 0, 5)
                );
            });

            $blockingSubscription = $subscriptions->first(function (CabinSubscription $subscription) use ($segmentStart, $segmentEnd, $cabin, $settings) {
                return $this->timeRangesOverlap(
                    $segmentStart,
                    $segmentEnd,
                    $this->resolveSubscriptionWindowStart($subscription, $cabin, $settings),
                    $this->resolveSubscriptionWindowEnd($subscription, $cabin, $settings)
                );
            });

            $segments[] = [
                'start' => $segmentStart,
                'end' => $segmentEnd,
                'label' => $cursor->format('h:i A') . ' - ' . $next->format('h:i A'),
                'status' => $blockingBooking || $blockingSubscription ? 'blocked' : 'available',
                'note' => $blockingBooking
                    ? 'Booked' . ($blockingBooking->doctor?->name ? ' · ' . $blockingBooking->doctor->name : '')
                    : ($blockingSubscription
                        ? 'Subscription' . ($blockingSubscription->doctor?->name ? ' · ' . $blockingSubscription->doctor->name : '')
                        : 'Available'),
            ];

            $cursor = $next->copy();
        }

        return response()->json([
            'window_start' => $windowStart,
            'window_end' => $windowEnd,
            'segments' => $segments,
            'bookings' => $bookings->map(fn (CabinBooking $booking) => [
                'label' => substr((string) $booking->start_time, 0, 5) . ' - ' . substr((string) $booking->end_time, 0, 5),
                'note' => 'Booked' . ($booking->doctor?->name ? ' · ' . $booking->doctor->name : ''),
            ])->values(),
            'subscriptions' => $subscriptions->map(fn (CabinSubscription $subscription) => [
                'label' => $this->resolveSubscriptionWindowStart($subscription, $cabin, $settings) . ' - ' . $this->resolveSubscriptionWindowEnd($subscription, $cabin, $settings),
                'note' => 'Subscription' . ($subscription->doctor?->name ? ' · ' . $subscription->doctor->name : ''),
            ])->values(),
        ]);
    }

    public function storeBooking(Request $request)
    {
        $data = $this->validateBooking($request);
        CabinBooking::create($data);

        return redirect()
            ->route('admin.cabins.bookings.index')
            ->with('success', 'Cabin booking created successfully.');
    }

    public function bookingAvailabilityTimeline(Request $request)
    {
        $validated = $request->validate([
            'cabin_id' => 'required|exists:cabins,id',
            'booking_date' => 'required|date',
            'booking_id' => 'nullable|exists:cabin_bookings,id',
        ]);

        $settings = $this->getSettings();
        $cabin = Cabin::findOrFail($validated['cabin_id']);
        $date = Carbon::parse($validated['booking_date'])->toDateString();
        $windowStart = substr((string) ($cabin->operating_start_time ?: $settings->clinic_open_time), 0, 5);
        $windowEnd = substr((string) ($cabin->operating_end_time ?: $settings->clinic_close_time), 0, 5);

        $bookings = CabinBooking::with('doctor')
            ->where('cabin_id', $cabin->id)
            ->whereDate('booking_date', $date)
            ->whereIn('status', ['booked', 'completed'])
            ->when($validated['booking_id'] ?? null, fn ($query, $bookingId) => $query->where('id', '!=', $bookingId))
            ->orderBy('start_time')
            ->get();

        $subscriptions = CabinSubscription::with('doctor')
            ->where('cabin_id', $cabin->id)
            ->where('status', 'active')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->orderBy('subscription_start_time')
            ->get();

        $windowOpen = Carbon::createFromFormat('H:i', $windowStart);
        $windowClose = Carbon::createFromFormat('H:i', $windowEnd);
        $boundaries = [$windowStart, $windowEnd];

        foreach ($bookings as $booking) {
            $start = substr((string) $booking->start_time, 0, 5);
            $end = substr((string) $booking->end_time, 0, 5);

            if ($this->timeRangesOverlap($windowStart, $windowEnd, $start, $end)) {
                $boundaries[] = $start;
                $boundaries[] = $end;
            }
        }

        foreach ($subscriptions as $subscription) {
            $start = $this->resolveSubscriptionWindowStart($subscription, $cabin, $settings);
            $end = $this->resolveSubscriptionWindowEnd($subscription, $cabin, $settings);

            if ($this->timeRangesOverlap($windowStart, $windowEnd, $start, $end)) {
                $boundaries[] = $start;
                $boundaries[] = $end;
            }
        }

        $boundaries = collect($boundaries)
            ->map(fn ($time) => substr((string) $time, 0, 5))
            ->filter(function ($time) use ($windowOpen, $windowClose) {
                $point = Carbon::createFromFormat('H:i', $time);
                return $point->greaterThanOrEqualTo($windowOpen) && $point->lessThanOrEqualTo($windowClose);
            })
            ->unique()
            ->sort()
            ->values();

        $rawSegments = [];

        for ($index = 0; $index < ($boundaries->count() - 1); $index++) {
            $segmentStart = $boundaries[$index];
            $segmentEnd = $boundaries[$index + 1];

            if ($segmentStart === $segmentEnd) {
                continue;
            }

            $blockingBooking = $bookings->first(function (CabinBooking $booking) use ($segmentStart, $segmentEnd) {
                return $this->timeRangesOverlap(
                    $segmentStart,
                    $segmentEnd,
                    substr((string) $booking->start_time, 0, 5),
                    substr((string) $booking->end_time, 0, 5)
                );
            });

            $blockingSubscription = $subscriptions->first(function (CabinSubscription $subscription) use ($segmentStart, $segmentEnd, $cabin, $settings) {
                return $this->timeRangesOverlap(
                    $segmentStart,
                    $segmentEnd,
                    $this->resolveSubscriptionWindowStart($subscription, $cabin, $settings),
                    $this->resolveSubscriptionWindowEnd($subscription, $cabin, $settings)
                );
            });

            $rawSegments[] = [
                'start' => $segmentStart,
                'end' => $segmentEnd,
                'status' => $blockingBooking || $blockingSubscription ? 'blocked' : 'available',
                'note' => $blockingBooking
                    ? 'Booked' . ($blockingBooking->doctor?->name ? ' · ' . $blockingBooking->doctor->name : '')
                    : ($blockingSubscription
                        ? 'Subscription' . ($blockingSubscription->doctor?->name ? ' · ' . $blockingSubscription->doctor->name : '')
                        : 'Available'),
            ];
        }

        $segments = [];

        foreach ($rawSegments as $segment) {
            $previousIndex = count($segments) - 1;

            if ($previousIndex >= 0
                && $segments[$previousIndex]['status'] === $segment['status']
                && $segments[$previousIndex]['note'] === $segment['note']
                && $segments[$previousIndex]['end'] === $segment['start']) {
                $segments[$previousIndex]['end'] = $segment['end'];
                continue;
            }

            $segments[] = $segment;
        }

        $segments = collect($segments)->map(function (array $segment) {
            return [
                'start' => $segment['start'],
                'end' => $segment['end'],
                'label' => Carbon::createFromFormat('H:i', $segment['start'])->format('h:i A')
                    . ' - ' .
                    Carbon::createFromFormat('H:i', $segment['end'])->format('h:i A'),
                'status' => $segment['status'],
                'note' => $segment['note'],
            ];
        })->values();

        return response()->json([
            'window_start' => $windowStart,
            'window_end' => $windowEnd,
            'segments' => $segments,
            'bookings' => $bookings->map(fn (CabinBooking $booking) => [
                'label' => substr((string) $booking->start_time, 0, 5) . ' - ' . substr((string) $booking->end_time, 0, 5),
                'note' => 'Booked' . ($booking->doctor?->name ? ' · ' . $booking->doctor->name : ''),
            ])->values(),
            'subscriptions' => $subscriptions->map(fn (CabinSubscription $subscription) => [
                'label' => $this->resolveSubscriptionWindowStart($subscription, $cabin, $settings) . ' - ' . $this->resolveSubscriptionWindowEnd($subscription, $cabin, $settings),
                'note' => 'Subscription' . ($subscription->doctor?->name ? ' · ' . $subscription->doctor->name : ''),
            ])->values(),
        ]);
    }

    public function subscriptionAvailability(Request $request)
    {
        $validated = $request->validate([
            'cabin_id' => 'required|exists:cabins,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'subscription_start_time' => 'required|date_format:H:i',
            'subscription_end_time' => 'required|date_format:H:i|after:subscription_start_time',
            'subscription_id' => 'nullable|integer',
        ]);

        $cabin = Cabin::findOrFail($validated['cabin_id']);
        $settings = $this->getSettings();
        [$validated['subscription_start_time'], $validated['subscription_end_time']] = $this->applyDoctorSubscriptionWindow(
            $validated['doctor_id'] ?? null,
            $validated['subscription_start_time'],
            $validated['subscription_end_time'],
            $cabin,
            $settings
        );
        [$windowStart, $windowEnd] = $this->resolveSubscriptionTimeRange(
            $validated['subscription_start_time'],
            $validated['subscription_end_time'],
            $cabin,
            $settings
        );

        $result = [
            'valid' => true,
            'message' => 'This cabin is available for the selected subscription period and daily time window.',
            'normalized_start_time' => $windowStart,
            'normalized_end_time' => $windowEnd,
            'conflict_type' => null,
        ];

        if (in_array($cabin->status, ['occupied', 'maintenance', 'inactive'], true)) {
            $result['valid'] = false;
            $result['conflict_type'] = 'status';
            $result['message'] = 'This cabin is currently not available for monthly subscription because it is marked as ' . $cabin->status . '.';

            return response()->json($result);
        }

        if ($cabin->booking_mode === 'hourly') {
            $result['valid'] = false;
            $result['conflict_type'] = 'mode';
            $result['message'] = 'This cabin is configured for hourly bookings only, so monthly subscription is not allowed.';

            return response()->json($result);
        }

        if ($cabin->available_from && Carbon::parse($validated['start_date'])->lt($cabin->available_from->copy()->startOfDay())) {
            $result['valid'] = false;
            $result['conflict_type'] = 'available_from';
            $result['message'] = 'This cabin becomes available only from ' . $cabin->available_from->format('d M Y') . '.';

            return response()->json($result);
        }

        $conflictingSubscription = CabinSubscription::where('cabin_id', $validated['cabin_id'])
            ->where('status', 'active')
            ->when($validated['subscription_id'] ?? null, fn ($query, $subscriptionId) => $query->where('id', '!=', $subscriptionId))
            ->where(function ($query) use ($validated) {
                $query->whereDate('start_date', '<=', $validated['end_date'])
                    ->whereDate('end_date', '>=', $validated['start_date']);
            })
            ->with('doctor')
            ->get()
            ->first(fn (CabinSubscription $subscription) => $this->timeRangesOverlap(
                $windowStart,
                $windowEnd,
                $this->resolveSubscriptionWindowStart($subscription, $cabin, $settings),
                $this->resolveSubscriptionWindowEnd($subscription, $cabin, $settings)
            ));

        if ($conflictingSubscription) {
            $doctorName = $conflictingSubscription->doctor->name ?? 'another doctor';
            $result['valid'] = false;
            $result['conflict_type'] = 'subscription';
            $result['message'] = 'This cabin is already assigned to ' . $doctorName . ' under another active subscription for the selected period and daily time window.';

            return response()->json($result);
        }

        if (! empty($validated['doctor_id'])) {
            $doctorSubscriptionConflict = CabinSubscription::with('cabin')
                ->where('doctor_id', $validated['doctor_id'])
                ->where('status', 'active')
                ->when($validated['subscription_id'] ?? null, fn ($query, $subscriptionId) => $query->where('id', '!=', $subscriptionId))
                ->where(function ($query) use ($validated) {
                    $query->whereDate('start_date', '<=', $validated['end_date'])
                        ->whereDate('end_date', '>=', $validated['start_date']);
                })
                ->get()
                ->first(fn (CabinSubscription $subscription) => $this->timeRangesOverlap(
                    $windowStart,
                    $windowEnd,
                    $this->resolveSubscriptionWindowStart($subscription, $subscription->cabin, $settings),
                    $this->resolveSubscriptionWindowEnd($subscription, $subscription->cabin, $settings)
                ));

            if ($doctorSubscriptionConflict) {
                $existingCabin = $doctorSubscriptionConflict->cabin;
                $result['valid'] = false;
                $result['conflict_type'] = 'doctor_subscription';
                $result['conflict_subscription_id'] = $doctorSubscriptionConflict->id;
                $result['conflict_edit_url'] = route('admin.cabins.subscriptions.edit', $doctorSubscriptionConflict->id);
                $result['conflict_cabin_label'] = trim(($existingCabin->cabin_code ?? 'Cabin') . ' - ' . ($existingCabin->name ?? ''));
                $result['conflict_period'] = trim(optional($doctorSubscriptionConflict->start_date)->format('d M Y') . ' - ' . optional($doctorSubscriptionConflict->end_date)->format('d M Y'));
                $result['conflict_time_window'] = $this->resolveSubscriptionWindowStart($doctorSubscriptionConflict, $existingCabin, $settings) . ' - ' . $this->resolveSubscriptionWindowEnd($doctorSubscriptionConflict, $existingCabin, $settings);
                $result['message'] = 'This doctor already has an active cabin subscription for the selected period and time window. Please edit the existing subscription timing if you want to change it.';

                return response()->json($result);
            }
        }

        $conflictingBooking = CabinBooking::where('cabin_id', $validated['cabin_id'])
            ->whereIn('status', ['booked', 'completed'])
            ->whereDate('booking_date', '>=', $validated['start_date'])
            ->whereDate('booking_date', '<=', $validated['end_date'])
            ->with('doctor')
            ->get()
            ->first(fn (CabinBooking $booking) => $this->timeRangesOverlap(
                substr($booking->start_time, 0, 5),
                substr($booking->end_time, 0, 5),
                $windowStart,
                $windowEnd
            ));

        if ($conflictingBooking) {
            $doctorName = $conflictingBooking->doctor->name ?? 'another doctor';
            $bookingDate = optional($conflictingBooking->booking_date)->format('d M Y');
            $result['valid'] = false;
            $result['conflict_type'] = 'booking';
            $result['message'] = 'This cabin already has an hourly booking for ' . $doctorName . ' on ' . $bookingDate . ' within the selected subscription time window.';

            return response()->json($result);
        }

        return response()->json($result);
    }

    public function subscriptionDoctorWindow(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'cabin_id' => 'nullable|exists:cabins,id',
        ]);

        $settings = $this->getSettings();
        $cabin = ! empty($validated['cabin_id']) ? Cabin::find($validated['cabin_id']) : null;
        $window = $this->getDoctorSubscriptionWindow((int) $validated['doctor_id'], $cabin, $settings);

        return response()->json([
            'start_time' => $window['start_time'],
            'end_time' => $window['end_time'],
            'uses_doctor_schedule' => $window['uses_doctor_schedule'],
            'message' => $window['message'],
        ]);
    }

    public function doctorSubscriptions(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'subscription_id' => 'nullable|integer',
        ]);

        $settings = $this->getSettings();
        $subscriptions = CabinSubscription::with('cabin')
            ->where('doctor_id', $validated['doctor_id'])
            ->where('status', 'active')
            ->when($validated['subscription_id'] ?? null, fn ($query, $subscriptionId) => $query->where('id', '!=', $subscriptionId))
            ->orderByDesc('start_date')
            ->get()
            ->map(function (CabinSubscription $subscription) use ($settings) {
                $cabin = $subscription->cabin;

                return [
                    'id' => $subscription->id,
                    'cabin_label' => trim(($cabin->cabin_code ?? 'Cabin') . ' - ' . ($cabin->name ?? '')),
                    'period' => trim(optional($subscription->start_date)->format('d M Y') . ' - ' . optional($subscription->end_date)->format('d M Y')),
                    'time_window' => $this->resolveSubscriptionWindowStart($subscription, $cabin, $settings) . ' - ' . $this->resolveSubscriptionWindowEnd($subscription, $cabin, $settings),
                    'status' => ucfirst((string) $subscription->status),
                    'edit_url' => route('admin.cabins.subscriptions.edit', $subscription->id),
                    'show_url' => route('admin.cabins.subscriptions.show', $subscription->id),
                ];
            })
            ->values();

        return response()->json([
            'subscriptions' => $subscriptions,
            'count' => $subscriptions->count(),
        ]);
    }

    public function editBooking(CabinBooking $booking)
    {
        $pageTitle = 'Edit Cabin Booking';

        return view('cabins.bookings.form', [
            'pageTitle' => $pageTitle,
            'booking' => $booking,
            'cabins' => Cabin::orderBy('cabin_code')->get(),
            'doctors' => Doctor::orderBy('name')->get(),
            'settings' => $this->getSettings(),
        ]);
    }

    public function showBooking(CabinBooking $booking)
    {
        $booking->load(['cabin.facilities', 'doctor.department']);
        $pageTitle = 'Cabin Booking View';

        return view('cabins.bookings.show', compact('pageTitle', 'booking'));
    }

    public function facilities()
    {
        $facilities = CabinFacility::withCount('cabins')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $pageTitle = 'Cabin Facilities';
        $addlink = route('admin.cabins.facilities.create');

        return view('cabins.facilities.index', compact('pageTitle', 'facilities', 'addlink'));
    }

    public function createFacility()
    {
        $pageTitle = 'Add Cabin Facility';

        return view('cabins.facilities.form', [
            'pageTitle' => $pageTitle,
            'facility' => new CabinFacility([
                'pricing_type' => 'free',
                'rate' => 0,
                'status' => 'active',
                'sort_order' => 0,
            ]),
        ]);
    }

    public function storeFacility(Request $request)
    {
        CabinFacility::create($this->validateFacility($request));

        return redirect()
            ->route('admin.cabins.facilities.index')
            ->with('success', 'Cabin facility created successfully.');
    }

    public function editFacility(CabinFacility $facility)
    {
        $pageTitle = 'Edit Cabin Facility';

        return view('cabins.facilities.form', compact('pageTitle', 'facility'));
    }

    public function updateFacility(Request $request, CabinFacility $facility)
    {
        $facility->update($this->validateFacility($request, $facility->id));

        return redirect()
            ->route('admin.cabins.facilities.index')
            ->with('success', 'Cabin facility updated successfully.');
    }

    public function destroyFacility(CabinFacility $facility)
    {
        if ($facility->cabins()->exists()) {
            return back()->with('error', 'This facility is already linked with cabins, so it cannot be deleted.');
        }

        $facility->delete();

        return redirect()
            ->route('admin.cabins.facilities.index')
            ->with('success', 'Cabin facility deleted successfully.');
    }

    public function updateBooking(Request $request, CabinBooking $booking)
    {
        $data = $this->validateBooking($request, $booking->id);
        $booking->update($data);

        return redirect()
            ->route('admin.cabins.bookings.index')
            ->with('success', 'Cabin booking updated successfully.');
    }

    public function destroyBooking(CabinBooking $booking)
    {
        $booking->delete();

        return redirect()
            ->route('admin.cabins.bookings.index')
            ->with('success', 'Cabin booking deleted successfully.');
    }

    public function subscriptions()
    {
        $today = Carbon::today();
        $subscriptions = CabinSubscription::with(['cabin', 'doctor'])
            ->orderByDesc('start_date')
            ->get();
        $upcomingRenewals = $this->getUpcomingSubscriptionRenewals($today);

        $pageTitle = 'Cabin Subscriptions';
        $addlink = route('admin.cabins.subscriptions.create');

        return view('cabins.subscriptions.index', compact('subscriptions', 'pageTitle', 'addlink', 'upcomingRenewals'));
    }

    public function createSubscription(Request $request)
    {
        $pageTitle = 'Add Cabin Subscription';
        $settings = $this->getSettings();
        $renewFromId = $request->integer('renew_from');

        $subscription = new CabinSubscription([
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
            'subscription_start_time' => substr($settings->clinic_open_time, 0, 5),
            'subscription_end_time' => substr($settings->clinic_close_time, 0, 5),
            'status' => 'active',
            'invoice_day' => 1,
        ]);

        if ($renewFromId) {
            $sourceSubscription = CabinSubscription::with(['cabin', 'doctor'])->findOrFail($renewFromId);
            $nextStartDate = $sourceSubscription->end_date
                ? $sourceSubscription->end_date->copy()->addDay()->startOfDay()
                : now()->startOfMonth();
            $nextEndDate = $nextStartDate->copy()->endOfMonth();

            $subscription = new CabinSubscription([
                'cabin_id' => $sourceSubscription->cabin_id,
                'doctor_id' => $sourceSubscription->doctor_id,
                'start_date' => $nextStartDate->toDateString(),
                'end_date' => $nextEndDate->toDateString(),
                'subscription_start_time' => !empty($sourceSubscription->subscription_start_time)
                    ? substr((string) $sourceSubscription->subscription_start_time, 0, 5)
                    : substr($settings->clinic_open_time, 0, 5),
                'subscription_end_time' => !empty($sourceSubscription->subscription_end_time)
                    ? substr((string) $sourceSubscription->subscription_end_time, 0, 5)
                    : substr($settings->clinic_close_time, 0, 5),
                'monthly_rate' => $sourceSubscription->monthly_rate,
                'gst_percent' => $sourceSubscription->gst_percent,
                'invoice_day' => $sourceSubscription->invoice_day ?: 1,
                'status' => 'active',
                'notes' => $sourceSubscription->notes,
            ]);

            $pageTitle = 'Renew Cabin Subscription';
        }

        return view('cabins.subscriptions.form', [
            'pageTitle' => $pageTitle,
            'subscription' => $subscription,
            'cabins' => Cabin::orderBy('cabin_code')->get(),
            'doctors' => Doctor::orderBy('name')->get(),
            'settings' => $settings,
        ]);
    }

    public function showSubscription(CabinSubscription $subscription)
    {
        $subscription->load(['cabin.facilities', 'doctor.department']);

        return view('cabins.subscriptions.show', compact('subscription'));
    }

    public function storeSubscription(Request $request)
    {
        $data = $this->validateSubscription($request);
        CabinSubscription::create($data);

        return redirect()
            ->route('admin.cabins.subscriptions.index')
            ->with('success', 'Cabin subscription created successfully.');
    }

    public function editSubscription(CabinSubscription $subscription)
    {
        $pageTitle = 'Edit Cabin Subscription';

        return view('cabins.subscriptions.form', [
            'pageTitle' => $pageTitle,
            'subscription' => $subscription,
            'cabins' => Cabin::orderBy('cabin_code')->get(),
            'doctors' => Doctor::orderBy('name')->get(),
            'settings' => $this->getSettings(),
        ]);
    }

    public function updateSubscription(Request $request, CabinSubscription $subscription)
    {
        $data = $this->validateSubscription($request, $subscription->id);
        $subscription->update($data);

        return redirect()
            ->route('admin.cabins.subscriptions.index')
            ->with('success', 'Cabin subscription updated successfully.');
    }

    public function destroySubscription(CabinSubscription $subscription)
    {
        $subscription->delete();

        return redirect()
            ->route('admin.cabins.subscriptions.index')
            ->with('success', 'Cabin subscription deleted successfully.');
    }

    public function reports(Request $request)
    {
        $today = Carbon::today();
        $defaultStart = $today->copy()->startOfMonth();
        $defaultEnd = $today->copy()->endOfMonth();
        $settings = $this->getSettings();

        $activeTab = in_array($request->query('tab'), ['occupancy', 'revenue'], true)
            ? $request->query('tab')
            : 'occupancy';

        [$occFrom, $occTo] = $this->normaliseDateRange(
            $this->resolveFilterDate($request->query('occ_from'), $defaultStart),
            $this->resolveFilterDate($request->query('occ_to'), $defaultEnd)
        );
        [$revFrom, $revTo] = $this->normaliseDateRange(
            $this->resolveFilterDate($request->query('rev_from'), $defaultStart),
            $this->resolveFilterDate($request->query('rev_to'), $defaultEnd)
        );

        $occCabinId = $request->filled('occ_cabin_id') ? (int) $request->query('occ_cabin_id') : null;
        $occCabinType = in_array($request->query('occ_cabin_type'), ['standard', 'premium', 'procedure', 'other'], true)
            ? $request->query('occ_cabin_type')
            : null;
        $revDoctorId = $request->filled('rev_doctor_id') ? (int) $request->query('rev_doctor_id') : null;
        $revStatus = in_array($request->query('rev_status'), ['draft', 'sent', 'paid', 'overdue'], true)
            ? $request->query('rev_status')
            : null;

        $occupancyData = $this->buildOccupancyReportData($occFrom, $occTo, $settings, $occCabinId, $occCabinType);
        $revenueData = $this->buildRevenueReportData($revFrom, $revTo, $revDoctorId, $revStatus);

        $pageTitle = 'Cabin Reports';

        return view('cabins.reports', array_merge(
            [
                'pageTitle' => $pageTitle,
                'activeTab' => $activeTab,
                'filters' => [
                    'occ_from' => $occFrom->toDateString(),
                    'occ_to' => $occTo->toDateString(),
                    'occ_cabin_id' => $occCabinId,
                    'occ_cabin_type' => $occCabinType,
                    'rev_from' => $revFrom->toDateString(),
                    'rev_to' => $revTo->toDateString(),
                    'rev_doctor_id' => $revDoctorId,
                    'rev_status' => $revStatus,
                ],
                'filterCabins' => Cabin::orderBy('cabin_code')->get(['id', 'cabin_code', 'name', 'cabin_type']),
                'filterDoctors' => Doctor::orderBy('name')->get(['id', 'name']),
            ],
            $occupancyData,
            $revenueData
        ));
    }

    private function buildOccupancyReportData(
        Carbon $rangeStart,
        Carbon $rangeEnd,
        CabinSetting $settings,
        ?int $cabinId = null,
        ?string $cabinType = null
    ): array {
        $cabins = Cabin::query()
            ->when($cabinId, fn ($query) => $query->where('id', $cabinId))
            ->when($cabinType, fn ($query, $type) => $query->where('cabin_type', $type))
            ->orderBy('cabin_code')
            ->get();

        $cabinIds = $cabins->pluck('id');

        $bookings = CabinBooking::with(['cabin', 'doctor'])
            ->when($cabinIds->isNotEmpty(), fn ($query) => $query->whereIn('cabin_id', $cabinIds), fn ($query) => $query->whereRaw('1 = 0'))
            ->whereBetween('booking_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
            ->whereIn('status', ['booked', 'completed'])
            ->get();

        $monthlySubscriptions = CabinSubscription::with(['cabin', 'doctor'])
            ->when($cabinIds->isNotEmpty(), fn ($query) => $query->whereIn('cabin_id', $cabinIds), fn ($query) => $query->whereRaw('1 = 0'))
            ->whereIn('status', ['active', 'expired'])
            ->whereDate('start_date', '<=', $rangeEnd->toDateString())
            ->whereDate('end_date', '>=', $rangeStart->toDateString())
            ->orderByDesc('start_date')
            ->get();

        $daysInRange = $rangeStart->diffInDays($rangeEnd) + 1;
        $bookingRevenue = round((float) $bookings->sum('total_amount'), 2);
        $subscriptionRevenue = round((float) $monthlySubscriptions->sum('total_amount'), 2);

        $cabinSummaries = $cabins->map(function (Cabin $cabin) use ($bookings, $monthlySubscriptions, $settings, $rangeStart, $rangeEnd, $daysInRange) {
            $operatingStart = substr((string) ($cabin->operating_start_time ?: $settings->clinic_open_time), 0, 5);
            $operatingEnd = substr((string) ($cabin->operating_end_time ?: $settings->clinic_close_time), 0, 5);
            $dailyMinutes = max(0, $this->timeStringToMinutes($operatingEnd) - $this->timeStringToMinutes($operatingStart));
            $availableHours = round(($dailyMinutes / 60) * $daysInRange, 2);

            $cabinBookings = $bookings->where('cabin_id', $cabin->id);
            $bookedHours = round((float) $cabinBookings->sum('total_hours'), 2);
            $bookingRevenue = round((float) $cabinBookings->sum('total_amount'), 2);

            $subscriptionHours = 0.0;
            $subscriptionRevenue = 0.0;

            foreach ($monthlySubscriptions->where('cabin_id', $cabin->id) as $subscription) {
                $subscriptionStart = Carbon::parse($subscription->start_date);
                $subscriptionEnd = Carbon::parse($subscription->end_date);
                $effectiveStart = $subscriptionStart->greaterThan($rangeStart) ? $subscriptionStart : $rangeStart->copy();
                $effectiveEnd = $subscriptionEnd->lessThan($rangeEnd) ? $subscriptionEnd : $rangeEnd->copy();

                if ($effectiveEnd->lt($effectiveStart)) {
                    continue;
                }

                $days = $effectiveStart->diffInDays($effectiveEnd) + 1;
                $windowStart = $this->resolveSubscriptionWindowStart($subscription, $cabin, $settings);
                $windowEnd = $this->resolveSubscriptionWindowEnd($subscription, $cabin, $settings);
                $dailySubscriptionHours = max(0, $this->timeStringToMinutes($windowEnd) - $this->timeStringToMinutes($windowStart)) / 60;

                $subscriptionHours += $days * $dailySubscriptionHours;
                $subscriptionRevenue += (float) $subscription->total_amount;
            }

            $occupiedHours = round($bookedHours + $subscriptionHours, 2);
            $utilisation = $availableHours > 0 ? round(min(($occupiedHours / $availableHours) * 100, 100), 1) : 0.0;

            return (object) [
                'id' => $cabin->id,
                'cabin_code' => $cabin->cabin_code,
                'name' => $cabin->name,
                'cabin_type' => $cabin->cabin_type,
                'booked_hours' => $bookedHours,
                'subscription_hours' => round($subscriptionHours, 2),
                'occupied_hours' => $occupiedHours,
                'available_hours' => $availableHours,
                'utilisation_percent' => $utilisation,
                'booking_revenue' => $bookingRevenue,
                'subscription_revenue' => round($subscriptionRevenue, 2),
                'combined_revenue' => round($bookingRevenue + $subscriptionRevenue, 2),
                'usage_type' => $subscriptionHours > $bookedHours ? 'monthly' : ($bookedHours > 0 ? 'hourly' : 'idle'),
            ];
        })->sortByDesc('utilisation_percent')->values();

        $totalAvailableHours = round((float) $cabinSummaries->sum('available_hours'), 2);
        $totalOccupiedHours = round((float) $cabinSummaries->sum('occupied_hours'), 2);
        $avgOccupancy = $totalAvailableHours > 0 ? round(($totalOccupiedHours / $totalAvailableHours) * 100, 1) : 0.0;
        $idleHours = round(max($totalAvailableHours - $totalOccupiedHours, 0), 2);
        $bestUtilisedCabin = $cabinSummaries->first();

        $hourBuckets = [];
        $globalStart = $this->timeStringToMinutes(substr((string) $settings->clinic_open_time, 0, 5));
        $globalEnd = $this->timeStringToMinutes(substr((string) $settings->clinic_close_time, 0, 5));
        for ($minutes = $globalStart; $minutes < $globalEnd; $minutes += 60) {
            $label = Carbon::createFromFormat('H:i', $this->minutesToTimeString($minutes))->format('g:i A')
                . ' - ' .
                Carbon::createFromFormat('H:i', $this->minutesToTimeString(min($minutes + 60, $globalEnd)))->format('g:i A');
            $hourBuckets[$label] = 0;
        }

        foreach ($bookings as $booking) {
            $bookingStart = $this->timeStringToMinutes(substr((string) $booking->start_time, 0, 5));
            $bookingEnd = $this->timeStringToMinutes(substr((string) $booking->end_time, 0, 5));

            foreach (array_keys($hourBuckets) as $index => $label) {
                $slotStart = $globalStart + ($index * 60);
                $slotEnd = min($slotStart + 60, $globalEnd);
                if ($bookingStart < $slotEnd && $bookingEnd > $slotStart) {
                    $hourBuckets[$label]++;
                }
            }
        }

        $peakBookingHours = collect($hourBuckets)
            ->map(fn ($count, $label) => ['label' => $label, 'count' => $count])
            ->sortByDesc('count')
            ->take(6)
            ->values();
        $peakMax = max((int) ($peakBookingHours->max('count') ?? 0), 1);

        $bookingTypeSplit = [
            [
                'label' => 'Monthly subscriptions',
                'value' => round((float) $cabinSummaries->sum('subscription_hours'), 2),
                'color' => '#059669',
            ],
            [
                'label' => 'Hourly bookings',
                'value' => round((float) $cabinSummaries->sum('booked_hours'), 2),
                'color' => '#216AAE',
            ],
            [
                'label' => 'Idle / Maintenance',
                'value' => $idleHours,
                'color' => '#D5DDE8',
            ],
        ];

        return [
            'occRangeLabel' => $this->formatReportRangeLabel($rangeStart, $rangeEnd),
            'cabinSummaries' => $cabinSummaries,
            'monthlySubscriptions' => $monthlySubscriptions,
            'monthRevenue' => $bookingRevenue,
            'subscriptionRevenue' => $subscriptionRevenue,
            'avgOccupancy' => $avgOccupancy,
            'totalOccupiedHours' => $totalOccupiedHours,
            'totalAvailableHours' => $totalAvailableHours,
            'idleHours' => $idleHours,
            'bestUtilisedCabin' => $bestUtilisedCabin,
            'peakBookingHours' => $peakBookingHours,
            'peakMax' => $peakMax,
            'bookingTypeSplit' => $bookingTypeSplit,
            'splitTotal' => max(collect($bookingTypeSplit)->sum('value'), 1),
        ];
    }

    private function buildRevenueReportData(
        Carbon $rangeStart,
        Carbon $rangeEnd,
        ?int $doctorId = null,
        ?string $status = null
    ): array {
        $invoiceQuery = CabinInvoice::with(['doctor', 'cabin'])
            ->whereBetween('invoice_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
            ->when($doctorId, fn ($query) => $query->where('doctor_id', $doctorId))
            ->when($status, fn ($query, $value) => $query->where('status', $value));

        $invoices = $invoiceQuery->get();

        $latestSubscriptions = CabinSubscription::with(['cabin', 'doctor'])
            ->whereIn('status', ['active', 'expired'])
            ->whereDate('start_date', '<=', $rangeEnd->toDateString())
            ->whereDate('end_date', '>=', $rangeStart->toDateString())
            ->when($doctorId, fn ($query) => $query->where('doctor_id', $doctorId))
            ->orderByDesc('start_date')
            ->get();

        $totalInvoiced = round((float) $invoices->sum('total_amount'), 2);
        $collectedAmount = round((float) $invoices->where('status', 'paid')->sum('total_amount'), 2);
        $outstandingAmount = round((float) $invoices->whereIn('status', ['draft', 'sent', 'overdue'])->sum('total_amount'), 2);
        $overdueAmount = round((float) $invoices->where('status', 'overdue')->sum('total_amount'), 2);

        $doctorRevenue = $invoices
            ->groupBy('doctor_id')
            ->map(function ($doctorInvoices) {
                $doctor = $doctorInvoices->first()?->doctor;

                return (object) [
                    'doctor_name' => $doctor->name ?? 'Unknown Doctor',
                    'invoiced' => round((float) $doctorInvoices->sum('total_amount'), 2),
                    'paid' => round((float) $doctorInvoices->where('status', 'paid')->sum('total_amount'), 2),
                    'due' => round((float) $doctorInvoices->whereIn('status', ['draft', 'sent', 'overdue'])->sum('total_amount'), 2),
                ];
            })
            ->sortByDesc('invoiced')
            ->values();

        $topDoctorRevenue = max((float) ($doctorRevenue->max('invoiced') ?? 0), 1);

        $monthlyTrend = collect(range(5, 0))
            ->map(function ($monthsAgo) use ($rangeEnd, $doctorId, $status) {
                $pointDate = $rangeEnd->copy()->startOfMonth()->subMonths($monthsAgo);
                $query = CabinInvoice::query()
                    ->whereBetween('invoice_date', [$pointDate->copy()->startOfMonth()->toDateString(), $pointDate->copy()->endOfMonth()->toDateString()])
                    ->when($doctorId, fn ($nestedQuery) => $nestedQuery->where('doctor_id', $doctorId))
                    ->when($status, fn ($nestedQuery, $value) => $nestedQuery->where('status', $value));

                return [
                    'label' => $pointDate->format('M'),
                    'total' => round((float) $query->sum('total_amount'), 2),
                ];
            })
            ->values();

        $revenueTypeBreakdown = [
            [
                'label' => 'Monthly subs',
                'value' => round((float) $invoices->where('billing_type', 'monthly')->sum('total_amount'), 2),
                'color' => '#059669',
            ],
            [
                'label' => 'Hourly bookings',
                'value' => round((float) $invoices->where('billing_type', 'hourly')->sum('total_amount'), 2),
                'color' => '#216AAE',
            ],
        ];

        return [
            'revRangeLabel' => $this->formatReportRangeLabel($rangeStart, $rangeEnd),
            'revenueSubscriptions' => $latestSubscriptions,
            'totalInvoiced' => $totalInvoiced,
            'collectedAmount' => $collectedAmount,
            'outstandingAmount' => $outstandingAmount,
            'overdueAmount' => $overdueAmount,
            'doctorRevenue' => $doctorRevenue,
            'topDoctorRevenue' => $topDoctorRevenue,
            'monthlyTrend' => $monthlyTrend,
            'trendMax' => max((float) ($monthlyTrend->max('total') ?? 0), 1),
            'revenueTypeBreakdown' => $revenueTypeBreakdown,
            'revenueTypeTotal' => max(collect($revenueTypeBreakdown)->sum('value'), 1),
        ];
    }

    private function resolveFilterDate(?string $value, Carbon $fallback): Carbon
    {
        if (empty($value)) {
            return $fallback->copy();
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $exception) {
            return $fallback->copy();
        }
    }

    private function normaliseDateRange(Carbon $start, Carbon $end): array
    {
        if ($start->gt($end)) {
            return [$end->copy(), $start->copy()];
        }

        return [$start->copy(), $end->copy()];
    }

    private function formatReportRangeLabel(Carbon $start, Carbon $end): string
    {
        if ($start->isSameDay($end)) {
            return $start->format('d M Y');
        }

        return $start->format('d M Y') . ' - ' . $end->format('d M Y');
    }

    public function doctors()
    {
        return redirect()->route('admin.doctors');
    }

    public function doctorProfile(Doctor $doctor)
    {
        $subscriptions = CabinSubscription::with('cabin')
            ->where('doctor_id', $doctor->id)
            ->orderByDesc('start_date')
            ->get();

        $bookings = CabinBooking::with('cabin')
            ->where('doctor_id', $doctor->id)
            ->orderByDesc('booking_date')
            ->orderByDesc('start_time')
            ->get();

        $invoices = CabinInvoice::with(['cabin', 'items'])
            ->where('doctor_id', $doctor->id)
            ->orderByDesc('invoice_date')
            ->get();

        $pageTitle = 'Doctor Cabin Profile';

        return view('cabins.doctors.profile', compact('pageTitle', 'doctor', 'subscriptions', 'bookings', 'invoices'));
    }

    public function invoices()
    {
        $invoices = CabinInvoice::with(['doctor', 'cabin'])
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->get();

        $pageTitle = 'Cabin Invoices';
        $addlink = route('admin.cabins.invoices.create');

        return view('cabins.invoices.index', compact('pageTitle', 'invoices', 'addlink'));
    }

    public function createInvoice()
    {
        $pageTitle = 'New Cabin Invoice';
        $settings = $this->getSettings();

        return view('cabins.invoices.form', compact('pageTitle', 'settings'))
            ->with([
                'doctors' => Doctor::orderBy('name')->get(),
                'cabins' => Cabin::orderBy('cabin_code')->get(),
            ]);
    }

    public function storeInvoice(Request $request)
    {
        $settings = $this->getSettings();

        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'cabin_id' => 'nullable|exists:cabins,id',
            'billing_type' => ['required', Rule::in(['hourly', 'monthly'])],
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'gst_percent' => 'nullable|numeric|min:0|max:100',
            'status' => ['required', Rule::in(['draft', 'sent', 'paid', 'overdue'])],
            'sent_via' => ['nullable', Rule::in(['email', 'whatsapp', 'both'])],
            'notes' => 'nullable|string',
        ]);

        [$items, $subtotal] = $this->buildInvoiceItems(
            (int) $validated['doctor_id'],
            $validated['billing_type'],
            $validated['period_start'],
            $validated['period_end'],
            $validated['cabin_id'] ?? null
        );

        if (empty($items)) {
            throw ValidationException::withMessages([
                'period_start' => 'No cabin bookings or subscriptions were found for this doctor and period.',
            ]);
        }

        $gstPercent = (float) ($validated['gst_percent'] ?? $settings->default_gst_percent);
        $gstAmount = round(($subtotal * $gstPercent) / 100, 2);

        DB::transaction(function () use ($validated, $items, $subtotal, $gstPercent, $gstAmount) {
            $invoice = CabinInvoice::create([
                'invoice_number' => $this->generateCabinInvoiceNumber(),
                'doctor_id' => $validated['doctor_id'],
                'cabin_id' => $validated['cabin_id'] ?? null,
                'billing_type' => $validated['billing_type'],
                'period_start' => $validated['period_start'],
                'period_end' => $validated['period_end'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'subtotal' => $subtotal,
                'gst_percent' => $gstPercent,
                'gst_amount' => $gstAmount,
                'total_amount' => round($subtotal + $gstAmount, 2),
                'status' => $validated['status'],
                'sent_via' => $validated['sent_via'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                $invoice->items()->create($item);
            }
        });

        return redirect()
            ->route('admin.cabins.invoices.index')
            ->with('success', 'Cabin invoice generated successfully.');
    }

    public function showInvoice(CabinInvoice $invoice)
    {
        $invoice->load(['doctor.department', 'cabin', 'items']);
        $pageTitle = 'Cabin Invoice';

        return view('cabins.invoices.show', compact('pageTitle', 'invoice'));
    }

    public function printInvoice(CabinInvoice $invoice)
    {
        $invoice->load(['doctor.department', 'cabin', 'items']);

        return view('cabins.invoices.print', compact('invoice'));
    }

    public function invoicePdf(CabinInvoice $invoice)
    {
        $invoice->load(['doctor.department', 'cabin', 'items']);

        $pdf = Pdf::loadView('cabins.invoices.pdf', compact('invoice'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('cabin-invoice-' . $invoice->invoice_number . '.pdf');
    }

    public function settings()
    {
        $pageTitle = 'Cabin Settings';
        $settings = $this->getSettings();

        return view('cabins.settings', compact('pageTitle', 'settings'));
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'clinic_open_time' => 'required|date_format:H:i',
            'clinic_close_time' => 'required|date_format:H:i|after:clinic_open_time',
            'min_booking_duration_minutes' => 'required|integer|min:15|max:480',
            'buffer_minutes' => 'required|integer|min:0|max:180',
            'default_gst_percent' => 'required|numeric|min:0|max:100',
            'monthly_invoice_day' => 'required|integer|min:1|max:31',
            'payment_due_days' => 'required|integer|min:0|max:90',
            'invoice_delivery_mode' => ['required', Rule::in(['email', 'whatsapp', 'both'])],
            'clinic_gstin' => 'nullable|string|max:30',
            'standard_hourly_rate' => 'required|numeric|min:0',
            'premium_hourly_rate' => 'required|numeric|min:0',
            'procedure_hourly_rate' => 'required|numeric|min:0',
            'standard_monthly_rate' => 'required|numeric|min:0',
            'premium_monthly_rate' => 'required|numeric|min:0',
            'procedure_monthly_rate' => 'required|numeric|min:0',
        ]);

        CabinSetting::updateOrCreate(['id' => 1], $data);

        return redirect()
            ->route('admin.cabins.settings')
            ->with('success', 'Cabin settings updated successfully.');
    }

    private function validateCabin(Request $request, ?int $cabinId = null): array
    {
        $validated = $request->validate([
            'cabin_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('cabins', 'cabin_code')->ignore($cabinId),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cabins', 'name')->ignore($cabinId),
            ],
            'cabin_type' => ['required', Rule::in(['consultation', 'premium', 'procedure', 'other'])],
            'floor_name' => 'nullable|string|max:100',
            'room_number' => 'nullable|string|max:50',
            'capacity' => 'required|integer|min:1|max:20',
            'booking_mode' => ['required', Rule::in(['hourly', 'monthly', 'both'])],
            'hourly_rate' => 'nullable|numeric|min:0',
            'monthly_rate' => 'nullable|numeric|min:0',
            'status' => ['required', Rule::in(['available', 'occupied', 'maintenance', 'inactive'])],
            'available_from' => 'nullable|date',
            'operating_start_time' => 'nullable|date_format:H:i',
            'operating_end_time' => 'nullable|date_format:H:i|after:operating_start_time',
            'facility_ids' => 'nullable|array',
            'facility_ids.*' => 'integer|exists:cabin_facilities,id',
            'notes' => 'nullable|string',
        ]);

        $settings = $this->getSettings();
        $validated['hourly_rate'] = $this->normalizeCabinRate(
            $validated['booking_mode'],
            'hourly_rate',
            $validated['hourly_rate'] ?? null,
            $validated['cabin_type'],
            $settings
        );
        $validated['monthly_rate'] = $this->normalizeCabinRate(
            $validated['booking_mode'],
            'monthly_rate',
            $validated['monthly_rate'] ?? null,
            $validated['cabin_type'],
            $settings
        );

        return $validated;
    }

    private function validateBooking(Request $request, ?int $bookingId = null): array
    {
        $settings = $this->getSettings();

        $validated = $request->validate([
            'cabin_id' => 'required|exists:cabins,id',
            'doctor_id' => 'required|exists:doctors,id',
            'booking_type' => ['required', Rule::in(['hourly', 'half_day', 'full_day'])],
            'booking_date' => 'required|date',
            'half_day_slot' => ['nullable', Rule::in(['first_half', 'second_half'])],
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'gst_percent' => 'nullable|numeric|min:0|max:100',
            'payment_choice' => ['required', Rule::in(['pay_now', 'pay_later', 'free_booking', 'no_payment_required'])],
            'payment_mode' => ['nullable', Rule::in(['cash', 'upi', 'card'])],
            'transaction_reference' => 'nullable|string|max:100',
            'status' => ['required', Rule::in(['booked', 'completed', 'cancelled'])],
            'notes' => 'nullable|string',
        ]);

        $cabin = Cabin::findOrFail($validated['cabin_id']);

        if (in_array($cabin->status, ['occupied', 'maintenance', 'inactive'], true)) {
            throw ValidationException::withMessages([
                'cabin_id' => 'This cabin is currently not available for hourly booking because it is marked as ' . $cabin->status . '.',
            ]);
        }

        if ($cabin->booking_mode === 'monthly') {
            throw ValidationException::withMessages([
                'cabin_id' => 'This cabin is configured for monthly allocation only, so hourly booking is not allowed.',
            ]);
        }

        if ($cabin->available_from && Carbon::parse($validated['booking_date'])->lt($cabin->available_from->copy()->startOfDay())) {
            throw ValidationException::withMessages([
                'booking_date' => 'This cabin becomes available only from ' . $cabin->available_from->format('d M Y') . '.',
            ]);
        }

        [$validated['start_time'], $validated['end_time']] = $this->resolveBookingTimeRange($validated, $settings);
        $duration = $this->calculateDurationHours($validated['start_time'], $validated['end_time']);

        if (($duration * 60) < (int) $settings->min_booking_duration_minutes) {
            throw ValidationException::withMessages([
                'end_time' => 'Booking duration is shorter than the configured minimum duration.',
            ]);
        }

        $hasOverlap = CabinBooking::where('cabin_id', $validated['cabin_id'])
            ->whereDate('booking_date', $validated['booking_date'])
            ->whereIn('status', ['booked', 'completed'])
            ->when($bookingId, fn ($query) => $query->where('id', '!=', $bookingId))
            ->where(function ($query) use ($validated) {
                $query->where('start_time', '<', $validated['end_time'])
                    ->where('end_time', '>', $validated['start_time']);
            })
            ->exists();

        if ($hasOverlap) {
            throw ValidationException::withMessages([
                'start_time' => 'This cabin already has a booking in the selected time range.',
            ]);
        }

        $blockingSubscription = CabinSubscription::where('cabin_id', $validated['cabin_id'])
            ->where('status', 'active')
            ->whereDate('start_date', '<=', $validated['booking_date'])
            ->whereDate('end_date', '>=', $validated['booking_date'])
            ->with('doctor')
            ->get()
            ->first(fn (CabinSubscription $subscription) => $this->timeRangesOverlap(
                $validated['start_time'],
                $validated['end_time'],
                $this->resolveSubscriptionWindowStart($subscription, $cabin, $settings),
                $this->resolveSubscriptionWindowEnd($subscription, $cabin, $settings)
            ));

        if ($blockingSubscription) {
            $doctorName = $blockingSubscription->doctor->name ?? 'another doctor';
            throw ValidationException::withMessages([
                'booking_date' => 'This cabin is already assigned to ' . $doctorName . ' under an active monthly subscription for the selected date and time window.',
            ]);
        }

        $gstPercent = (float) ($validated['gst_percent'] ?? $settings->default_gst_percent);
        $baseAmount = round($duration * (float) $this->resolveHourlyRate($cabin, $settings), 2);
        $gstAmount = round(($baseAmount * $gstPercent) / 100, 2);
        $totalAmount = round($baseAmount + $gstAmount, 2);

        $isFreeBooking = $validated['payment_choice'] === 'free_booking';
        $isNoPaymentRequired = $validated['payment_choice'] === 'no_payment_required';
        $isPayLater = $validated['payment_choice'] === 'pay_later';

        if ($validated['payment_choice'] === 'pay_now' && empty($validated['payment_mode'])) {
            throw ValidationException::withMessages([
                'payment_mode' => 'Please choose a payment mode for pay now.',
            ]);
        }

        if (in_array($validated['payment_mode'] ?? null, ['upi', 'card'], true) && empty($validated['transaction_reference']) && $validated['payment_choice'] === 'pay_now') {
            throw ValidationException::withMessages([
                'transaction_reference' => 'Reference number is required for UPI or card payment.',
            ]);
        }

        if ($isFreeBooking || $isNoPaymentRequired) {
            $baseAmount = 0.00;
            $gstAmount = 0.00;
            $totalAmount = 0.00;
        }

        $validated['total_hours'] = round($duration, 2);
        $validated['base_amount'] = $baseAmount;
        $validated['gst_percent'] = $gstPercent;
        $validated['gst_amount'] = $gstAmount;
        $validated['total_amount'] = $totalAmount;
        $validated['payment_mode'] = $isNoPaymentRequired
            ? 'no_payment_required'
            : ($isFreeBooking ? 'free_booking' : ($isPayLater ? 'pay_later' : $validated['payment_mode']));
        $validated['payment_status'] = $isNoPaymentRequired
            ? 'No Payment Required'
            : ($isFreeBooking ? 'Authorized' : ($isPayLater ? 'Pending' : 'Authorized'));
        $validated['paid_amount'] = $validated['payment_choice'] === 'pay_now' ? $totalAmount : 0.00;
        $validated['paid_on'] = $validated['payment_choice'] === 'pay_now' ? now() : null;

        return $validated;
    }

    private function validateFacility(Request $request, ?int $facilityId = null): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => [
                'nullable',
                'string',
                'max:120',
                Rule::unique('cabin_facilities', 'slug')->ignore($facilityId),
            ],
            'description' => 'nullable|string',
            'pricing_type' => ['required', Rule::in(['free', 'paid'])],
            'rate' => 'nullable|numeric|min:0|max:999999.99',
            'charge_label' => 'nullable|string|max:100',
            'payment_note' => 'nullable|string',
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'sort_order' => 'nullable|integer|min:0|max:999',
        ]);

        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['rate'] = $validated['pricing_type'] === 'paid'
            ? round((float) ($validated['rate'] ?? 0), 2)
            : 0.00;
        $validated['charge_label'] = $validated['pricing_type'] === 'paid'
            ? ($validated['charge_label'] ?: 'Per Use')
            : null;
        $validated['payment_note'] = $validated['pricing_type'] === 'paid'
            ? ($validated['payment_note'] ?? null)
            : null;

        return $validated;
    }

    private function validateSubscription(Request $request, ?int $subscriptionId = null): array
    {
        $validated = $request->validate([
            'cabin_id' => 'required|exists:cabins,id',
            'doctor_id' => 'required|exists:doctors,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'subscription_start_time' => 'required|date_format:H:i',
            'subscription_end_time' => 'required|date_format:H:i|after:subscription_start_time',
            'monthly_rate' => 'nullable|numeric|min:0',
            'gst_percent' => 'nullable|numeric|min:0|max:100',
            'invoice_day' => 'required|integer|min:1|max:31',
            'status' => ['required', Rule::in(['active', 'expired', 'cancelled'])],
            'notes' => 'nullable|string',
        ]);

        $cabin = Cabin::findOrFail($validated['cabin_id']);
        $settings = $this->getSettings();

        if (in_array($cabin->status, ['occupied', 'maintenance', 'inactive'], true)) {
            throw ValidationException::withMessages([
                'cabin_id' => 'This cabin is currently not available for monthly subscription because it is marked as ' . $cabin->status . '.',
            ]);
        }

        if ($cabin->booking_mode === 'hourly') {
            throw ValidationException::withMessages([
                'cabin_id' => 'This cabin is configured for hourly bookings only, so monthly subscription is not allowed.',
            ]);
        }

        if ($cabin->available_from && Carbon::parse($validated['start_date'])->lt($cabin->available_from->copy()->startOfDay())) {
            throw ValidationException::withMessages([
                'start_date' => 'This cabin becomes available only from ' . $cabin->available_from->format('d M Y') . '.',
            ]);
        }

        [$validated['subscription_start_time'], $validated['subscription_end_time']] = $this->applyDoctorSubscriptionWindow(
            (int) $validated['doctor_id'],
            $validated['subscription_start_time'],
            $validated['subscription_end_time'],
            $cabin,
            $settings
        );

        [$validated['subscription_start_time'], $validated['subscription_end_time']] = $this->resolveSubscriptionTimeRange(
            $validated['subscription_start_time'],
            $validated['subscription_end_time'],
            $cabin,
            $settings
        );

        $conflictingSubscription = CabinSubscription::where('cabin_id', $validated['cabin_id'])
            ->where('status', 'active')
            ->when($subscriptionId, fn ($query) => $query->where('id', '!=', $subscriptionId))
            ->where(function ($query) use ($validated) {
                $query->whereDate('start_date', '<=', $validated['end_date'])
                    ->whereDate('end_date', '>=', $validated['start_date']);
            })
            ->with('doctor')
            ->get()
            ->first(fn (CabinSubscription $subscription) => $this->timeRangesOverlap(
                $validated['subscription_start_time'],
                $validated['subscription_end_time'],
                $this->resolveSubscriptionWindowStart($subscription, $cabin, $settings),
                $this->resolveSubscriptionWindowEnd($subscription, $cabin, $settings)
            ));

        if ($conflictingSubscription) {
            throw ValidationException::withMessages([
                'start_date' => 'This cabin already has a subscription in the selected period and time window.',
            ]);
        }

        $doctorSubscriptionConflict = CabinSubscription::with('cabin')
            ->where('doctor_id', $validated['doctor_id'])
            ->where('status', 'active')
            ->when($subscriptionId, fn ($query) => $query->where('id', '!=', $subscriptionId))
            ->where(function ($query) use ($validated) {
                $query->whereDate('start_date', '<=', $validated['end_date'])
                    ->whereDate('end_date', '>=', $validated['start_date']);
            })
            ->get()
            ->first(fn (CabinSubscription $subscription) => $this->timeRangesOverlap(
                $validated['subscription_start_time'],
                $validated['subscription_end_time'],
                $this->resolveSubscriptionWindowStart($subscription, $subscription->cabin, $settings),
                $this->resolveSubscriptionWindowEnd($subscription, $subscription->cabin, $settings)
            ));

        if ($doctorSubscriptionConflict) {
            $cabinLabel = trim(($doctorSubscriptionConflict->cabin->cabin_code ?? 'Cabin') . ' - ' . ($doctorSubscriptionConflict->cabin->name ?? ''));

            throw ValidationException::withMessages([
                'doctor_id' => 'This doctor already has an active subscription in ' . $cabinLabel . ' for the selected period and time window. Edit that subscription timing instead of creating a new one.',
            ]);
        }

        $conflictingBooking = CabinBooking::where('cabin_id', $validated['cabin_id'])
            ->whereIn('status', ['booked', 'completed'])
            ->whereDate('booking_date', '>=', $validated['start_date'])
            ->whereDate('booking_date', '<=', $validated['end_date'])
            ->get()
            ->first(fn (CabinBooking $booking) => $this->timeRangesOverlap(
                substr($booking->start_time, 0, 5),
                substr($booking->end_time, 0, 5),
                $validated['subscription_start_time'],
                $validated['subscription_end_time']
            ));

        if ($conflictingBooking) {
            throw ValidationException::withMessages([
                'start_date' => 'This cabin already has hourly bookings inside the selected subscription time window.',
            ]);
        }

        $monthlyRate = $validated['monthly_rate'] !== null && $validated['monthly_rate'] !== ''
            ? round((float) $validated['monthly_rate'], 2)
            : (float) $this->resolveMonthlyRate($cabin, $settings);
        $gstPercent = (float) ($validated['gst_percent'] ?? $settings->default_gst_percent);
        $gstAmount = round(($monthlyRate * $gstPercent) / 100, 2);

        $validated['monthly_rate'] = round($monthlyRate, 2);
        $validated['gst_percent'] = $gstPercent;
        $validated['gst_amount'] = $gstAmount;
        $validated['total_amount'] = round($monthlyRate + $gstAmount, 2);

        return $validated;
    }

    private function getSettings(): CabinSetting
    {
        return CabinSetting::first() ?? new CabinSetting([
            'clinic_open_time' => '09:00',
            'clinic_close_time' => '21:00',
            'min_booking_duration_minutes' => 60,
            'buffer_minutes' => 15,
            'default_gst_percent' => 18,
            'monthly_invoice_day' => 1,
            'payment_due_days' => 15,
            'invoice_delivery_mode' => 'both',
            'standard_hourly_rate' => 800,
            'premium_hourly_rate' => 1200,
            'procedure_hourly_rate' => 1500,
            'standard_monthly_rate' => 22000,
            'premium_monthly_rate' => 32000,
            'procedure_monthly_rate' => 38000,
        ]);
    }

    private function buildInvoiceItems(int $doctorId, string $billingType, string $periodStart, string $periodEnd, ?int $cabinId = null): array
    {
        $items = [];
        $subtotal = 0.0;

        if ($billingType === 'hourly') {
            $bookings = CabinBooking::with('cabin')
                ->where('doctor_id', $doctorId)
                ->whereIn('status', ['booked', 'completed'])
                ->whereDate('booking_date', '>=', $periodStart)
                ->whereDate('booking_date', '<=', $periodEnd)
                ->when($cabinId, fn ($query) => $query->where('cabin_id', $cabinId))
                ->orderBy('booking_date')
                ->get();

            foreach ($bookings as $booking) {
                $lineTotal = round((float) $booking->base_amount, 2);
                $subtotal += $lineTotal;
                $items[] = [
                    'description' => sprintf(
                        '%s - %s (%s to %s, %s hrs)',
                        $booking->cabin->cabin_code ?? 'Cabin',
                        optional($booking->booking_date)->format('d M Y'),
                        substr($booking->start_time, 0, 5),
                        substr($booking->end_time, 0, 5),
                        number_format((float) $booking->total_hours, 2)
                    ),
                    'reference_type' => 'booking',
                    'reference_id' => $booking->id,
                    'quantity' => (float) $booking->total_hours,
                    'unit_rate' => $booking->total_hours > 0 ? round(((float) $booking->base_amount / (float) $booking->total_hours), 2) : (float) $booking->base_amount,
                    'line_total' => $lineTotal,
                ];
            }
        }

        if ($billingType === 'monthly') {
            $subscriptions = CabinSubscription::with('cabin')
                ->where('doctor_id', $doctorId)
                ->whereIn('status', ['active', 'expired'])
                ->whereDate('start_date', '<=', $periodEnd)
                ->whereDate('end_date', '>=', $periodStart)
                ->when($cabinId, fn ($query) => $query->where('cabin_id', $cabinId))
                ->orderBy('start_date')
                ->get();

            foreach ($subscriptions as $subscription) {
                $lineTotal = round((float) $subscription->monthly_rate, 2);
                $subtotal += $lineTotal;
                $items[] = [
                    'description' => sprintf(
                        '%s - Monthly subscription (%s to %s, %s to %s)',
                        $subscription->cabin->cabin_code ?? 'Cabin',
                        optional($subscription->start_date)->format('d M Y'),
                        optional($subscription->end_date)->format('d M Y'),
                        $this->resolveSubscriptionWindowStart($subscription, $subscription->cabin, null),
                        $this->resolveSubscriptionWindowEnd($subscription, $subscription->cabin, null)
                    ),
                    'reference_type' => 'subscription',
                    'reference_id' => $subscription->id,
                    'quantity' => 1,
                    'unit_rate' => $lineTotal,
                    'line_total' => $lineTotal,
                ];
            }
        }

        return [$items, round($subtotal, 2)];
    }

    private function generateCabinInvoiceNumber(): string
    {
        $prefix = 'CAB-INV-' . now()->format('Ym');
        $lastInvoice = CabinInvoice::where('invoice_number', 'like', $prefix . '-%')
            ->orderByDesc('id')
            ->first();

        $next = 1;
        if ($lastInvoice && preg_match('/-(\d+)$/', $lastInvoice->invoice_number, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return sprintf('%s-%03d', $prefix, $next);
    }

    private function resolveCabinStatus(Cabin $cabin, Carbon $now): array
    {
        if ($cabin->status === 'maintenance') {
            return [
                'label' => 'Maintenance',
                'class' => 'maintenance',
                'bucket' => 'maintenance',
                'meta' => 'Not available for booking',
            ];
        }

        if ($cabin->status === 'occupied') {
            return [
                'label' => 'Occupied',
                'class' => 'booked',
                'bucket' => 'booked',
                'meta' => 'Marked occupied in cabin master',
            ];
        }

        if ($cabin->status === 'inactive') {
            return [
                'label' => 'Inactive',
                'class' => 'maintenance',
                'bucket' => 'maintenance',
                'meta' => 'Hidden from active allocation',
            ];
        }

        $settings = $this->getSettings();
        $activeSubscription = $cabin->subscriptions->first(function (CabinSubscription $subscription) use ($now, $cabin, $settings) {
            return $this->timeRangesOverlap(
                $now->format('H:i'),
                $now->copy()->addMinute()->format('H:i'),
                $this->resolveSubscriptionWindowStart($subscription, $cabin, $settings),
                $this->resolveSubscriptionWindowEnd($subscription, $cabin, $settings)
            );
        });
        if ($activeSubscription) {
            return [
                'label' => 'Monthly',
                'class' => 'monthly',
                'bucket' => 'monthly',
                'meta' => trim((optional($activeSubscription->doctor)->name ?: 'Assigned doctor') . ' · ' . $this->resolveSubscriptionWindowStart($activeSubscription, $cabin, $settings) . ' to ' . $this->resolveSubscriptionWindowEnd($activeSubscription, $cabin, $settings)),
            ];
        }

        $activeBooking = $cabin->bookings->first(function (CabinBooking $booking) use ($now) {
            $start = Carbon::parse($booking->booking_date->format('Y-m-d') . ' ' . $booking->start_time);
            $end = Carbon::parse($booking->booking_date->format('Y-m-d') . ' ' . $booking->end_time);

            return $now->between($start, $end);
        });

        if ($activeBooking) {
            return [
                'label' => 'Booked',
                'class' => 'booked',
                'bucket' => 'booked',
                'meta' => optional($activeBooking->doctor)->name ?: 'Doctor assigned',
            ];
        }

        return [
            'label' => 'Available',
            'class' => 'available',
            'bucket' => 'available',
            'meta' => 'Open for booking',
        ];
    }

    private function calculateDurationHours(string $startTime, string $endTime): float
    {
        $start = Carbon::createFromFormat('H:i', $startTime);
        $end = Carbon::createFromFormat('H:i', $endTime);

        return $start->diffInMinutes($end) / 60;
    }

    private function resolveBookingTimeRange(array $validated, CabinSetting $settings): array
    {
        $clinicStart = Carbon::createFromFormat('H:i', substr($settings->clinic_open_time, 0, 5));
        $clinicEnd = Carbon::createFromFormat('H:i', substr($settings->clinic_close_time, 0, 5));

        if ($validated['booking_type'] === 'full_day') {
            return [$clinicStart->format('H:i'), $clinicEnd->format('H:i')];
        }

        if ($validated['booking_type'] === 'half_day') {
            if (empty($validated['half_day_slot'])) {
                throw ValidationException::withMessages([
                    'half_day_slot' => 'Please choose first half or second half.',
                ]);
            }

            $midPoint = $clinicStart->copy()->addMinutes((int) floor($clinicStart->diffInMinutes($clinicEnd) / 2));

            return $validated['half_day_slot'] === 'second_half'
                ? [$midPoint->format('H:i'), $clinicEnd->format('H:i')]
                : [$clinicStart->format('H:i'), $midPoint->format('H:i')];
        }

        if (empty($validated['start_time']) || empty($validated['end_time'])) {
            throw ValidationException::withMessages([
                'start_time' => 'Start time and end time are required for hourly bookings.',
            ]);
        }

        $start = Carbon::createFromFormat('H:i', $validated['start_time']);
        $end = Carbon::createFromFormat('H:i', $validated['end_time']);

        if ($end->lessThanOrEqualTo($start)) {
            throw ValidationException::withMessages([
                'end_time' => 'End time must be later than start time.',
            ]);
        }

        if ($start->lt($clinicStart) || $end->gt($clinicEnd)) {
            throw ValidationException::withMessages([
                'start_time' => 'Hourly booking time must stay within the clinic working hours from Cabin Settings.',
            ]);
        }

        return [$start->format('H:i'), $end->format('H:i')];
    }

    private function resolveHourlyRate(Cabin $cabin, CabinSetting $settings): float
    {
        if (! is_null($cabin->hourly_rate)) {
            return (float) $cabin->hourly_rate;
        }

        return match ($cabin->cabin_type) {
            'premium' => (float) $settings->premium_hourly_rate,
            'procedure' => (float) $settings->procedure_hourly_rate,
            default => (float) $settings->standard_hourly_rate,
        };
    }

    private function resolveMonthlyRate(Cabin $cabin, CabinSetting $settings): float
    {
        if (! is_null($cabin->monthly_rate)) {
            return (float) $cabin->monthly_rate;
        }

        return match ($cabin->cabin_type) {
            'premium' => (float) $settings->premium_monthly_rate,
            'procedure' => (float) $settings->procedure_monthly_rate,
            default => (float) $settings->standard_monthly_rate,
        };
    }

    private function normalizeCabinRate(
        string $bookingMode,
        string $field,
        mixed $submittedValue,
        string $cabinType,
        CabinSetting $settings
    ): ?float {
        $supportsHourly = in_array($bookingMode, ['hourly', 'both'], true);
        $supportsMonthly = in_array($bookingMode, ['monthly', 'both'], true);

        if ($field === 'hourly_rate' && ! $supportsHourly) {
            return null;
        }

        if ($field === 'monthly_rate' && ! $supportsMonthly) {
            return null;
        }

        if ($submittedValue !== null && $submittedValue !== '') {
            return round((float) $submittedValue, 2);
        }

        if ($field === 'hourly_rate') {
            return round(match ($cabinType) {
                'premium' => (float) $settings->premium_hourly_rate,
                'procedure' => (float) $settings->procedure_hourly_rate,
                default => (float) $settings->standard_hourly_rate,
            }, 2);
        }

        return round(match ($cabinType) {
            'premium' => (float) $settings->premium_monthly_rate,
            'procedure' => (float) $settings->procedure_monthly_rate,
            default => (float) $settings->standard_monthly_rate,
        }, 2);
    }

    private function resolveSubscriptionTimeRange(
        string $startTime,
        string $endTime,
        Cabin $cabin,
        CabinSetting $settings
    ): array {
        $windowStart = Carbon::createFromFormat('H:i', $startTime);
        $windowEnd = Carbon::createFromFormat('H:i', $endTime);

        $operatingStart = Carbon::createFromFormat(
            'H:i',
            substr($cabin->operating_start_time ?: $settings->clinic_open_time, 0, 5)
        );
        $operatingEnd = Carbon::createFromFormat(
            'H:i',
            substr($cabin->operating_end_time ?: $settings->clinic_close_time, 0, 5)
        );

        if ($windowStart->lt($operatingStart) || $windowEnd->gt($operatingEnd)) {
            throw ValidationException::withMessages([
                'subscription_start_time' => 'Subscription time must stay within the cabin operating hours.',
            ]);
        }

        return [$windowStart->format('H:i'), $windowEnd->format('H:i')];
    }

    private function applyDoctorSubscriptionWindow(
        ?int $doctorId,
        string $fallbackStartTime,
        string $fallbackEndTime,
        Cabin $cabin,
        CabinSetting $settings
    ): array {
        if (! $doctorId) {
            return [$fallbackStartTime, $fallbackEndTime];
        }

        $window = $this->getDoctorSubscriptionWindow($doctorId, $cabin, $settings);

        if ($window['uses_doctor_schedule']) {
            return [$window['start_time'], $window['end_time']];
        }

        return [$fallbackStartTime, $fallbackEndTime];
    }

    private function getDoctorSubscriptionWindow(
        int $doctorId,
        ?Cabin $cabin = null,
        ?CabinSetting $settings = null
    ): array {
        $settings ??= $this->getSettings();
        $fallbackStart = substr((string) (($cabin?->operating_start_time) ?: $settings->clinic_open_time), 0, 5);
        $fallbackEnd = substr((string) (($cabin?->operating_end_time) ?: $settings->clinic_close_time), 0, 5);

        $sessions = DoctorSession::where('doctor_id', $doctorId)
            ->orderBy('start_time')
            ->get();

        if ($sessions->isNotEmpty()) {
            $start = $sessions->min(fn (DoctorSession $session) => substr((string) $session->start_time, 0, 5));
            $end = $sessions->max(fn (DoctorSession $session) => substr((string) $session->end_time, 0, 5));

            return [
                'start_time' => $start ?: $fallbackStart,
                'end_time' => $end ?: $fallbackEnd,
                'uses_doctor_schedule' => true,
                'message' => 'Subscription timing is using this doctor\'s appointment session hours.',
            ];
        }

        $slotRows = DoctorTimeSlot::where('doctor_id', $doctorId)
            ->where('is_weekly_off', false)
            ->orderBy('slot_time')
            ->get();

        if ($slotRows->isNotEmpty()) {
            $slotSetting = DoctorSlotSetting::where('doctor_id', $doctorId)->first();
            $slotDuration = max((int) ($slotSetting?->slot_duration ?? 15), 1);
            $start = substr((string) $slotRows->first()->slot_time, 0, 5);
            $lastSlotStart = Carbon::createFromFormat('H:i', substr((string) $slotRows->last()->slot_time, 0, 5));
            $end = $lastSlotStart->copy()->addMinutes($slotDuration)->format('H:i');

            return [
                'start_time' => $start ?: $fallbackStart,
                'end_time' => $end ?: $fallbackEnd,
                'uses_doctor_schedule' => true,
                'message' => 'Subscription timing is using this doctor\'s configured appointment slots.',
            ];
        }

        return [
            'start_time' => $fallbackStart,
            'end_time' => $fallbackEnd,
            'uses_doctor_schedule' => false,
            'message' => 'No appointment timing is configured for this doctor yet, so cabin working hours are being used.',
        ];
    }

    private function resolveSubscriptionWindowStart(
        CabinSubscription $subscription,
        ?Cabin $cabin = null,
        ?CabinSetting $settings = null
    ): string {
        if (! empty($subscription->subscription_start_time)) {
            return substr((string) $subscription->subscription_start_time, 0, 5);
        }

        $settings ??= $this->getSettings();
        return substr((string) (($cabin?->operating_start_time) ?: $settings->clinic_open_time), 0, 5);
    }

    private function resolveSubscriptionWindowEnd(
        CabinSubscription $subscription,
        ?Cabin $cabin = null,
        ?CabinSetting $settings = null
    ): string {
        if (! empty($subscription->subscription_end_time)) {
            return substr((string) $subscription->subscription_end_time, 0, 5);
        }

        $settings ??= $this->getSettings();
        return substr((string) (($cabin?->operating_end_time) ?: $settings->clinic_close_time), 0, 5);
    }

    private function timeRangesOverlap(string $startA, string $endA, string $startB, string $endB): bool
    {
        $rangeStartA = Carbon::createFromFormat('H:i', substr($startA, 0, 5));
        $rangeEndA = Carbon::createFromFormat('H:i', substr($endA, 0, 5));
        $rangeStartB = Carbon::createFromFormat('H:i', substr($startB, 0, 5));
        $rangeEndB = Carbon::createFromFormat('H:i', substr($endB, 0, 5));

        return $rangeStartA->lt($rangeEndB) && $rangeEndA->gt($rangeStartB);
    }

    private function timeStringToMinutes(string $value): int
    {
        [$hours, $minutes] = array_pad(explode(':', substr($value, 0, 5)), 2, '0');

        return ((int) $hours * 60) + (int) $minutes;
    }

    private function minutesToTimeString(int $minutes): string
    {
        $hours = (int) floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        return str_pad((string) $hours, 2, '0', STR_PAD_LEFT) . ':' . str_pad((string) $remainingMinutes, 2, '0', STR_PAD_LEFT);
    }

    private function getUpcomingSubscriptionRenewals(Carbon $today)
    {
        $renewalEnd = $today->copy()->addDays(7);

        return CabinSubscription::with(['cabin', 'doctor'])
            ->where('status', 'active')
            ->whereDate('end_date', '>=', $today->toDateString())
            ->whereDate('end_date', '<=', $renewalEnd->toDateString())
            ->orderBy('end_date')
            ->get()
            ->map(function (CabinSubscription $subscription) use ($today) {
                $endDate = optional($subscription->end_date)?->copy();
                $daysLeft = $endDate ? $today->diffInDays($endDate, false) : null;

                return [
                    'model' => $subscription,
                    'days_left' => $daysLeft,
                    'renew_url' => route('admin.cabins.subscriptions.create', ['renew_from' => $subscription->id]),
                    'edit_url' => route('admin.cabins.subscriptions.edit', $subscription->id),
                    'show_url' => route('admin.cabins.subscriptions.show', $subscription->id),
                ];
            })
            ->values();
    }
}
