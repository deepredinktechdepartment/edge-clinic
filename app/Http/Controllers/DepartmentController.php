<?php
namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Departments_Faq;
use App\Models\Procedure;
use App\Models\Condition;
use App\Models\Doctor;
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


class DepartmentController extends Controller
{
    public function faqs()
    {
        try{
            $departments_data = Department::orderBy('dept_name','ASC')->get();
            $department_faqs=Departments_Faq::leftjoin('departments','departments.id','=','faqs.department_id')->orderBy('departments.dept_name','ASC')->get(['faqs.*','departments.dept_name']);
            $pageTitle="FAQs";
            $addlink=url('faq/store');
            $isajax='0';
            return view('departments.faqs.lists', compact('isajax','pageTitle','departments_data','addlink','department_faqs'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Page can\'t access.');
        }
    }
    public function filter_faqs(Request $request)
    {
        try{

            $query = $request->get('query');

                $departments_data = Department::orderBy('dept_name','ASC')->get();
                $department_faqs=Departments_Faq::leftjoin('departments','departments.id','=','faqs.department_id')
                 ->where(function($department_faqs) use ($query){
                    if($query){
                        $department_faqs->where('department_id',$query);
                    }
                })
                ->orderBy('departments.dept_name','ASC')->get(['faqs.*','departments.dept_name']);
                $pageTitle="FAQs";
                $addlink=url('faq/store');
                $isajax='1';
                return $doctors_data =view('departments.faqs.renderintable',compact('department_faqs'))->render();


        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Page can\'t access.');
        }
    }

