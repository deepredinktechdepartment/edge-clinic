@extends('template_v1')

@section('content')



<div class="tt-posts">
   	<div class="d-flex justify-content-between tt-wrap bg-white mb-3">
	  	<div class="p-2 bd-highlight"><h5 class="mb-0 pb-0">{{ $pageTitle??'' }}</h5></div>

	</div>
</div>




<div class="row">
<div class="col-md-12">
<div class="t-table table-responsive">
    <table
        class="table table-borderless table-hover table-centered align-middle table-nowrap mb-0">
        <thead>
            <tr>
                <td scope="col">S.No.</td>
                <td scope="col">Name</td>
                <td scope="col">Status</td>
                <td scope="col">Click on icon to change your password.</td>
            </tr>
        </thead>
        <tbody>
            @foreach($User as $item)
            <tr>
                <td>{{$loop->iteration??''}}</td>
                <td>{{Str::title($item->name??'')}}</td>
                <td>
                        @if($item->is_active==1)
                        <span class="badge bg-success">Active</span>
                        @else
                        <span class="badge bg-danger">Deactivated</span>
                        @endif
                </td>
                <td><a title="click here to change your password" alt="click here to change your password" href="#offcanvasRight" data-id="{{$item->id??''}}" data-bs-toggle="offcanvas" role="button" aria-controls="offcanvasRight" class="editPassword"><i class="fa-solid fa-key"></i></a>
                 </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
</div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight"
        aria-labelledby="offcanvasRightLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasRightLabel">Change Password</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
           <form action="{{route('admin.changepassword.store')}}" method="post" id="Changepassword_Form" enctype="multipart/form-data">

           	@csrf


            <div class="form-group">
                <label>Name : @php echo auth()->user()->name??'' @endphp </label>
            </div>

            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" name="password" id="password" class="form-control">
                @error('password')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm New Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
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
$(document).ready(function() {

    $("#Changepassword_Form").validate({
        rules: {

            password: {
                required: true,
                maxlength: 12,
                minlength: 6,
                normalizer: function(value) {
                    // Trim the value of the `field` element before
                    // validating. this trims only the value passed
                    // to the attached validators, not the value of
                    // the element itself.
                    return $.trim(value);
                },


            },
            password_confirmation: {
                required: true,
                maxlength: 12,
                minlength: 6,
                equalTo: "#password",
                normalizer: function(value) {
                    // Trim the value of the `field` element before
                    // validating. this trims only the value passed
                    // to the attached validators, not the value of
                    // the element itself.
                    return $.trim(value);
                },


            },
            long_description: {
                required: true,
            },

        },
        messages: {
            password: {
                required: "Password is required",
                maxlength: jQuery.validator.format("Password too long more than (12) characters"),
                minlength: jQuery.validator.format("At least {0} characters required!"),
            },
            password_confirmation: {
                required: "Confirm Password is required",
                maxlength: jQuery.validator.format("Confirm Password too long more than (12) characters"),
                minlength: jQuery.validator.format("At least {0} characters required!"),
            }



        }
    });
});
</script>

<script>
$('body').on('click', '.editPassword', function () {
      var id = $(this).data('id');
      $.get("{{ route('admin.changepassword.edit') }}" +'/' + id , function (data) {
        $('#offcanvasRightLabel').html("Change Password");
        $('#id').val(data.id);

      })

   });
</script>
@endpush