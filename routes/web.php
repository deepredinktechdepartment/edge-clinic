<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HomePageController;
use App\Http\Controllers\DepartmentController;

use App\Http\Controllers\DoctorController;
use App\Http\Controllers\UsermanagementController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\RegistrationFeeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SourceController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ShortUrlController;
use App\Http\Controllers\AppointmentConfigController;
use App\Http\Controllers\CabinManagementController;
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

use App\Http\Controllers\OtpController;

Route::post('/send-otp', [OtpController::class, 'send']);
Route::post('/verify-otp', [OtpController::class, 'verify']);


Route::get('/doctors', [DoctorController::class, 'index'])
    ->name('doctors.list');

Route::get('/patient-appointments/deleted', [DoctorController::class, 'appointments'])
    ->name('patient.appointments');

Route::get('/', [DoctorController::class, 'appointments'])
    ->name('for.patients');

Route::post('callback/send-otp', [EnquiryController::class, 'sendOtp']);
Route::post('callback/verify-otp', [EnquiryController::class, 'verifyOtp']);
Route::post('callback/submit-enquiry', [EnquiryController::class, 'store']);


    Route::get('/doctor/profile/{id}', [DoctorController::class, 'ajaxProfile'])->name('doctor.profile.ajax');
Route::get('/doctor/appointment/{id}', [DoctorController::class, 'ajaxAppointment'])->name('doctor.appointment.ajax');

    Route::get('/doctor/{slug}', [DoctorController::class, 'show'])
    ->name('doctor.single');

Route::get('/appointment/book/{doctor?}',
    [DoctorController::class, 'bookAppointment']
)->name('appointment.book');
Route::get('terms-of-use', function () {
    return view('pages.terms-of-use');
})->name('terms.use');

Route::get('partner-api-docs', function () {
    return view('pages.partner-appointment-api');
})->name('partner.api.docs');

Route::any('/appointments', [DoctorController::class, 'appointmentsStore'])->name('appointments.store');
Route::get('/appointment/patient-form', [DoctorController::class, 'patientForm'])
     ->name('appointment.patientForm');

     Route::get('/check-registration-fee', [DoctorController::class, 'checkRegistrationFee'])
    ->name('check.registration.fee');

    Route::get('/check-followup-fee', [DoctorController::class, 'checkFollowupFee']);

use App\Http\Controllers\RazorpayController;
// Route::get('/', [RazorpayController::class, 'index']);
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
// Route::get('/sync-doctors', [MocDocController::class, 'syncDoctors']);
// Fetch only (no DB update)
Route::post('/mocdoc/fetch-doctors', [MocDocController::class, 'fetchDoctors'])
    ->name('mocdoc.fetchDoctors');

// Sync and store in DB
Route::post('/mocdoc/sync-doctors', [MocDocController::class, 'syncDoctors'])
    ->name('mocdoc.syncDoctors');
Route::get('/mocdoc/doctors/{entityKey}', [MocDocController::class, 'sendHmacRequest']);
Route::get('mocdoc/doctors/calendar/{entitykey?}/{drkey?}/{startdate?}/{enddate?}', [MocDocController::class, 'getDoctorCalendar']);
Route::post('api/doctors/calendar', [MocDocController::class, '_getDoctorCalendar']);



use App\Http\Controllers\PatientAuthController;

Route::get('/patient/login', [PatientAuthController::class, 'loginForm'])->name('patient.login');
Route::post('/patient/login', [PatientAuthController::class, 'login']);

// Patient Registration
Route::post('patient/register', [PatientAuthController::class, 'register'])->name('patient.register');



Route::post('/patient/logout', [PatientAuthController::class, 'logout'])->name('patient.logout');

Route::middleware('prevent.env.access')->group(function () {
    // Your routes here...
});



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


Route::resource('registration-fees', RegistrationFeeController::class)
        ->names([
            'index'  => 'registration-fees.index',
            'create' => 'registration-fees.create',
            'store'  => 'registration-fees.store',
            'edit'   => 'registration-fees.edit',
            'update' => 'registration-fees.update',
        ])->middleware('auth');



Route::post('registration-fees/{registrationFee}/toggle',[RegistrationFeeController::class, 'changeStatus'])->name('registration-fees.status')->middleware('auth');

