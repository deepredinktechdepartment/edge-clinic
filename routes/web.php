<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HomePageController;
use App\Http\Controllers\DepartmentController;

use App\Http\Controllers\DoctorController;
use App\Http\Controllers\UsermanagementController;
use App\Http\Controllers\ChangePasswordController;
use Illuminate\Support\Facades\Log;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

//bookanappointmnetroutswithoutadmin

Route::get('/doctors', [DoctorController::class, 'index'])
    ->name('doctors.list');

Route::get('/patient-appointments', [DoctorController::class, 'appointments'])
    ->name('patient.appointments');

    Route::get('/doctor/profile/{id}', [DoctorController::class, 'ajaxProfile'])->name('doctor.profile.ajax');
Route::get('/doctor/appointment/{id}', [DoctorController::class, 'ajaxAppointment'])->name('doctor.appointment.ajax');

    Route::get('/doctor/{slug}', [DoctorController::class, 'show'])
    ->name('doctor.single');

Route::get('/appointment/book/{doctor_id?}',
    [DoctorController::class, 'bookAppointment']
)->name('appointment.book');
Route::get('terms-of-use', function () {
    return view('pages.terms-of-use');
})->name('terms.use');

Route::any('/appointments', [DoctorController::class, 'appointmentsStore'])->name('appointments.store');
Route::get('/appointment/patient-form', [DoctorController::class, 'patientForm'])
     ->name('appointment.patientForm');

use App\Http\Controllers\RazorpayController;
Route::get('/', [RazorpayController::class, 'index']);
Route::any(
    'razorpay/create-order',
    [RazorpayController::class, 'createOrder']
)->name('razorpay.create-order');
Route::any('razorpay/verify', [RazorpayController::class, 'verifyPayment'])->name('razorpay.verify');
Route::get('razorpay/success', [RazorpayController::class, 'success'])->name('razorpay.success');
Route::get('razorpay/failure', [RazorpayController::class, 'failure'])->name('razorpay.failure');
Route::get('testmail', [RazorpayController::class, 'testmail'])->name('test.mail');

// Moc Doc API
use App\Http\Controllers\MocDocController;
Route::get('/mocdoc/doctors/{entityKey}', [MocDocController::class, 'sendHmacRequest']);
Route::get('mocdoc/doctors/calendar/{entitykey?}/{drkey?}/{startdate?}/{enddate?}', [MocDocController::class, 'getDoctorCalendar']);
Route::post('api/doctors/calendar', [MocDocController::class, '_getDoctorCalendar']);

Route::get('/test-mocdoc-booking', function () {

    try {

        $entityKey = "jv-medi-clinic";
        $url = "https://mocdoc.com/api/bookappt/" . $entityKey;

        $dummyData = [
            'first_name' => 'Sathish Kumar',
            'phone' => '9876543210',
            'dr' => 'dr.ananth',
            'date' => '20251220',
            'start' => '10:00',
            'end' => '10:15',
            'entitylocation' => 'location1',
            'session' => '',
            'sessionval' => '',
            'token_no' =>"",
            'title' => 'Mr',
            'age' => '32 years',
            'email' => 'sathish@testmail.com',
            'appnotes' => 'Testing MocDoc API'
        ];

        /*
        |--------------------------------------------------------------------------
        | Generate Headers (same as actual API)
        |--------------------------------------------------------------------------
        */
        $headers = app(\App\Http\Controllers\MocDocController::class)
            ->mocdocHmacHeaders($url, 'POST',"application/json");




        /*
        |--------------------------------------------------------------------------
        | Log everything for backend trace
        |--------------------------------------------------------------------------
        */
        // ❗ Override Content-Type



        Log::info('TEST MocDoc Booking - URL', ['url' => $url]);
        Log::info('TEST MocDoc Booking - Headers', $headers);
        Log::info('TEST MocDoc Booking - Payload', $dummyData);

        /*
        |--------------------------------------------------------------------------
        | Call API
        |--------------------------------------------------------------------------
        */
        $response = app(\App\Http\Controllers\RazorpayController::class)
            ->bookMocdocAppointment($dummyData);

            dd($response);
        Log::info('TEST MocDoc Booking - API Response', [
            'response' => $response
        ]);

        /*
        |--------------------------------------------------------------------------
        | SHOW FULL REQUEST FOR TECH TEAM
        |--------------------------------------------------------------------------
        */
        return response()->json([
            'success' => true,

            'request_review' => [
                'url'     => $url,
                'method'  => 'POST',
                'headers' => $headers,
                'payload' => $dummyData,
            ],

            'api_response' => $response

        ], 200, [], JSON_PRETTY_PRINT);

    } catch (\Throwable $e) {

        Log::error('TEST MocDoc Booking - Exception', [
            'message' => $e->getMessage(),
            'trace'   => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'error'   => $e->getMessage()
        ], 500, [], JSON_PRETTY_PRINT);
    }
});


