@extends('template_v1')

@section('content')

<div class="tt-posts">
   	<div class="d-flex justify-content-between tt-wrap mb-3">
	  	<div class="p-2 bd-highlight"><h5 class="mb-0 pb-0">{{$pageTitle??''}}</h5></div>
	  	<div class="p-2 bd-highlight">
	  		@if(isset($addlink) && !empty($addlink))
	  		<a href="#offcanvasRight" data-bs-toggle="offcanvas" role="button" aria-controls="offcanvasRight"><i class="fa-solid fa-circle-plus"></i></a>
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
                    <td >{{ Config::get('constants.SNO') }}</td>
                    <td >Name</td>
                    <td >Description</td>
                    <td >Action</td>
                </tr>
            </thead>
            <tbody>
                @foreach($procedures_data as $procedure)
            	 <tr>
                    <td >{{$loop->iteration??''}}</td>
                    <td >{{$procedure->name??''}}</td>
                    <td >{{$procedure->about_procedure??''}}</td>
                    <td><a href="#offcanvasRight" data-id="{{$procedure->id??''}}" data-bs-toggle="offcanvas" role="button" aria-controls="offcanvasRight" class="editPost"><i class="fa-solid fa-pen-to-square"></i></a>&nbsp;&nbsp;<a href="{{ route ('admin.procedure.delete',["ID"=>Crypt::encryptString($procedure->id)] ) }}" title="Delete" onclick="return confirm('Are you sure to delete this?')"><i class="fa-solid fa-trash-can"></i></a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight"
        aria-labelledby="offcanvasRightLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasRightLabel">Add Procedure in {{$departments_data->dept_name??''}}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
           <form action="{{route('admin.procedure.store')}}" method="post" id="Doctor_Form" enctype="multipart/form-data">
            <input type="hidden" name="procedure_id" value="" id="id">
           	@csrf
            <input type="hidden" name="department_id" value="{{$department_id??''}}" id="department_id">
           	<div class="row">
           		<div class="col-md-12">
           			<div class="form-group">
           				<label>Name<span class="imp_str">*</span></label>
           				<input type="text" name="name" class="form-control nameForSlug" id="name">
           			</div>
           		</div>
           		<div class="col-md-12">
           			<div class="form-group">
           				<label>Slug<span class="imp_str">*</span></label>
           				<input type="text" name="slug" class="form-control slugForName" id="slug">
           			</div>
                    <div class="form-group">
                    <label>About Procedure</label>
                    <textarea name="about_procedure" id="about_procedure" class="form-control" rows="5"></textarea>
                </div>
           		</div>
                <!-- <fieldset>
                    <legend>Quick info</legend>
                </fieldset> -->
                <!-- <div class="col-md-6">
                   <div class="form-group">
                        <label>Preparation Time</label>
                        <input type="text" name="preparation_time" class="form-control" id="preparation_time">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Post Procedure Care</label>
                        <input type="text" name="post_procedure_care" class="form-control" id="post_procedure_care">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Procedure Duration</label>
                        <input type="text" name="procedure_duration" class="form-control" id="procedure_duration">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Back to Work</label>
                        <input type="text" name="back_to_work" class="form-control" id="back_to_work">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Approximate Cost</label>
                        <input type="text" name="approximate_cost" class="form-control" id="approximate_cost">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Est. Recovery Period</label>
                        <input type="text" name="est_recovery_period" class="form-control" id="est_recovery_period">
                    </div>
                </div> -->
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
        // CKEDITOR.replace( 'about_procedure' );
</script>

<script>
$(document).ready(function() {

    $("#Doctor_Form").validate({
        rules: {

            name: {
                required: true,
                minlength: 2,
                normalizer: function(value) {
                    // Trim the value of the `field` element before
                    // validating. this trims only the value passed
                    // to the attached validators, not the value of
                    // the element itself.
                    return $.trim(value);
                },
            },
            slug: {
                required: true,
            },

        },
        messages: {
            name: {
                required: "Procedure Name is required",
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

      $.get("{{ route('admin.procedure.edit') }}" +'/' + id , function (data) {
          $('#offcanvasRightLabel').html("Edit Procedure");
          $('#id').val(data.id);
          $('#name').val(data.name);
          $('#slug').val(data.slug);
          $('#preparation_time').val(data.preparation_time);
          $('#post_procedure_care').val(data.post_procedure_care);
          $('#procedure_duration').val(data.procedure_duration);
          $('#back_to_work').val(data.back_to_work);
          $('#approximate_cost').val(data.approximate_cost);
          $('#est_recovery_period').val(data.est_recovery_period);
          $('#about_procedure').val(data.about_procedure);

          var editor = CKEDITOR.instances['about_procedure'];
          editor.setData(data.about_procedure);


      })
   });
</script>


@endpush