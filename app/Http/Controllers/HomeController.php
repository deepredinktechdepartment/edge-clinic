<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use App\Models\Doctor;
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

    // Session::forget('OrganizationID');
    // Session::forget('Licence_OrganizationID');

    $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {


            $user  = auth()->user();

            if ($user) {

            // Session::put('OrganizationID', auth()->user()->org_id??0);
            // Session::put('Licence_OrganizationID', auth()->user()->licence_id??0);
            return redirect('admin/dashboard')->with('success', 'Successfully logged in.');

            }

        }
        else{
            return redirect('/admin')->with('error', 'Invalid Credentials.');
        }


    }
    public function logout()
    {

        Auth::logout();
        Session::flush();
        return redirect('/admin')->with('error', 'You have been successfully logged out!');
    }


    public function dashboard_lists()
    {
        try{
        $user  = auth()->user();
        $pageTitle = 'Dashboard';
        $addLink ='';
        $departments_count = Department::get()->count();
        $doctors_count = Doctor::get()->count();
        return view('home.dashboard',compact('pageTitle','addLink','departments_count','doctors_count'));

    }
    catch (Exception $exception){
       return redirect()->back()->with('error', 'Something went wrong'.$exception->getMessage());
       }
    }


}