use App\Http\Controllers\PatientAuthController;

Route::get('/patient/login', [PatientAuthController::class, 'loginForm'])->name('patient.login');
Route::post('/patient/login', [PatientAuthController::class, 'login']);

// Patient Registration
Route::post('patient/register', [PatientAuthController::class, 'register'])->name('patient.register');



Route::post('/patient/logout', [PatientAuthController::class, 'logout'])->name('patient.logout');

Route::middleware('prevent.env.access')->group(function () {
    // Your routes here...
});


Route::any('/',[HomeController::class, 'auth_login'])->name('login');

/* Admin URLS */
Route::group(['prefix'=>'admin','as'=>'admin.'], function(){
Route::any('/',[HomeController::class, 'auth_login'])->name('login');
Route::post('adminlogin-verification',[HomeController::class, 'Loginsubmit'])->name('adminlogin.verification');
Route::get('logout', [HomeController::class,'logout'])->name('logout');
Route::get('dashboard', [HomeController::class,'dashboard_lists'])->name('dashboard')->middleware('auth');


/* Departments */

Route::get('specializations', [DepartmentController::class,'departments_list'])->name('specializations')->middleware('auth');
Route::post('department/store', [DepartmentController::class, 'store_department'])->name('department.store')->middleware('auth');
Route::get('department/edit/{id?}',[DepartmentController::class,'edit_departments'])->name('department.edit')->middleware('auth');
Route::get('department/delete',[DepartmentController::class,'delete_departments'])->name('department.delete')->middleware('auth');

/** FAQ of Department**/
Route::get('faqs', [DepartmentController::class,'faqs'])->name('faqs')->middleware('auth');
Route::get('filter-faqs', [DepartmentController::class,'filter_faqs'])->name('filter.faqs')->middleware('auth');
Route::post('faq/store', [DepartmentController::class, 'store_faq'])->name('faq.store')->middleware('auth');
Route::get('faq/edit/{id?}',[DepartmentController::class,'edit_faq'])->name('faq.edit')->middleware('auth');
Route::get('faq/delete',[DepartmentController::class,'delete_faq'])->name('faq.delete')->middleware('auth');
/*** End  ***/



/** Change password **/
Route::get('changepassword', [ChangePasswordController::class,'changepassword'])->name('changepassword')->middleware('auth');
Route::post('changepassword/store', [ChangePasswordController::class, 'store_changepassword'])->name('changepassword.store')->middleware('auth');
Route::get('changepassword/edit/{id?}',[ChangePasswordController::class,'edit_changepassword'])->name('changepassword.edit')->middleware('auth');

/*** End  ***/


/** Profile**/
Route::get('profile', [UsermanagementController::class,'profile'])->name('profile')->middleware('auth');
Route::post('profile/store', [UsermanagementController::class, 'store_profile'])->name('profile.store')->middleware('auth');
Route::get('profile/edit/{id?}',[UsermanagementController::class,'edit_profile'])->name('profile.edit')->middleware('auth');

/*** End  ***/

/* Doctors */

Route::get('doctors', [DoctorController::class,'doctors_list'])->name('doctors')->middleware('auth');
Route::post('doctor/store', [DoctorController::class, 'store_doctor'])->name('doctor.store')->middleware('auth');
Route::get('doctor/edit/{id?}',[DoctorController::class,'edit_doctors'])->name('doctor.edit')->middleware('auth');
Route::get('doctor/delete',[DoctorController::class,'delete_doctors'])->name('doctor.delete')->middleware('auth');


/* Procedures */
Route::get('procedures', [DepartmentController::class,'procedures_list'])->name('procedures.view')->middleware('auth');
Route::post('procedure/store', [DepartmentController::class, 'procedure_store'])->name('procedure.store')->middleware('auth');
Route::get('procedure/edit/{id?}',[DepartmentController::class,'edit_procedure'])->name('procedure.edit')->middleware('auth');
Route::get('procedure/delete',[DepartmentController::class,'delete_procedure'])->name('procedure.delete')->middleware('auth');


/* Conditions */
Route::get('conditions', [DepartmentController::class,'conditions_list'])->name('conditions.view')->middleware('auth');
Route::post('condition/store', [DepartmentController::class, 'condition_store'])->name('condition.store')->middleware('auth');
Route::get('condition/edit/{id?}',[DepartmentController::class,'edit_condition'])->name('condition.edit')->middleware('auth');
Route::get('condition/delete',[DepartmentController::class,'delete_condition'])->name('condition.delete')->middleware('auth');



/* Testimonials */
Route::get('doctor-videos', [DoctorController::class,'doctor_videos_list'])->name('doctor-videos')->middleware('auth');
Route::post('doctor-video/store', [DoctorController::class, 'doctor_video_store'])->name('doctor-videos.store')->middleware('auth');
Route::get('doctor-video/edit/{id?}',[DoctorController::class,'edit_doctor_video'])->name('doctor-videos.edit')->middleware('auth');
Route::get('doctor-video/delete',[DoctorController::class,'delete_doctor_video'])->name('doctor-videos.delete')->middleware('auth');
Route::get('filter-doctor-videos', [DoctorController::class,'filter_doctor_video'])->name('filter.doctor.videos')->middleware('auth');





/*admin- Users */

Route::get('users', [UsermanagementController::class,'index'])->name('users')->middleware('auth');
Route::get('user/create', [UsermanagementController::class,'create_user'])->middleware('auth');
Route::post('user/store', [UsermanagementController::class, 'store_user'])->name('user.store')->middleware('auth');
Route::get('user/edit/{id?}',[UsermanagementController::class,'edit_user'])->name('user.edit')->middleware('auth');
Route::get('user/delete',[UsermanagementController::class,'delete_user'])->name('user.delete')->middleware('auth');


});

