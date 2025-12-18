@extends('template_v1')
@section('content')

<div class="tt-posts">
   	<div class="d-flex justify-content-between tt-wrap bg-white mb-3">
	  	<div class="p-2 bd-highlight"><h5 class="mb-0 pb-0">{{$pageTitle??''}}</h5></div>
	  	<div class="p-2 bd-highlight">
	  		@if(isset($addlink) && !empty($addlink))
	  		<a href="#offcanvasRight" data-bs-toggle="offcanvas" role="button" aria-controls="offcanvasRight" class="addPost"><i class="fa-solid fa-circle-plus"></i></a>
	  		@else
			@endif
	  	</div>
	</div>
</div>

<div class="t-job-sheet container-fluid g-0">
    <div class="t-table table-responsive">
       <table
            class="table table-borderless table-hover" id="default-datatable" style="width: 100%;">
            <thead>
                <tr>
                    <td scope="col">{{ Config::get('constants.SNO') }}</td>
                    <td scope="col">Name</td>
                    <td scope="col">Description</td>
                    <td scope="col">Action</td>
                </tr>
            </thead>
            <tbody>
            	@foreach($departments_data as $department)
                <tr>
                    <td>{{$loop->iteration??''}}</td>
                    <td>{{Str::title($department->dept_name??'')}}</td>
                    <td>{{Str::limit($department->dept_description??'', 50)}}</td>
                    <td><a href="#offcanvasRight" data-id="{{$department->id??''}}" data-bs-toggle="offcanvas" role="button" aria-controls="offcanvasRight" class="editPost"><i class="fa-solid fa-pen-to-square"></i></a>&nbsp;&nbsp;<a href="{{ route ('admin.department.delete',["ID"=>Crypt::encryptString($department->id)] ) }}" title="Delete" onclick="return confirm('Are you sure to delete this?')"><i class="fa-solid fa-trash-can"></i></a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight"
        aria-labelledby="offcanvasRightLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasRightLabel">Add Specialization</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
           <form action="{{route('admin.department.store')}}" method="post" id="Department_Form" enctype="multipart/form-data">
            <input type="hidden" name="department_id" value="" id="id">
           	@csrf
           	<div class="row">
           		<div class="col-md-6">
           			<div class="form-group">
           				<label>Name<span class="imp_str">*</span></label>
           				<input type="text" name="dept_name" class="form-control nameForSlug" id="dept_name">
           			</div>
           		</div>
           		<div class="col-md-6">
           			<div class="form-group">
           				<label>Slug<span class="imp_str">*</span></label>
           				<input readonly type="text" name="dept_slug" class="form-control slugForName" id="dept_slug">
           			</div>
           		</div>
           	</div>
           	<div class="form-group">
           		<label>Description<span class="imp_str">*</span></label>
           		<textarea class="form-control" name="dept_description" rows="4" id="dept_description"></textarea>
           	</div>
            <!-- <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Department Icon<span class="imp_str">*</span>&nbsp;&nbsp;<a href="#" data-bs-toggle="tooltip" data-bs-title="Upload PNG, JPG"><i class="fa-solid fa-circle-info"></i></a></label>
                        <input type="file" class="file-input form-control" name="home_icon">
                        <img src="" class="img-fluid pt-1" width="50px" id="home_icon">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Department Banner<span class="imp_str">*</span>&nbsp;&nbsp;<a href="#" data-bs-toggle="tooltip" data-bs-title="Upload PNG, JPG"><i class="fa-solid fa-circle-info"></i></a></label>
                        <input type="file" class="file-input form-control" name="dept_picture">
                        <img src="" class="img-fluid pt-1" width="100px" id="dept_picture" >
                    </div>
                </div>
            </div> -->
            <!-- <div class="form-group">
                    <label>About Department</label>
                    <textarea name="about_dept" id="about_dept"></textarea>
                </div>
                <div class="form-group">
                    <label>About Procedure</label>
                    <textarea name="about_procedure" id="about_procedure"></textarea>
                </div> -->

             <!-- <div class="row">
                <div class="col-md-6">
                   <div class="form-group">
                    <label>Our Approach</label>
                    <textarea name="our_approach" id="our_approach" class="form-control" rows="3"></textarea>
                </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                    <label>Technology & Facilities</label>
                    <textarea name="tech_facility" id="tech_facility" class="form-control" rows="3"></textarea>
                </div>
                </div>
             </div>    -->
           	<div class="row">
                <!-- <div class="col-md-6">
                    <div class="form-group">
                        <label>Procedure Banner<span class="imp_str">*</span>&nbsp;&nbsp;<a href="#" data-bs-toggle="tooltip" data-bs-title="Upload PNG, JPG"><i class="fa-solid fa-circle-info"></i></a></label>
                        <input type="file" class="file-input form-control" name="procedure_banner">
                        <img src="" class="img-fluid pt-1" width="100px" id="procedure_banner" >
                    </div>
                </div> -->
           		<!-- <div class="col-md-6">
           			<label>Is active?<span class="imp_str">*</span></label>
                    <div class="form-group mb-3 d-flex align-items-center gap-3">
                        <div class="form-check d-flex align-items-center gap-2">
                            <input type="radio" class="form-check-input mb-1" name="is_active" value="1" checked>
                            <label class="form-check-label">Published</label>
                        </div>

                        <div class="form-check  d-flex align-items-center gap-2">
                            <input type="radio" class="form-check-input mb-1" name="is_active" value="0"
                                required="required"/>
                            <label class="form-check-label">Draft</label>
                        </div>
                    </div>
           		</div> -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Sort Order<span class="imp_str">*</span></label>
                        <input type="number" min="1" name="sort_order" class="form-control" id="sort_order" value="1">
                    </div>
                </div>
           		<div>
           		</div>
           	</div>
           	<div>
           		<button type="submit" class="btn btn-brand btn-wide btn-sm">Save</button>
           	</div>
           </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
        CKEDITOR.replace( 'about_dept' );
        CKEDITOR.replace( 'about_procedure' );
