@extends('template_v1')

@section('content')



<div class="tt-posts">
   	<div class="d-flex justify-content-between tt-wrap mb-3">
	  	<div class="p-2 bd-highlight"><h5 class="mb-0 pb-0">{{ $pageTitle??'' }}</h5></div>
	  	<div class="p-2 bd-highlight">
	  		@if(isset($addlink) && !empty($addlink))
	  		<a href="#offcanvasRight" data-bs-toggle="offcanvas" role="button" aria-controls="offcanvasRight" class="addFaqPost"><i class="fa-solid fa-circle-plus"></i></a>
	  		@else
			@endif
	  	</div>
	</div>
</div>


<div class="row mb-4">
    <div class="col-md-5">
        <label>Departments<span class="imp_str">*</span></label>
                <form class="find-doctor" method="post">
            <div class="row">
                <div class="col-sm-7 col-10">

                    <select class="form-control" name="department_id" id="department_id" required>
                        <option value="">-- All --</option>
                        @foreach($departments_data as $department)
                        <option value="{{$department->id??''}}">{{Str::title($department->dept_name??'')}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-1 ">
                    <button type="button" id="FindFaqs" class="btn btn-danger btn-sm">Go</button>
                </div>
            </div>
        </form>
    </div>
   </div>




<div class="t-job-sheet container-fluid g-0" id="SearchFAQs">
    @include('departments.faqs.renderintable')
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight"
        aria-labelledby="offcanvasRightLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasRightLabel">Add FAQ</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
           <form action="{{route('admin.faq.store')}}" method="post" id="Faq_Form" enctype="multipart/form-data">
            <input type="hidden" name="department_faq_id" value="" id="id">
           	@csrf
           	<div class="row">
                <div class="col-md-12">
                    <div class="form-group" id="selection">
                        <label>Department<span class="imp_str">*</span></label>
                        <select class="form-control" name="department_id" id="department_id">
                            <option value="">-- Pick one --</option>
                            @foreach($departments_data as $department)
                            <option value="{{$department->id??''}}">{{Str::title($department->dept_name??'')}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>


           	</div>

               <div class="form-group">
                <label>Question?<span class="imp_str">*</span></label>
                <input type="text" name="faq_question" class="form-control nameForSlug" id="faq_question">
            </div>
            <div class="form-group">
                <label>Answer<span class="imp_str">*</span></label>
                <textarea name="faq_answer" id="faq_answer" rows=2></textarea>
            </div>

           	<div class="row">
           		<div class="col-md-6">
           			<label>Status<span class="imp_str">*</span></label>
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
        CKEDITOR.replace( 'faq_answer' );
</script>

<script>
$(document).ready(function() {

    $("#Faq_Form").validate({
        rules: {

            faq_question: {
                required: true,
                maxlength: 250,
                minlength: 2,
                normalizer: function(value) {
                    // Trim the value of the `field` element before
                    // validating. this trims only the value passed
                    // to the attached validators, not the value of
                    // the element itself.
                    return $.trim(value);
                },

            },
            department_id: {
                required: true,
            },
            faq_answer: {
                required: true,
            },

        },
        messages: {
            faq_question: {
                required: "Question is required",
                maxlength: jQuery.validator.format("Question too long more than (250) characters"),
                minlength: jQuery.validator.format("At least {0} characters required!"),
            },
            department_id: {
                required: "Department is required",
            },
            faq_answer: {
                required: "Answer is required",
            },
        },

		 submitHandler: function(form) {
            // Your form submission logic here
			var editor = CKEDITOR.instances['faq_answer'];
			var faqValue = editor.getData();

            if (faqValue === '') {
                // Show an alert if the FAQ Answer field is empty
                alert("FAQ Answer field is required.");
				return false;
                // Alternatively, you can display an alert using some alert UI library
            } else {
                // If FAQ Answer is not empty, proceed with form submission
                form.submit();
            }

        }


    });
});
</script>

<script>
$('body').on('click', '.editFaqPost', function () {
      var id = $(this).data('id');
      $.get("{{ route('admin.faq.edit') }}" +'/' + id , function (data) {
        $('#offcanvasRightLabel').html("Edit FAQ");
        $('#id').val(data.id);
        $('#department_id option[value="' + data.department_id + '"]').prop('selected', true);
        $('#faq_question').val(data.faq_question);
        $('input[name="is_active"]').prop('checked', false); // Uncheck all radio buttons
        $('input[name="is_active"][value="' + data.is_active + '"]').prop('checked', true); // Check the selected radio button
        var editorValue = data.faq_answer;
        var editor = CKEDITOR.instances['faq_answer'];
        editor.setData(data.faq_answer);
      })

   });


   $('body').on('click', '.addFaqPost', function () {
    $('#offcanvasRightLabel').html("Add FAQ");
    var editorValue = "";
    var editor = CKEDITOR.instances['faq_answer'];
    editor.setData(editorValue);
    document.getElementById("Faq_Form").reset();
    return false;

});
</script>


<script type="text/javascript">
    $( document ).ready(function() {

        $('#FindFaqs').on('click',function() {
             var query = $("#department_id").val();
            $.ajax({
                url:"{{ route('admin.filter.faqs') }}",
                type:"GET",
                data:{'query':query},
                success:function (data) {

                    $('#SearchFAQs').html(data);
                }
            })
        });

        });

    </script>


@endpush