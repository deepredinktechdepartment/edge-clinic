<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Department;
use App\Models\DoctorVideo;
use Illuminate\Http\Request;
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

class DoctorController extends Controller
{
     // -------------------------------------------------------
    // Show Doctors List Page
    // -------------------------------------------------------
public function index()
{
    $doctors = Doctor::with('department')
        ->orderByRaw("TRIM(REPLACE(name, 'Dr. ', '')) ASC")
        ->get();

    return view('appointment.doctors', compact('doctors'));
}
public function show($slug)
{
    // Fetch doctor by slug with department
    $doctor = Doctor::with('department')
        ->where('slug', $slug)
        ->firstOrFail();

    return view('appointment.doctor-single', compact('doctor'));
}

public function bookAppointment($doctor_id = null)
{
    // Fetch doctor
    $doctor = Doctor::find($doctor_id);

    if (!$doctor) {
        return redirect()->route('doctors.list')
            ->with('error', 'Doctor not found');
    }

    // Load appointment booking page
    return view('appointment.book', compact('doctor'));
}
 public function appointmentsStore(Request $request)
    {
            $appointmentFee = 500; // Example fee, you can calculate dynamically
            $appointmentDate = $request->input('appointment_date'); // get date from previous form

            return view('appointment.patient_form', [
            'appointmentFee' => $appointmentFee,
            'appointmentDate' => $appointmentDate,
            'appointmentData' => $request->all()
            ]);
    }


    public function doctors_list()
    {


        $doctors_data=Doctor::leftjoin('departments','departments.id','=','doctors.department_id')->orderBy('doctors.department_id','ASC')->orderBy('doctors.is_active','DESC')->orderBy('doctors.sort_order','ASC')->get(['doctors.*','departments.dept_name']);
        $departments_data = Department::orderBy('dept_name','ASC')->get();
        $pageTitle="Doctors";
        $addlink=url('admin/doctors/create');
        return view('doctors.doctors_list', compact('pageTitle','doctors_data','addlink','departments_data'))
        ->with('i', (request()->input('page', 1) - 1) * 5);



    }
    public function store_doctor(Request $request)
    {


        $request->validate([
            'department_id' => 'required',
            'name' => 'required',
            'designation' => 'required',
            'qualification' => 'required',
            'slug' => 'sometimes|nullable',
            'slots' => 'nullable',

        ]);

 $slots = [];

    if ($request->has('slots')) {
        foreach ($request->slots as $slot) {

            if (!isset($slot['days']) || !isset($slot['session'])) {
                continue; // skip empty slots
            }

            $slots[] = [
                'days'       => $slot['days'],
                'session'    => $slot['session'], // array
                'start_time' => $slot['start_time'] ?? '',
                'end_time'   => $slot['end_time'] ?? '',
            ];
        }
    }


    if($request->doctor_id) {

        if ($request->hasFile('profile_pic')) {
        $profile_filename=$request->slug.'-'.time().'.'.$request->profile_pic->extension();
        $request->profile_pic->move(public_path('uploads/doctors'),$profile_filename);

           Doctor::where('id', $request->doctor_id)
        ->update(["photo"=>$profile_filename]);
        }
        else{
            $profile_filename="";
        }

            Doctor::updateOrCreate(['id' => $request->doctor_id],
                ["department_id"=>$request->department_id??'',
                "name"=>$request->name??'',
                "slug"=>$request->slug??'',
                "designation"=>$request->designation??'',
                "qualification"=>$request->qualification??'',
                "experience"=>$request->experience??'',
                "expertise"=>$request->expertise??'',                
                "awards"=>$request->awards??'',
                "sort_order"=>$request->sort_order??1,
                "is_active"=>$request->is_active??1,
                "bio"=>$request->bio??'',
                "slots"=> json_encode($slots), // SAVE JSON
                "online_payment"=> $request->has('online_payment') ? 1 : 0
            ]);


    }else{

        if ($request->hasFile('profile_pic')) {
        $profile_filename=$request->slug.'-'.time().'.'.$request->profile_pic->extension();
        $request->profile_pic->move(public_path('uploads/doctors'),$profile_filename);
        }
        else{
            $profile_filename="";
        }
            Doctor::updateOrCreate(['id' => $request->doctor_id],
                ["department_id"=>$request->department_id??'',
                "name"=>$request->name??'',
                "slug"=>$request->slug??'',
                "designation"=>$request->designation??'',
                "qualification"=>$request->qualification??'',
                "experience"=>$request->experience??'',
                "expertise"=>$request->expertise??'',
                "sort_order"=>$request->sort_order??1,
                "awards"=>$request->awards??'',
                "bio"=>$request->bio??'',
                "is_active"=>$request->is_active??1,
                "photo"=>$profile_filename??'',
                "slots"         => json_encode($slots), // SAVE JSON
                "online_payment"=> $request->has('online_payment') ? 1 : 0
            ]);

    }




       return redirect()->route('admin.doctors')->with('success', 'Doctor Saved successfully!!');

    }
    public function edit_doctors($id)
    {



            $data=Doctor::where("id",$id)->get()->first();

        // Return the data as JSON
        return response()->json($data);


    }
    public function update_departments(Request $request)
    {
        $request->validate([
            'department_name' => 'required',
            'is_display' => 'sometimes|nullable',
        ]);

        // dd($request->Department_ID);


        Department::where('id', $request->Department_ID)
            ->update(
            [



                "department_name"=>$request->department_name??'',
                "is_active"=>$request->is_active??'',
            ]
            );


        return redirect('employee/departments')->with('success', "Success! Details are updated successfully");
    }
    public function delete_doctors(Request $request)
    {
        $D_ID = Crypt::decryptString($request->ID);
        $departments_name=Doctor::where("id",$D_ID)->delete();
        if($departments_name){

        return redirect()->back()->with('success','Success! Details are deleted successfully');
        }
        else{
            return redirect()->back()->with('error','Something went wrong');
        }

    }