Route::resource('services', ServiceController::class);
Route::resource('sources', SourceController::class)->except(['show'])->middleware('auth');

Route::prefix('cabins')->name('cabins.')->middleware('auth')->group(function () {
    Route::get('/', [CabinManagementController::class, 'dashboard'])->name('dashboard');
    Route::get('/list', [CabinManagementController::class, 'index'])->name('index');
    Route::get('/create', [CabinManagementController::class, 'create'])->name('create');
    Route::post('/', [CabinManagementController::class, 'store'])->name('store');
    Route::get('/facilities/list', [CabinManagementController::class, 'facilities'])->name('facilities.index');
    Route::get('/facilities/create', [CabinManagementController::class, 'createFacility'])->name('facilities.create');
    Route::post('/facilities', [CabinManagementController::class, 'storeFacility'])->name('facilities.store');
    Route::get('/facilities/{facility}/edit', [CabinManagementController::class, 'editFacility'])->name('facilities.edit');
    Route::put('/facilities/{facility}', [CabinManagementController::class, 'updateFacility'])->name('facilities.update');
    Route::delete('/facilities/{facility}', [CabinManagementController::class, 'destroyFacility'])->name('facilities.destroy');
    Route::get('/{cabin}/edit', [CabinManagementController::class, 'edit'])->name('edit');
    Route::put('/{cabin}', [CabinManagementController::class, 'update'])->name('update');
    Route::delete('/{cabin}', [CabinManagementController::class, 'destroy'])->name('destroy');

    Route::get('/bookings/list', [CabinManagementController::class, 'bookings'])->name('bookings.index');
    Route::get('/bookings/create', [CabinManagementController::class, 'createBooking'])->name('bookings.create');
    Route::get('/bookings/availability', [CabinManagementController::class, 'bookingAvailabilityTimeline'])->name('bookings.availability');
    Route::post('/bookings', [CabinManagementController::class, 'storeBooking'])->name('bookings.store');
    Route::get('/bookings/{booking}', [CabinManagementController::class, 'showBooking'])->name('bookings.show');
    Route::get('/bookings/{booking}/edit', [CabinManagementController::class, 'editBooking'])->name('bookings.edit');
    Route::put('/bookings/{booking}', [CabinManagementController::class, 'updateBooking'])->name('bookings.update');
    Route::delete('/bookings/{booking}', [CabinManagementController::class, 'destroyBooking'])->name('bookings.destroy');

    Route::get('/subscriptions/list', [CabinManagementController::class, 'subscriptions'])->name('subscriptions.index');
    Route::get('/subscriptions/create', [CabinManagementController::class, 'createSubscription'])->name('subscriptions.create');
    Route::get('/subscriptions/availability', [CabinManagementController::class, 'subscriptionAvailability'])->name('subscriptions.availability');
    Route::post('/subscriptions', [CabinManagementController::class, 'storeSubscription'])->name('subscriptions.store');
    Route::get('/subscriptions/{subscription}', [CabinManagementController::class, 'showSubscription'])->name('subscriptions.show');
    Route::get('/subscriptions/{subscription}/edit', [CabinManagementController::class, 'editSubscription'])->name('subscriptions.edit');
    Route::put('/subscriptions/{subscription}', [CabinManagementController::class, 'updateSubscription'])->name('subscriptions.update');
    Route::delete('/subscriptions/{subscription}', [CabinManagementController::class, 'destroySubscription'])->name('subscriptions.destroy');

    Route::get('/reports', [CabinManagementController::class, 'reports'])->name('reports');
    Route::get('/doctors', [CabinManagementController::class, 'doctors'])->name('doctors.index');
    Route::get('/doctors/{doctor}', [CabinManagementController::class, 'doctorProfile'])->name('doctors.show');
    Route::get('/invoices', [CabinManagementController::class, 'invoices'])->name('invoices.index');
    Route::get('/invoices/create', [CabinManagementController::class, 'createInvoice'])->name('invoices.create');
    Route::post('/invoices', [CabinManagementController::class, 'storeInvoice'])->name('invoices.store');
    Route::get('/invoices/{invoice}', [CabinManagementController::class, 'showInvoice'])->name('invoices.show');
    Route::get('/invoices/{invoice}/print', [CabinManagementController::class, 'printInvoice'])->name('invoices.print');
    Route::get('/invoices/{invoice}/pdf', [CabinManagementController::class, 'invoicePdf'])->name('invoices.pdf');
    Route::get('/settings', [CabinManagementController::class, 'settings'])->name('settings');
    Route::post('/settings', [CabinManagementController::class, 'updateSettings'])->name('settings.update');
    Route::get('/{cabin}', [CabinManagementController::class, 'show'])->name('show');
});