    public function store_faq(Request $request)
    {

        try{

        if(empty($request->faq_answer)){
            return redirect()->back()->with('error', 'FAQ Description should not be empty.');
        }


        $request->validate([
            'department_id' => 'required',
            'faq_question' => 'required',
            'faq_answer' => 'required',
            'is_active' => 'sometimes|nullable',

        ]);

        if($request->department_faq_id){

            $Update_FAQ=Departments_Faq::updateOrCreate(['id' => $request->department_faq_id],
                [
                    "department_id"=>$request->department_id??'',
                    "faq_question"=>$request->faq_question??'',
                    "faq_answer"=>$request->faq_answer??'',
                    "is_active"=>$request->is_active??0,
                ]
        );
            return redirect()->route('admin.faqs')->with('success', 'Department FAQ Updated successfully!!');

        }else{


            $Insert_FAQ=Departments_Faq::updateOrCreate(['id' => $request->department_faq_id],
            [
            "department_id"=>$request->department_id??'',
            "faq_question"=>$request->faq_question??'',
            "faq_answer"=>$request->faq_answer??'',
            "is_active"=>$request->is_active??0,
            ]
            );
            return redirect()->route('admin.faqs')->with('success', 'Department FAQ Saved successfully!!');
        }

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Page can\'t access.');
        }
    }

    public function edit_faq($id)
    {
            try{
            $data=Departments_Faq::get()->where("id",$id)->first();
            return response()->json($data);
            } catch (Exception $e) {
            return redirect()->back()->with('error', 'Page can\'t access.');
            }
    }


    public function delete_faq(Request $request)
    {
        try{
        $ID = Crypt::decryptString($request->ID);
        $DeleteRecord=Departments_Faq::where("id",$ID)->delete();
        if($DeleteRecord){
        return redirect()->back()->with('success','Record is deleted.');
        }
        else{
            return redirect()->back()->with('error','Record is not deleted');
        }

    } catch (Exception $e) {
        return redirect()->back()->with('error', 'Page can\'t access.');
    }

    }

    public function departments_list()
    {

            $departments_data=Department::orderBy('sort_order','ASC')->get();
            foreach ($departments_data as $key => $value) {
                $departments_data[$key]->procedure_count=Procedure::where('department_id',$value->id??'')->get()->count();

                $departments_data[$key]->conditions_count=Condition::where('department_id',$value->id??'')->get()->count();

            }

            $pageTitle="Specializations";
            $addlink=url('admin/departments/create');
            return view('departments.department_list', compact('pageTitle','departments_data','addlink'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }
    public function create_departments()
    {

            $pageTitle="Add New";
            return view('departments.add_edit_departments',compact('pageTitle'));

    }
    public function store_department(Request $request)
    {


        $request->validate([
            'dept_name' => 'required',
            'dept_description' => 'required'      

        ]);

        if($request->department_id){

            if ($request->hasFile('home_icon')) {
            $homeicon_filename=$request->dept_slug.'-'.time().'.'.$request->home_icon->extension();
            $request->home_icon->move(public_path('uploads/departments/icons'),$homeicon_filename);

               Department::where('id', $request->department_id)
            ->update(["dept_icon"=>$homeicon_filename]);
            }
            else{
                $homeicon_filename="";
            }

            if ($request->hasFile('dept_picture')) {
            $dept_pic_filename=$request->dept_slug.'-'.time().'.'.$request->dept_picture->extension();
            $request->dept_picture->move(public_path('uploads/departments/pictures'),$dept_pic_filename);

               Department::where('id', $request->department_id)
            ->update(["dept_banner"=>$dept_pic_filename]);
            }
            else{
                $dept_pic_filename="";
            }

            if ($request->hasFile('procedure_banner')) {
            $dept_procedure_filename=$request->dept_slug.'-'.time().'.'.$request->procedure_banner->extension();
            $request->procedure_banner->move(public_path('uploads/departments/pictures'),$dept_procedure_filename);

               Department::where('id', $request->department_id)
            ->update(["procedure_banner"=>$dept_procedure_filename]);
            }
            else{
                $dept_procedure_filename="";
            }

            Department::updateOrCreate(['id' => $request->department_id],
                ["dept_name"=>$request->dept_name??'',
                "dept_slug"=>$request->dept_slug??'',
                "dept_description"=>$request->dept_description??'',
                "about_dept"=>$request->about_dept??null,
                "about_procedure"=>$request->about_procedure??null,
                "our_approach"=>$request->our_approach??null,
                "tech_facility"=>$request->tech_facility??null,
                "is_active"=>$request->is_active??1,
                "sort_order"=>$request->sort_order??1,
            ]);
            return redirect()->route('admin.specializations')->with('success', 'Department Saved successfully!!');

        }else{
            $isexist = Department::select('id')->where('dept_name',$request->dept_name)->get();
            if($isexist->count()==0){


            if ($request->hasFile('home_icon')) {
            $homeicon_filename=$request->dept_slug.'-'.time().'.'.$request->home_icon->extension();
            $request->home_icon->move(public_path('uploads/departments/icons'),$homeicon_filename);
            }
            else{
                $homeicon_filename="";
            }

            if ($request->hasFile('dept_picture')) {
            $dept_pic_filename=$request->dept_slug.'-'.time().'.'.$request->dept_picture->extension();
            $request->dept_picture->move(public_path('uploads/departments/pictures'),$dept_pic_filename);
            }
            else{
                $dept_pic_filename="";
            }

            if ($request->hasFile('procedure_banner')) {
            $dept_procedure_filename=$request->dept_slug.'-'.time().'.'.$request->procedure_banner->extension();
            $request->procedure_banner->move(public_path('uploads/departments/pictures'),$dept_procedure_filename);
            }
            else{
                $dept_procedure_filename="";
            }

            Department::updateOrCreate(['id' => $request->department_id],
                ["dept_name"=>$request->dept_name??'',
                "dept_slug"=>$request->dept_slug??'',
                "dept_description"=>$request->dept_description??null,
                "about_dept"=>$request->about_dept??null,
                "about_procedure"=>$request->about_procedure??null,           
                "sort_order"=>$request->sort_order??1,
                "dept_banner"=>$dept_pic_filename??null,
                "dept_icon"=>$homeicon_filename??null,
                "procedure_banner"=>$dept_procedure_filename??null,
                "our_approach"=>$request->our_approach??null,
                "tech_facility"=>$request->tech_facility??null,
            ]);
            return redirect()->route('admin.specializations')->with('success', 'Department Saved successfully!!');
        }else{

         return redirect()->back()->with('error', 'Department already exist an account.');
        }
        }

    }
    public function edit_departments($id)
    {



            $data=Department::get()->where("id",$id)->first();
       

        // Return the data as JSON
        return response()->json($data);


    }
    public function update_departments(Request $request)
    {
        $request->validate([
            'department_name' => 'required',
            'is_display' => 'sometimes|nullable',
        ]);



        Department::where('id', $request->Department_ID)
            ->update(
            [



                "department_name"=>$request->department_name??'',
                "is_active"=>$request->is_active??'',
            ]
            );


        return redirect('employee/departments')->with('success', "Success! Details are updated successfully");
    }
    public function delete_departments(Request $request)
    {
        $D_ID = Crypt::decryptString($request->ID);
        $departments_name=Department::where("id",$D_ID)->delete();
        if($departments_name){

        return redirect()->back()->with('success','Success! Data deleted');
        }
        else{
            return redirect()->back()->with('error','Something went wrong');
        }

    }


    /* Procedure */

public function procedures_list(Request $request)
    {

        $department_id = Crypt::decryptString($request->ID);
        $departments_data = Department::orderBy('dept_name','ASC')->where('id',$department_id)->get()->first();
        $procedures_data =Procedure::where('department_id',$department_id)->get();
        $pageTitle="Procedures in " .($departments_data->dept_name??'')." Department";


        $addlink=url('admin/procedures/create');
        return view('procedures.procedures_list', compact('pageTitle','departments_data','addlink','department_id','procedures_data'))
        ->with('i', (request()->input('page', 1) - 1) * 5);
    }

public function procedure_store(Request $request)
    {

        $request->validate([
          'name' => 'required',
          'slug' => 'required',
        ]);

        try {
            Procedure::updateOrCreate(['id' => $request->procedure_id],
                [
                "department_id"=>$request->department_id??'',
                "name"=>$request->name??'',
                "slug"=>$request->slug??'',
                "about_procedure"=>$request->about_procedure??'',
                "preparation_time"=>$request->preparation_time??'',
                "post_procedure_care"=>$request->post_procedure_care??'',
                "procedure_duration"=>$request->procedure_duration??'',
                "back_to_work"=>$request->back_to_work??'',
                "approximate_cost"=>$request->approximate_cost??'',
                "est_recovery_period"=>$request->est_recovery_period??'',
            ]);
    return redirect()->route('admin.procedures.view', ['ID'=>Crypt::encryptString($request->department_id)])->with('message','Success! Details are added successfully');

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Page can\'t access.');
        }

    }


    public function edit_procedure($id)
    {

            $data=Procedure::get()->where("id",$id)->first();
    

        // Return the data as JSON
        return response()->json($data);


    }


    public function delete_procedure(Request $request)
    {
        $D_ID = Crypt::decryptString($request->ID);
        $departments_name=Procedure::where("id",$D_ID)->delete();
        if($departments_name){

        return redirect()->back()->with('success','Success! Details are deleted successfully');
        }
        else{
            return redirect()->back()->with('error','Something went wrong');
        }

    }


/* Frontend Department View */


public function show_department($slug=false){
    try {
        if($slug){
            $department_data = Department::where('dept_slug',$slug)->get()->first();
            $pageTitle="Department of ".$department_data->dept_name??'';
            
              $doctors_data =  Doctor::leftjoin('departments','departments.id','=','doctors.department_id')->orderBy('doctors.department_id','ASC')->orderBy('doctors.sort_order','ASC')->where('departments.dept_slug',$slug)->where('doctors.is_active',1)->get(['doctors.*','departments.dept_name']);
            $conditions_data = Condition::leftJoin('departments','departments.id', '=', 'conditions.department_id')->where('departments.dept_slug',$slug)->get(['conditions.*']);

            $procedures_data = Procedure::leftJoin('departments','departments.id', '=', 'procedures.department_id')->where('departments.dept_slug',$slug)->get(['procedures.*']);

           

            return view('frontend.home.single_department',compact('pageTitle','department_data','doctors_data','conditions_data','procedures_data'));
        }
        else{
            return redirect()->back()->with('error', 'Page can\'t access.');
        }


    } catch (Exception $e) {
        return redirect()->back()->with('error', 'Page can\'t access.');

    }


}


public function show_condition($id)
    {
        $user = Condition::find($id);

      
  
        return response()->json($user);
    }
public function show_procedures_list($id)
    {
        $user = Procedure::find($id);

      
  
        return response()->json($user);
    }

    // Forntend Show Procedures
public function show_procedures(Request $request)
    {



        $department_slug = $request->slug??'';
        $departments_data = Department::where('dept_slug',$department_slug)->get()->first();
        $procedures_data = Procedure::leftJoin('departments','departments.id', '=', 'procedures.department_id')->where('departments.dept_slug',$department_slug)->get(['procedures.*']);
        $doctors_data = Doctor::leftJoin('departments','departments.id', '=', 'doctors.department_id')->where('departments.dept_slug',$department_slug)->get(['doctors.*']);

        $pageTitle="Procedures in " .($departments_data->dept_name??'');

        return view('frontend.home.show_procedures', compact('pageTitle','departments_data','procedures_data','doctors_data'))
        ->with('i', (request()->input('page', 1) - 1) * 5);
    }



// Conditions

public function conditions_list(Request $request)
    {

        $department_id = Crypt::decryptString($request->ID);
        $departments_data = Department::orderBy('dept_name','ASC')->where('id',$department_id)->get()->first();
        $conditions_data =Condition::where('department_id',$department_id)->get();
        $pageTitle="Conditions in " .($departments_data->dept_name??'')." Department";


        $addlink=url('admin/conditions/create');
        return view('conditions.conditions_list', compact('pageTitle','departments_data','addlink','department_id','conditions_data'))
        ->with('i', (request()->input('page', 1) - 1) * 5);
    }


public function condition_store(Request $request)
    {


        $request->validate([
            'name' => 'required',
            'description' => 'required',

        ]);

        if($request->condition_id){

            if ($request->hasFile('icon_path')) {
            $homeicon_filename=$request->dept_slug.'-'.time().'.'.$request->icon_path->extension();
            $request->icon_path->move(public_path('uploads/departments/conditions'),$homeicon_filename);

               Condition::where('id', $request->condition_id)
            ->update(["icon_path"=>$homeicon_filename]);
            }
            else{
                $homeicon_filename="";
            }

            Condition::updateOrCreate(['id' => $request->condition_id],
                ["name"=>$request->name??'',
                "slug"=>$request->slug??'',
                "department_id"=>$request->department_id??'',
                "description"=>$request->description??'',]);
             return redirect()->route('admin.conditions.view', ['ID'=>Crypt::encryptString($request->department_id)])->with('message','Success! Details are added successfully');

        }else{
            $isexist = Condition::select('id')->where('name',$request->name)->get();
            if($isexist->count()==0){


            if ($request->hasFile('icon_path')) {
            $homeicon_filename=$request->dept_slug.'-'.time().'.'.$request->icon_path->extension();
            $request->icon_path->move(public_path('uploads/departments/conditions'),$homeicon_filename);
            }
            else{
                $homeicon_filename="";
            }

            Condition::updateOrCreate(['id' => $request->condition_id],
                ["name"=>$request->name??'',
                "slug"=>$request->slug??'',
                "department_id"=>$request->department_id??'',
                "description"=>$request->description??'',
                "icon_path"=>$homeicon_filename,
            ]);
             return redirect()->route('admin.conditions.view', ['ID'=>Crypt::encryptString($request->department_id)])->with('message','Success! Details are added successfully');
        }else{

         return redirect()->back()->with('error', 'Department already exist an account.');
        }
        }

    }

    public function edit_condition($id)
    {

            $data=Condition::get()->where("id",$id)->first();
           

        // Return the data as JSON
        return response()->json($data);


    }


    public function delete_condition(Request $request)
    {
        $D_ID = Crypt::decryptString($request->ID);
        $departments_name=Condition::where("id",$D_ID)->delete();
        if($departments_name){

        return redirect()->back()->with('success','Success! Details are deleted successfully');
        }
        else{
            return redirect()->back()->with('error','Something went wrong');
        }

    }

}