    public function show_doctors(Request $request){
        try {
                $doctors_data = Doctor::leftjoin('departments','departments.id','=','doctors.department_id')->orderBy('departments.sort_order','ASC')->orderBy('doctors.sort_order','ASC')->where('doctors.is_active',1)->get(['doctors.*','departments.dept_name']);
                // dd($doctors_data);
                $pageTitle="Doctors";
                return view('frontend.home.doctors',compact('pageTitle','doctors_data'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Page can\'t access.');

        }

    }

    public function show_single_doctor($slug){
        try {
                $doctors_data = Doctor::where('slug',$slug)->get()->first();

                // dd($doctors_data);

                $doctors_video_data = DoctorVideo::where('doctor_id',$doctors_data->id??'')->latest();
                $pageTitle="Doctors";
                return view('frontend.home.single_doctor',compact('pageTitle','doctors_data'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Page can\'t access.');

        }

    }

    public function search_Doctors(Request $request){
        try{
        $query = $request->get('query');
        if ($request->ajax()) {
            if(Str::length($query)>=2 ) {
            $doctors_data = Doctor::leftJoin('departments','departments.id', '=', 'doctors.department_id')
                ->where('doctors.name', 'LIKE', '%'.$query.'%')
                ->orWhere('departments.dept_name', 'LIKE', '%'.$query.'%')
                ->get();
           $doctors_data =view('frontend.childviews.doctor',compact('doctors_data'))->render();
            return $doctors_data;
            }
            else{
                return "<p class='text-center'>Please enter at least two characters for getting doctors.</p>";
            }
        }
        } catch (Exception $e) {
        return redirect()->back()->with('error', 'Page can\'t access.');
        }
    }


    /* Doctor Videos */



public function doctor_videos_list()
    {


        $doctors_videos_data=DoctorVideo::leftjoin('doctors','doctors.id','=','doctor_videos.doctor_id')->take(12)->get(['doctor_videos.*','doctors.name']);
        $doctors_data = Doctor::orderBy('name','ASC')->get();
        $pageTitle="Doctors Videos";
        $addlink=url('admin/doctors/create');
        return view('doctors.doctor_videos_list', compact('pageTitle','doctors_data','addlink','doctors_videos_data'))
        ->with('i', (request()->input('page', 1) - 1) * 5);



    }


    public function doctor_video_store(Request $request)
    {

        $request->validate([
         'doctor_id' => 'required',
          'youtube_url' => 'required',
          'description' => 'required',
        ]);
        try {


            DoctorVideo::updateOrCreate(['id' => $request->doctor_video_id],
                [
                 "doctor_id"=>$request->doctor_id??'',
                "youtube_url"=>$request->youtube_url??'',
                "description"=>$request->description??'',
            ]);

    return redirect()->route('admin.doctor-videos')->with('success', "Success! Details are added successfully");

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Page can\'t access.');
        }

    }

    public function filter_doctor_video(Request $request)
    {
        try{

            $query = $request->get('query');

                $doctors_data = Doctor::orderBy('name','ASC')->get();
                $doctors_videos_data=DoctorVideo::leftjoin('doctors','doctors.id','=','doctor_videos.doctor_id')
                 ->where(function($doctors_videos_data) use ($query){
                    if($query){
                        $doctors_videos_data->where('doctor_id',$query);
                    }
                })
                ->orderBy('doctors.name','ASC')->get(['doctor_videos.*','doctors.name']);
                $pageTitle="FAQs";
                $isajax='1';
                return $doctors_data =view('doctors.renderintable',compact('doctors_videos_data'))->render();


        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Page can\'t access.');
        }
    }

    public function edit_doctor_video($id)    {
        try{

            $data=DoctorVideo::where("id",$id)->get()->first();
            return response()->json($data);

            } catch (Exception $e) {
        return redirect()->back()->with('error', 'Page can\'t access.');

    }
    }

    public function delete_doctor_video(Request $request)    {
        try{
        $blog_id=Crypt::decryptString($request->ID);
        $data=DoctorVideo::where('id',$blog_id)->delete();
        return redirect()->back()->with('success','Success! Details are deleted successfully');

        } catch (Exception $e) {
        return redirect()->back()->with('error', 'Page can\'t access.');

    }

    }


}
