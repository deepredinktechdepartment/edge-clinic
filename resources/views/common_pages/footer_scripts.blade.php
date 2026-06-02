<!-- jQuery FIRST -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>

<!-- jQuery Validate -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/additional-methods.min.js"></script>

<!-- intl-tel-input -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.1.1/js/intlTelInput.min.js"></script>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.2.3/js/bootstrap.bundle.min.js"></script>

<!-- Rest -->
<script src="{{ URL::to('assets/js/app.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Include DataTables JS -->
 <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

 <!-- Include DataTables Buttons JS -->
 <script src="https://cdn.datatables.net/buttons/3.0.1/js/dataTables.buttons.js"></script>
 <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

<style>
.password-toggle-wrap {
  position: relative;
}
.password-toggle-input {
  padding-right: 42px;
}
.password-toggle-btn {
  position: absolute;
  top: 50%;
  right: 12px;
  transform: translateY(-50%);
  border: 0;
  background: transparent;
  color: #6c757d;
  padding: 0;
  line-height: 1;
}
</style>


    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>
    <script type="text/javascript">
function convertToSlug(Text) {
  return Text
    .toLowerCase()
    .replace(/[^\w ]+/g,'')
    .replace(/ +/g,'-');
}

function slugClean(Text) {
  return Text
    .toLowerCase()
    .replace(/[^\w -]+/g,'')
    .replace(/ +/g,'-')
    .replace(/-+/g,'-');
}

function codeClean(Text) {
  return Text
    .replace(/[^\w -]+/g,'')
    .replace(/ +/g,'-')
    .replace(/-+/g,'-');
}

$('.nameForSlug').on('keyup change', function() {
  $('.slugForName').val(convertToSlug($(this).val()));
});

$('.slugForName').on('change keyup', function() {
  $('.slugForName').val(slugClean($(this).val()));
});

$('.codeClean').on('change keyup', function() {
  $('.codeClean').val(codeClean($(this).val()));
});


</script>
    <script>

   $(function() {
    console.log( "ready!" );
$(".accordion-body ul li").each(function() {

       if ( $(this).hasClass('active') ) {
          $(this).closest('.accordion-item').find('.accordion-header .accordion-button').removeClass('collapsed');
          $(this).closest('.accordion-item').find('.accordion-collapse').addClass('show');

       }
    });

   })
 </script>


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
          $('#offcanvasRightLabel_Changepwd').html("Change Password");
          $('#id').val(data.id);

        })

     });
  </script>

  <script>
  $(document).on('click', '.password-toggle-btn', function () {
      const target = $(this).siblings('.password-toggle-input').first();
      const icon = $(this).find('i');
      const isPassword = target.attr('type') === 'password';

      target.attr('type', isPassword ? 'text' : 'password');
      icon.toggleClass('fa-eye fa-eye-slash');
      $(this).attr('aria-label', isPassword ? 'Hide password' : 'Show password');
  });
  </script>

  {{-- Change password layout code --}}

  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight-chgpwd"  aria-labelledby="offcanvasRightLabel_Changepwd">
  <div class="offcanvas-header">
      <h5 class="offcanvas-title" id="offcanvasRightLabel_Changepwd">Change Password</h5>
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
          <div class="password-toggle-wrap">
              <input type="password" name="password" id="password" class="form-control password-toggle-input">
              <button type="button" class="password-toggle-btn" data-target="#password" aria-label="Show password">
                  <i class="fa-solid fa-eye"></i>
              </button>
          </div>
          @error('password')
              <div class="alert alert-danger">{{ $message }}</div>
          @enderror
      </div>

      <div class="form-group">
          <label for="password_confirmation">Confirm New Password</label>
          <div class="password-toggle-wrap">
              <input type="password" name="password_confirmation" id="password_confirmation" class="form-control password-toggle-input">
              <button type="button" class="password-toggle-btn" data-target="#password_confirmation" aria-label="Show password">
                  <i class="fa-solid fa-eye"></i>
              </button>
          </div>
      </div>



       <div>
         <button type="submit" class="btn btn-brand btn-wide btn-sm">Save</button>
       </div>
     </form>
  </div>
</div>