</script>

<script>
$(document).ready(function() {

    $("#Department_Form").validate({
        rules: {

            dept_name: {
                required: true,
                maxlength: 30,
                minlength: 2,
                normalizer: function(value) {
                    // Trim the value of the `field` element before
                    // validating. this trims only the value passed
                    // to the attached validators, not the value of
                    // the element itself.
                    return $.trim(value);
                },


            },
            dept_description: {
                required: true,
            },

        },
        messages: {
            dept_name: {
                required: "Department Name is required",
                maxlength: jQuery.validator.format("Department too long more than (30) characters"),
                minlength: jQuery.validator.format("At least {0} characters required!"),
            },
            dept_description: {
                required: "Description is required",
            },




        }
    });
});
</script>

<script>
$('body').on('click', '.editPost', function () {
      var id = $(this).data('id');

      $.get("{{ route('admin.department.edit') }}" +'/' + id , function (data) {
          $('#offcanvasRightLabel').html("Edit Specialization");
          $('#id').val(data.id);
          $('#dept_name').val(data.dept_name);
          $('#dept_slug').val(data.dept_slug);
          $('#dept_description').val(data.dept_description);

          $('#our_approach').val(data.our_approach);
          $('#tech_facility').val(data.tech_facility);
          $('#sort_order').val(data.sort_order);

          var selectedOption = data.is_active;
          $('input[name="is_active"]').prop('checked', false); // Uncheck all radio buttons
      $('input[name="is_active"][value="' + selectedOption + '"]').prop('checked', true); // Check the selected radio button

      var editorValue = data.about_dept;

          var editor = CKEDITOR.instances['about_dept'];
          editor.setData(data.about_dept);


          var procedure_editor = CKEDITOR.instances['about_procedure'];
          procedure_editor.setData(data.about_procedure);

        if (data.dept_icon !== null) {
      var homeIconUrl = "{{URL::to('public/uploads/departments/icons/')}}" + '/' + data.dept_icon;
      $('#home_icon').attr('src', homeIconUrl).show();
    } else {
      $('#home_icon').hide();
    }

    if (data.dept_banner !== null) {
      var deptPictureUrl = "{{URL::to('public/uploads/departments/pictures/')}}" + '/' + data.dept_banner;
      $('#dept_picture').attr('src', deptPictureUrl).show();
    } else {
      $('#dept_picture').hide();
    }

    if (data.procedure_banner !== null) {
      var deptPictureUrl = "{{URL::to('public/uploads/departments/pictures/')}}" + '/' + data.procedure_banner;
      $('#procedure_banner').attr('src', deptPictureUrl).show();
    } else {
      $('#procedure_banner').hide();
    }




      })
   });


   $('body').on('click', '.addPost', function () {
    $('#offcanvasRightLabel').html("Add Specialization");
    var editorValue = "";
    var editor = CKEDITOR.instances['about_dept'];
    editor.setData(editorValue);
    document.getElementById("dept_picture").value = null;
    $('#dept_picture').hide();
    document.getElementById("home_icon").value = null;
    $('#home_icon').hide();
    document.getElementById("Department_Form").reset();
    return false;

});

</script>


@endpush