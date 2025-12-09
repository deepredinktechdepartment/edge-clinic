<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mail;
use Carbon;
use Config;
use Validator;
use Auth;
use Session;

class ChangePasswordController extends Controller
{
    //

    public function changepassword()
    {
        try{

            $user = auth()->user()??'';
            if($user){
                $User=User::where('id',auth()->user()->id)->get();
                $pageTitle="Change your password";
                $addlink=url('changepassword/store');
                $isajax='0';
                return view('changepassword.lists', compact('isajax','pageTitle','User','addlink'))
                ->with('i', (request()->input('page', 1) - 1) * 5);
            }
            else{
                return redirect()->back()->with('error', 'Session is expired. Please login and try again.');
            }

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Page can\'t access.');
        }
    }
    public function store_changepassword(Request $request)
    {
        try{
        $request->validate([
            'password' => 'required|min:6|max:12',
            'password_confirmation' => 'required_with:password|same:password|min:6|max:12'
        ]);

        $user = auth()->user()??'';
        if($user){
            $Update_Password=User::updateOrCreate(['id' =>$user->id],
                [
                    "password"=> Hash::make($request->password),
                ]
        );

        Auth::logout();
        Session::flush();
        return redirect()->route('admin.login')->with('success', 'Password is changed. Please login with new credentials.');


        }else{


        }

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Page can\'t access.');
        }
    }

    public function edit_changepassword($id)
    {
        try{
        $data=User::get()->where("id",$id)->first();
        return response()->json($data);
        } catch (Exception $e) {
        return redirect()->back()->with('error', 'Page can\'t access.');
        }
    }


}
