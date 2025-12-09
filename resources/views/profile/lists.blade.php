@extends('template_v1')

@section('content')



<div class="tt-posts">
   	<div class="d-flex justify-content-between tt-wrap bg-white mb-3">
	  	<div class="p-2 bd-highlight"><h5 class="mb-0 pb-0">{{ $pageTitle??'' }}</h5></div>

	</div>
</div>


<div class="t-job-sheet container-fluid g-0">
  <div class="row">
    <div class="col-md-4">
      <div class="card text-center">
    <div class="card-body">
      @if(!empty($User->profile_picture))
        <img src="{{URL::to('public/uploads/users/'.$User->profile_picture)}}" alt="user" class="img-fluid mb-1 mt-2" width="100px" style="border-radius: 50%;" />
        @else
        <img src="https://placekitten.com/320/320" alt="user" class="img-fluid mb-1"  width="100px" style="border-radius: 50%;">
        @endif
        <h5 class="mt-2">{{ $User->name??'' }}</h5>
        <p class="mb-0"><i class="fa-solid fa-envelope"></i> {{ $User->email??'' }}</p>
        <p><i class="fa-solid fa-phone"></i> {{ $User->phone??'' }}</p>

    </div>
    <div class="card-footer float-right">
        <div style="float:right;">
            <a href="#offcanvasRight" data-id="{{$User->id??''}}" data-bs-toggle="offcanvas" role="button" aria-controls="offcanvasRight" class="editProfile"><i class="fa-solid fa-pen-to-square"></i></a>
        </div>

    </div>
  </div>
    </div>
  </div>

</div>




  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight"
  aria-labelledby="offcanvasRightLabel">
  <div class="offcanvas-header">
      <h5 class="offcanvas-title" id="offcanvasRightLabel">Edit Profile</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
     <form action="{{route('admin.profile.store')}}" method="post" id="User_Form" enctype="multipart/form-data">
      <input type="hidden" name="user_id" value="" id="id">
      @csrf
      <div class="row">
          <div class="col-md-6">
              <div class="form-group">
                  <label>Name<span class="imp_str">*</span></label>
                  <input type="text" name="name" class="form-control" id="name">
              </div>
          </div>

          <div class="col-md-6">
              <div class="form-group">
                  <label>Email<span class="imp_str">*</span></label>
                  <input readonly type="email" name="email" id="email" class="form-control" value="" autocomplete="email" required>
                </div>
          </div>
          <div class="col-md-6">
              <div class="form-group">
                  <label>Mobile<span class="imp_str">*</span></label>
                  <input type="text" name="phone" id="phone" class="form-control" value="">
            </div>
          </div>


          <div class="col-md-6">
              <div class="form-group">
                  <label>Photo<span class="imp_str">*</span>&nbsp;&nbsp;<a href="#" data-bs-toggle="tooltip" data-bs-title="Upload PNG, JPG"><i class="fa-solid fa-circle-info"></i></a></label>
                  <input type="file" class="file-input form-control" name="profile_picture">
                  <img src="" class="img-fluid pt-1" width="100px" id="profile_picture" >
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
<script>
$(document).ready(function() {

$("#User_Form").validate({
  rules: {

      name: {
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
      phone: {
          required: true,
          number:true,
          maxlength: 10,
          minlength: 10,
          normalizer: function(value) {
              // Trim the value of the `field` element before
              // validating. this trims only the value passed
              // to the attached validators, not the value of
              // the element itself.
              return $.trim(value);
          },


      },

  profile_picture: {
        extension:'jpe?g,png',
        },

  },
  messages: {
      name: {
          required: "Name is required",
          maxlength: jQuery.validator.format("Name too long more than (30) characters"),
          minlength: jQuery.validator.format("At least {0} characters required!"),
      },
      phone: {
          required: "Enter 10 digits mobile number",
          maxlength: jQuery.validator.format("Phone too long more than (10) characters"),
          minlength: jQuery.validator.format("At least {0} characters required!"),
      },

      profile_picture: {
          required: "Upload photo",
          extension: "Upload Only JPG|JPEG|PNG",
      },




  }
});
});
</script>
<script>

$('body').on('click', '.editProfile', function () {
var id = $(this).data('id');

$.get("{{ route('admin.profile.edit') }}" +'/' + id , function (data) {
    $('#offcanvasRightLabel').html("Edit Profile");
    $('#id').val(data.id);
    $('#name').val(data.name);
    $('#email').val(data.email);
    $('#phone').val(data.phone);
    $('#password_label').hide();
    $('#password_confirm_label').hide();




  if (data.profile_picture !== null) {
var deptPictureUrl = "{{URL::to('public/uploads/users/')}}" + '/' + data.profile_picture;
$('#profile_picture').attr('src', deptPictureUrl).show();
} else {
$('#profile_picture').hide();
}

})
});

</script>

@endpush