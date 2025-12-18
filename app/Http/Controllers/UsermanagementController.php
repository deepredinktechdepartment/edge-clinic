<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Hash;
use Validator;
use Auth;
use Illuminate\Support\Facades\Session;
use Carbon;
Use Exception;
use Illuminate\Support\Facades\Crypt;
use Config;

class UsermanagementController extends Controller
{
	// use PasswordValidationRules;

    public function index()
    {
        try{
            $users_data=User::select('users.*','user_types.name as ut_name')
            ->leftjoin('user_types','user_types.id','=','users.role')
            ->whereNotIn('users.role',[4])
            ->get();
            $user_type_data=DB::table('user_types')->whereNotIn('id',[1])->get();
            $addlink=url('admin/user/create');
            $pageTitle="Users";
            return view('users.users_list', compact('pageTitle','users_data','addlink','user_type_data'));
            } catch (Exception $e) {
        return redirect()->back()->with('error', 'Page can\'t access.');

    }

    }
    public function store_user(Request $request)
    {

		try{
        $request->validate([
         'name' => 'required|min:1|max:100',
         'email' => 'required|email',
         'role' => 'required',
         'phone' => 'required',
         'profile' => 'mimes:jpg,jpeg,png',
        ]);


        if($request->user_id){

            if ($request->hasFile('profile_picture')) {
        $profile_filename=$request->name.'-'.time().'.'.$request->profile_picture->extension();
        $request->profile_picture->move(public_path('uploads/users'),$profile_filename);

        User::where('id', $request->user_id)
            ->update(["profile_picture"=>$profile_filename]);
        }
        else{
            $profile_filename="";
        }

        User::where('id', $request->user_id)
            ->update([

                "name"=>$request->name??'',
                "role"=>$request->role,
                "email"=>$request->email,
                "phone"=>$request->phone??'',
                "is_active"=>$request->is_active??1,
                'system_ip' =>request()->ip()??0,
        ]);

    return redirect()->route('admin.users')->with('success', "Success! Details are added successfully");

        }else{

            $isexistemail = User::select('id')->where('email',$request->email)->get();
            if($isexistemail->count()==0){


        if ($request->hasFile('profile_picture')) {
        $profile_filename=$request->name.'-'.time().'.'.$request->profile_picture->extension();
        $request->profile_picture->move(public_path('uploads/users'),$profile_filename);
        }
        else{
            $profile_filename="";
        }

        User::insert([
            [

                "name"=>$request->name??'',
                "role"=>$request->role,
                "email"=>$request->email,
                "password"=> Hash::make($request->password),
                "phone"=>$request->phone??'',
                "profile_picture"=>$profile_filename??'',
                "is_active"=>$request->is_active??1,
                'email_verified_at' =>Carbon\Carbon::now(),
                'system_ip' =>request()->ip()??0,
            ]
        ]);


        return redirect()->route('admin.users')->with('success', "Success! Details are added successfully");
    }

    else{

         return redirect()->back()->with('error', 'User already exist an account.');
     }

        }

        } catch (Exception $e) {
        return redirect()->back()->with('error', 'Page can\'t access.');

    }

	}
	public function edit_user($id){

        try{

            $data=User::where("id",$id)->get()->first();
            return response()->json($data);

            } catch (Exception $e) {
        return redirect()->back()->with('error', 'Page can\'t access.');

    }

    }
    public function delete_user(Request $request)
    {
        try{
        $user_id=Crypt::decryptString($request->ID);
        $data=User::where('id',$user_id)->delete();
        return redirect()->back()->with('success','Success! Details are deleted successfully');

        } catch (Exception $e) {
        return redirect()->back()->with('error', 'Page can\'t access.');

    }

    }


    public function profile(Request $request)
    {
        try{

            $user = auth()->user()??'';
            if($user){
                $User=User::where('id',auth()->user()->id)->get()->first();
                $pageTitle="Profile";
                $addlink=url('profile/store');
                $isajax='0';
                return view('profile.lists', compact('isajax','pageTitle','User','addlink'))
                ->with('i', (request()->input('page', 1) - 1) * 5);
            }
            else{
                return redirect()->back()->with('error', 'Session is expired. Please login and try again.');
            }

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Page can\'t access.');
        }

    }

    public function edit_profile($id)
    {
        try{
        $data=User::get()->where("id",$id)->first();
        return response()->json($data);
        } catch (Exception $e) {
        return redirect()->back()->with('error', 'Page can\'t access.');
        }
    }

    public function store_profile(Request $request)
    {
        try{
        $request->validate([
            'name' => 'required',
            'phone' => 'required'
        ]);
        $user = auth()->user()??'';
        if($user){

            if ($request->hasFile('profile_picture')) {
                $profile_filename=$request->name.'-'.time().'.'.$request->profile_picture->extension();
                $request->profile_picture->move(public_path('uploads/users'),$profile_filename);
                }
                else{
                    $profile_filename=$user->profile_picture??'';
                }

            $Update_Profile=User::updateOrCreate(['id' =>$user->id],
                [
                    "name"=> $request->name??'',
                    "phone"=> $request->phone??'',
                    "profile_picture"=> $profile_filename??'',

                ]
        );


        return redirect()->route('admin.profile')->with('success', 'Profile is updated.');


        }else{


        }

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Page can\'t access.');
        }
    }

}
