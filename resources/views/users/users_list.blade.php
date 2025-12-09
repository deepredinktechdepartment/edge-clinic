@extends('template_v1')

@section('content')
<div class="tt-posts">
    <div class="d-flex justify-content-between tt-wrap bg-white mb-3">
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
                    <td scope="col">{{ Config::get('constants.SNO') }}</td>
                    <td scope="col">Name</td>
                    <td scope="col">Email/Username</td>
                    <td scope="col">Phone</td>
                    <td scope="col">Role</td>
                    <td scope="col">Action</td>
                </tr>
            </thead>
            <tbody>

               @foreach ($users_data as $user)
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ Str::title($user->name ?? '') }}</td>
        <td>{{ $user->email ?? '' }}</td>
        <td>{{ $user->phone ?? '' }}</td>
        <td>{{ $user->ut_name ?? '' }}</td>

        <td>
            @if($user->role != 1)
                <a href="#offcanvasRight" 
                   data-id="{{ $user->id }}" 
                   data-bs-toggle="offcanvas" 
                   role="button" 
                   aria-controls="offcanvasRight" 
                   class="editPost">
                    <i class="fa-solid fa-pen-to-square"></i>
                </a>

                &nbsp;&nbsp;

                <a href="{{ route('admin.user.delete', ['ID' => Crypt::encryptString($user->id)]) }}"
                   title="Delete"
                   onclick="return confirm('Are you sure to delete this?')">
                    <i class="fa-solid fa-trash-can"></i>
                </a>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
    </tr>
@endforeach

            </tbody>
        </table>
    </div>
</div>
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight"
        aria-labelledby="offcanvasRightLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasRightLabel">Add User</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
           <form action="{{route('admin.user.store')}}" method="post" id="User_Form" enctype="multipart/form-data">
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
                    <label>Role<span class="imp_str">*</span></label>
                    <select class="form-control" name="role" id="role" required>
                      <option value="">-- Select --</option>
                      @foreach($user_type_data as $usertype)

                        <option value="{{$usertype->id??''}}">{{ucwords($usertype->name??'')}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Email<span class="imp_str">*</span></label>
                        <input type="email" name="email" id="email" class="form-control" value="" autocomplete="email" required>
                      </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Mobile<span class="imp_str">*</span></label>
                        <input type="text" name="phone" id="phone" class="form-control" value="">
                  </div>
                </div>
                <div class="col-md-6" id="password_label">
                    <div class="form-group">
                        <label>Password<span class="imp_str">*</span></label>
                        <input name="password" type="password" class="form-control" id="password" autocomplete="new-password">
                      </div>
                </div>
                <div class="col-md-6" id="password_confirm_label">
                    <div class="form-group">
                        <label>Confirm Password<span class="imp_str">*</span></label>
                        <input name="password_confirm" type="password" class="form-control" id="password_confirm">
                      </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Profile Picture<span class="imp_str">*</span>&nbsp;&nbsp;<a href="#" data-bs-toggle="tooltip" data-bs-title="Upload PNG, JPG"><i class="fa-solid fa-circle-info"></i></a></label>
                        <input type="file" class="file-input form-control" name="profile_picture">
                        <img src="" class="img-fluid pt-1" width="100px" id="profile_picture" >
                    </div>
                </div>
                <div class="col-md-6">
                    <label>Is active?<span class="imp_str">*</span></label>
                    <div class="form-group mb-3 d-flex align-items-center gap-3">
                        <div class="form-check d-flex align-items-center gap-2">
                            <input type="radio" class="form-check-input mb-1" name="is_active" value="1" checked>
                            <label class="form-check-label">Yes</label>
                        </div>

                        <div class="form-check  d-flex align-items-center gap-2">
                            <input type="radio" class="form-check-input mb-1" name="is_active" value="0"
                                required="required"/>
                            <label class="form-check-label">No</label>
                        </div>
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
            role: {
                required: true,
            },
            password: {
            required:false,
            minlength: 5,
        },
        password_confirm: {
            minlength: 5,
            equalTo: "#password"
        },
        profile_picture: {
              extension:'jpe?g,png',
              },

        },
        messages: {
            name: {
                required: "Name is required",
                maxlength: jQuery.validator.format("Department too long more than (30) characters"),
                minlength: jQuery.validator.format("At least {0} characters required!"),
            },
            role: {
                required: "Role is required",
            },
            profile_picture: {
                required: "Upload profile picture",
                extension: "Upload Only JPG|JPEG|PNG",
            },




        }
    });
});
</script>
<script>

$('body').on('click', '.editPost', function () {
      var id = $(this).data('id');

      $.get("{{ route('admin.user.edit') }}" +'/' + id , function (data) {
          $('#offcanvasRightLabel').html("Edit User");
          $('#id').val(data.id);
          $('#name').val(data.name);
          $('#email').val(data.email);
          $('#phone').val(data.phone);
          $('#password_label').hide();
          $('#password_confirm_label').hide();
        var selectedOption = data.role;
        // Find the option that matches the selected value and mark it as selected
        $('#role option[value="' + selectedOption + '"]').prop('selected', true);

        var selectedOption = data.is_active;
          $('input[name="is_active"]').prop('checked', false); // Uncheck all radio buttons
      $('input[name="is_active"][value="' + selectedOption + '"]').prop('checked', true); // Check the selected radio button


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