use App\Http\Controllers\DoctorPaymentController;

// Doctor Payment Report
Route::get('admin/payment/report', [DoctorPaymentController::class, 'index'])
        ->name('admin.payment.report');

Route::get('admin/payment/report/filter', [DoctorPaymentController::class, 'filter'])
        ->name('admin.payment.report.filter');
// Route::get('admin/payment/report/doctor/{doctorId}', [DoctorPaymentController::class, 'doctorReport'])
//         ->name('admin.payment.report.doctor');
//         Route::get('/admin/payment/report/export', [DoctorPaymentController::class, 'export'])
//     ->name('admin.payment.report.export');



  Route::post('admin/user/forgot-password', [ChangePasswordController::class, 'forgotPassword'])
    ->name('admin.user.forgot-password');

    Route::get('admin/appointments-report', [DoctorPaymentController::class, 'appointments_list'])
    ->name('admin.appointments.report');

Route::get('admin/appointments-report/print', [DoctorPaymentController::class, 'print'])
    ->name('admin.appointments.report.print');


use App\Http\Controllers\PatientController;
Route::prefix('patients')->name('patients.')->group(function () {
    Route::get('/', [PatientController::class, 'index'])->name('index');
    Route::get('/create', [PatientController::class, 'create'])->name('create');
    Route::post('/store', [PatientController::class, 'store'])->name('store');
    Route::get('/edit', [PatientController::class, 'edit'])->name('edit');
    Route::post('/update/{id}', [PatientController::class, 'update'])->name('update');
    Route::post('/delete', [PatientController::class, 'delete'])->name('delete');
});


