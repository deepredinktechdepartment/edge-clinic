<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MocDoc\DoctorController;
use App\Http\Controllers\MocDoc\AvailabilityController;
use App\Http\Controllers\MocDoc\BookingController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::prefix('mocdoc')->group(function () {
    // 1. Get all doctors (or filter by department / location)
    Route::get('/doctors', [DoctorController::class, 'list']);          // e.g. POST /api/mocdoc/doctors

    // 2. Get single doctor detail (optional)
    Route::post('/doctors/{doctorId}', [DoctorController::class, 'detail']);

    // 3. Get availability for a doctor (7-day window or date range)
    Route::post('/availability', [AvailabilityController::class, 'getAvailability']);
    //   Request body: { doctor_id, hospital_id, (optional) date or date_range }

    // 4. Book appointment
    Route::post('/book-appointment', [BookingController::class, 'book']);

    // 5. (Optional) Cancel appointment
    Route::post('/cancel-appointment', [BookingController::class, 'cancel']);

    // 6. (Optional) Get booking status / details
    Route::post('/get-booking', [BookingController::class, 'getBooking']);
});
