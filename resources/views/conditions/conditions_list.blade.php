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
            class="table table-borderless table-hover table-centered align-middle table-nowrap mb-0">
            <thead>
                <tr>
                    <td width="5%">{{ Config::get('constants.SNO') }}</td>
                    <td scope="col">Icon</td>
                    <td scope="col">Name</td>
                    <td scope="col">Description</td>
                    <td scope="col">Action</td>
                </tr>
            </thead>
            <tbody>
                @foreach($conditions_data as $condition)
            	 <tr>
                    <td width="5%">{{$loop->iteration??''}}</td>
                    <td>
                        @if(isset($condition->icon_path))
                        <div style="background-color: #D13438; padding: 15px 20px; width:80px; height: 75px; border-radius: 10px;">
                        <img src="{{URL::to('public/uploads/departments/conditions/'.$condition->icon_path??'')}}" class="img-fluid" width="50px" />
                        </div>
                        @else

                        @endif
                    </td>
                    <td scope="col">{{$condition->name??''}}</td>
                    <td scope="col">{{$condition->description??''}}</td>
                    <td scope="col"><a href="#offcanvasRight" data-id="{{$condition->id??''}}" data-bs-toggle="offcanvas" role="button" aria-controls="offcanvasRight" class="editPost"><i class="fa-solid fa-pen-to-square"></i></a>&nbsp;&nbsp;<a href="{{ route ('admin.condition.delete',["ID"=>Crypt::encryptString($condition->id)] ) }}" title="Delete" onclick="return confirm('Are you sure to delete this?')"><i class="fa-solid fa-trash-can"></i></a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight"
        aria-labelledby="offcanvasRightLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasRightLabel">Add Condition in {{$departments_data->dept_name??''}}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
           <form action="{{route('admin.condition.store')}}" method="post" id="Doctor_Form" enctype="multipart/form-data">
            <input type="hidden" name="condition_id" value="" id="id">
           	@csrf
            <input type="hidden" name="department_id" value="{{$department_id??''}}" id="department_id">
           	<div class="row">
           		<div class="col-md-6">
           			<div class="form-group">
           				<label>Name<span class="imp_str">*</span></label>
           				<input type="text" name="name" class="form-control nameForSlug" id="name">
           			</div>
           		</div>
           		<div class="col-md-6">
           			<div class="form-group">
           				<label>Slug<span class="imp_str">*</span></label>
           				<input type="text" name="slug" class="form-control slugForName" id="slug">
           			</div>
           		</div>
                <div class="form-group">
                    <label>About Condition</label>
                    <textarea name="description" class="form-control" id="about_condition" rows="3"></textarea>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Condition Icon<span class="imp_str">*</span>&nbsp;&nbsp;<a href="#" data-bs-toggle="tooltip" data-bs-title="Upload PNG, JPG"><i class="fa-solid fa-circle-info"></i></a></label>
                        <input type="file" class="file-input form-control" name="icon_path">
                        <img src="" class="img-fluid pt-1" width="50px" id="icon_path">
                    </div>
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
<!-- <script>
        CKEDITOR.replace( 'about_procedure' );
</script> -->

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
                required: "Condition Name is required",
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

      $.get("{{ route('admin.condition.edit') }}" +'/' + id , function (data) {
          $('#offcanvasRightLabel').html("Edit Procedure");
          $('#id').val(data.id);
          $('#name').val(data.name);
          $('#slug').val(data.slug);
          $('#about_condition').val(data.description);

           if (data.icon_path !== null) {
      var homeIconUrl = "{{URL::to('public/uploads/departments/conditions/')}}" + '/' + data.icon_path;
      $('#home_icon').attr('src', homeIconUrl).show();
    } else {
      $('#home_icon').hide();
    }


      })
   });
</script>


@endpush