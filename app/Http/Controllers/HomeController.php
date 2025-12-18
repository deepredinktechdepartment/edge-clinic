<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
Use Exception;
use Hash;
use Validator;
use Auth;
use Session;

class HomeController extends Controller
{


    public function auth_login(Request $request)
    {
        $user  = auth()->user();


        if($user){

           return redirect('admin/dashboard')->with('success', 'Successfully logged in.');
        }

        else{

           $pageTitle="Login";
            return view('auth.login', compact('pageTitle'));
        }


    }
   public function Loginsubmit(Request $request)
{
    // 1️⃣ Validate input
    $request->validate([
        'email'    => 'required|email',
        'password' => 'required'
    ]);

    // 2️⃣ Check if email exists
    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return redirect('/admin')
            ->withInput($request->only('email'))
            ->with('error', 'Email address not found.');
    }

    // 3️⃣ Check if account is active
    if ($user->is_active != 1) {
        return redirect('/admin')
            ->withInput($request->only('email'))
            ->with('error', 'Your account is inactive. Please contact admin.');
    }

    // 4️⃣ Check password
    if (!Hash::check($request->password, $user->password)) {
        return redirect('/admin')
            ->withInput($request->only('email'))
            ->with('error', 'Invalid password.');
    }

    // 5️⃣ Login user
    Auth::login($user);

    return redirect('admin/dashboard')
        ->with('success', 'Successfully logged in.');
}
    public function logout()
    {

        Auth::logout();
        Session::flush();
        return redirect('/admin')->with('error', 'You have been successfully logged out!');
    }


public function dashboard_lists()
{
    try {
        $pageTitle = 'Dashboard';
        $addLink = '';

        // ------------------------------------------------
        // 📅 Today date
        // ------------------------------------------------
        $today = now()->toDateString();

        // ------------------------------------------------
        // 📊 Dashboard Metrics
        // ------------------------------------------------
        $departments_count = Department::count();

        $doctors_count = Doctor::count();

        $patients_count = Patient::count();

        $appointments_count = Appointment::whereDate('date', $today)->count();

        $today_collection = Payment::whereDate('created_at', $today)
            ->where('status', 'Authorized')
            ->sum('amount');

        return view(
            'home.dashboard',
            compact(
                'pageTitle',
                'addLink',
                'departments_count',
                'doctors_count',
                'patients_count',
                'appointments_count',
                'today_collection'
            )
        );

    } catch (Exception $exception){
       return redirect()->back()->with('error', 'Something went wrong'.$exception->getMessage());
       }

}

}