Route::resource('invoices', InvoiceController::class);
Route::post('/invoice/pay', [InvoiceController::class, 'pay'])
    ->name('invoice.pay');
Route::post('/invoices/send-sms',[InvoiceController::class,'sendInvoiceSms'])
    ->name('invoices.send.sms');

});

Route::prefix('admin/appointment-config')->name('admin.appointment-config.')->group(function () {
    Route::get('/',                             [AppointmentConfigController::class, 'index'])->name('index');
    Route::get('/load/{doctorId}',              [AppointmentConfigController::class, 'loadConfig'])->name('load');
    Route::post('/save',                        [AppointmentConfigController::class, 'saveConfig'])->name('save');
    Route::delete('/slot/{slotId}',             [AppointmentConfigController::class, 'deleteSlot'])->name('slot.delete');
    Route::patch('/slot/{slotId}/reserved',     [AppointmentConfigController::class, 'toggleReserved'])->name('slot.reserved');
    Route::post('/slot/override',               [AppointmentConfigController::class, 'addOverrideSlot'])->name('slot.override');
    Route::post('/weekly-off',                  [AppointmentConfigController::class, 'toggleWeeklyOff'])->name('weekly-off');
    Route::post('/non-practice-day',            [AppointmentConfigController::class, 'toggleNonPracticeDay'])->name('non-practice-day');
});

Route::get('/bill/{invoice_number}', [InvoiceController::class,'publicInvoice'])
    ->name('invoice.public');
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

Route::post('appointments/update-status', [DoctorPaymentController::class, 'updateStatus'])->name('appointments.updateStatus');
Route::post('appointments/update-payment', [DoctorPaymentController::class, 'updatePayment'])->name('appointments.updatePayment');
Route::get('appointments/{payment}/reschedule-slots', [DoctorPaymentController::class, 'rescheduleSlots'])->name('appointments.rescheduleSlots');
Route::post('appointments/reschedule', [DoctorPaymentController::class, 'rescheduleAppointment'])->name('appointments.reschedule');
Route::get('appointments/{id}/status-log', [DoctorPaymentController::class, 'getStatusLog']);

Route::get('admin/appointments-report/print', [DoctorPaymentController::class, 'print'])
    ->name('admin.appointments.report.print');

Route::post('/appointments/send-sms',[DoctorPaymentController::class,'sendAppointmentSms'])
->name('appointments.send.sms');

    Route::get(
    'admin/appointments/report/pdf',
    [DoctorPaymentController::class, 'appointmentsReportPdf']
)->name('admin.appointments.report.pdf');

Route::get(
    'admin/payment/report/pdf',
    [DoctorPaymentController::class, 'paymentReportPdf']
)->name('admin.payment.report.pdf');


Route::get(
    'admin/appointments/report/print',
    [DoctorPaymentController::class, 'appointmentsReportPrint']
)->name('admin.appointments.report.print');


Route::get(
    'admin/enquiries',
    [EnquiryController::class, 'callback_enquiries']
)->name('admin.enquiries');

Route::get(
    'admin/enquiries/delete/{ID}',
    [EnquiryController::class, 'delete']
)->name('admin.enquiries.delete');



use App\Http\Controllers\PatientController;

