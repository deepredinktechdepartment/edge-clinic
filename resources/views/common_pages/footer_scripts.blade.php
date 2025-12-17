<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.2.3/js/bootstrap.bundle.min.js"></script>
    <script src="{{URL::to('assets/js/app.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/additional-methods.min.js"></script>
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script src="https://unpkg.com/@grammarly/editor-sdk@2.3.11?clientId=client_9m1fYK3MPQxwKsib5CxtpB"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- intl-tel-input JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.1.1/js/intlTelInput.min.js"></script>
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
