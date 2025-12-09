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
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
    <div class="col-md-5">
        <label>Doctors<span class="imp_str">*</span></label>
                <form class="find-doctor" method="post">
            <div class="row">
                <div class="col-sm-7 col-10">

                    <select class="form-control" name="doctor_id" id="doctor_id" required>
                        <option value="">-- All --</option>
                        @foreach($doctors_data as $doctor)
                        <option value="{{$doctor->id??''}}">{{Str::title($doctor->name??'')}}</option>
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
    </div>

</div>
<div class="t-job-sheet container-fluid g-0" id="SearchFAQs">
@include('doctors.renderintable') 
</div>
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight"
        aria-labelledby="offcanvasRightLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasRightLabel">Add Doctor Video</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
           <form action="{{route('admin.doctor-videos.store')}}" method="post" id="Doctor_Form" enctype="multipart/form-data" autocomplete="off">
            <input type="hidden" name="doctor_video_id" value="" id="id">
           	@csrf
           	
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group" id="selection">
                        <label>Doctors<span class="imp_str">*</span></label>
                        <select class="form-control" name="doctor_id" id="doctor_id">
                            <option value="">-- Select --</option>
                            @foreach($doctors_data as $doctor)
                            <option value="{{$doctor->id??''}}">{{$doctor->name??''}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>youtube URL<span class="imp_str">*</span></label>
                        <input type="url" name="youtube_url" class="form-control" id="youtube_url">

                    </div>
                </div>
                <div class="col-md-12">
                    <label>Description<span class="imp_str">*</span></label>
                    <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                </div>
            </div>
           	<div class="mt-3">
           		<button type="submit" class="btn btn-brand btn-wide btn-sm">Save</button>
           	</div>
           </form>
        </div>
    </div>
@endsection

@push('scripts')

<script>
$(document).ready(function() {

    $("#Doctor_Form").validate({
        rules: {

            doctor_id: {
                required: true,

            },
            youtube_url: {
                required: true,
            },
            description: {
                required: true,
            },

        },
        messages: {
            doctor_id: {
                required: "Please select doctor",
                maxlength: jQuery.validator.format("Department too long more than (30) characters"),
                minlength: jQuery.validator.format("At least {0} characters required!"),
            },
            youtube_url: {
                required: "Youtube URL is required",
            },




        }
    });
});
</script>

<script>

$('body').on('click', '.editPost', function () {
      var id = $(this).data('id');

      $.get("{{ route('admin.doctor-videos.edit') }}" +'/' + id , function (data) {
          $('#offcanvasRightLabel').html("Edit Doctor Video");
          $('#id').val(data.id);
          $('#description').val(data.description);
          $('#youtube_url').val(data.youtube_url);
        var selectedOption = data.doctor_id;
        // Find the option that matches the selected value and mark it as selected
        $('#doctor_id option[value="' + selectedOption + '"]').prop('selected', true);
       

      })
   });
</script>

<script type="text/javascript">
    $( document ).ready(function() {

        $('#FindFaqs').on('click',function() {
             var query = $("#doctor_id").val();
            $.ajax({
                url:"{{ route('admin.filter.doctor.videos') }}",
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