Route::prefix('patients')->name('patients.')->group(function () {
    Route::get('/', [PatientController::class, 'index'])->name('index');
    Route::get('/create', [PatientController::class, 'create'])->name('create');
    Route::post('/store', [PatientController::class, 'store'])->name('store');
    Route::get('/edit', [PatientController::class, 'edit'])->name('edit');
    Route::post('/update/{id}', [PatientController::class, 'update'])->name('update');
    Route::post('/delete', [PatientController::class, 'delete'])->name('delete');
     Route::get('/by-phone', [PatientController::class, 'getByPhone']);
});
/* =========================
   APPOINTMENTS
========================= */
use App\Http\Controllers\AppointmentController;
Route::get('invoice/appointment/{paymentId}',[AppointmentController::class, 'printInvoice'])->name('invoice.appointment');
Route::prefix('manualappointment')
    ->name('manualappointment.')
    ->middleware('auth') // 🔐 protect appointments
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | STEP 1 / 2 – Select Doctor & Slot (Patient based)
        |--------------------------------------------------------------------------
        */
          Route::get('patientcreate', [AppointmentController::class, 'patientCreate'])
            ->name('create');

        Route::get('doctorslotchoose/{patientId?}', [AppointmentController::class, 'slotChoose'])
            ->name('slot.choose');




        /*
        |--------------------------------------------------------------------------
        | STEP 3 – Store Appointment
        |--------------------------------------------------------------------------
        */
        Route::post('/store', [AppointmentController::class, 'store'])
            ->name('store');

        /*
        |--------------------------------------------------------------------------
        | STEP 4 – Payment Page
        |--------------------------------------------------------------------------
        */
        Route::get('/payment/{appointment}', [AppointmentController::class, 'payment'])
            ->name('payment');
              // AJAX route to fetch slots for selected doctor
    Route::get('ajax-slots/{doctorId}', [AppointmentController::class, 'ajaxSlots'])
        ->name('manualappointment.ajaxslots');

            Route::post('confirm', [AppointmentController::class, 'confirm'])
        ->name('confirm');

        Route::get('/check-registration-fee/{id}', [AppointmentController::class, 'checkRegistrationFee'])
    ->name('check.registration.fee');

    });

    Route::get('/get-patient/{id}', [InvoiceController::class,'getPatient']);
Route::get('/get-patient-orders/{id}', [InvoiceController::class,'getOrders']);


Route::get('admin/doctor-sync', [DoctorController::class, 'doctorSyncDashboard'])
    ->name('admin.doctor.sync');
    Route::get('/get-latest-appointment/{patient}',
    [InvoiceController::class,'getLatestAppointment']
)->name('get.latest.appointment');


use App\Http\Controllers\MedicineController;
use App\Http\Controllers\Icd10Controller;

Route::get('admin/medicines', [MedicineController::class, 'index'])->name('admin.medicines.index');
Route::get('admin/icd10', [Icd10Controller::class, 'index'])->name('admin.icd10.index');
Route::middleware('auth')->prefix('admin/consultations')->name('consultations.')->group(function () {

    // ── Static routes first ───────────────────────────────────────────────────
    Route::get('/create',                   [\App\Http\Controllers\ConsultationController::class, 'create'])->name('create');
    Route::post('/',                        [\App\Http\Controllers\ConsultationController::class, 'store'])->name('store');
    Route::get('/case-sheet-template/pdf',  [\App\Http\Controllers\ConsultationController::class, 'caseSheetTemplatePdf'])->name('case_sheet_template.pdf');
    Route::get('/lookups/icd10',            [\App\Http\Controllers\ConsultationController::class, 'searchIcd10'])->name('search.icd10');
    Route::get('/lookups/medicines',        [\App\Http\Controllers\ConsultationController::class, 'searchMedicines'])->name('search.medicines');

    // ── Wildcard {consultation} routes AFTER static ones ─────────────────────
    Route::get('/{consultation}/edit',      [\App\Http\Controllers\ConsultationController::class, 'edit'])->name('edit');
    Route::get('/{consultation}/print',     [\App\Http\Controllers\ConsultationController::class, 'print'])->name('print');
    Route::get('/{consultation}/pdf',       [\App\Http\Controllers\ConsultationController::class, 'pdf'])->name('pdf');
    Route::post('/{consultation}/case-sheet-files', [\App\Http\Controllers\ConsultationController::class, 'uploadCaseSheetFiles'])->name('case_sheet_files.upload');
    Route::get('/{consultation}/case-sheet-files/{side}', [\App\Http\Controllers\ConsultationController::class, 'viewCaseSheetFile'])
        ->whereIn('side', ['front', 'back'])
        ->name('case_sheet_files.view');
    Route::post('/{consultation}/email',    [\App\Http\Controllers\ConsultationController::class, 'email'])->name('email');
});

