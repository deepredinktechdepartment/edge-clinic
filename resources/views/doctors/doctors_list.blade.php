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
                    <!-- <td width="5%">{{ Config::get('constants.SNO') }}</td> -->
                    <td scope="col">Photo</td>
                    <td scope="col">Doctor Details</td>
                    <td scope="col">Department</td>
                    <!-- <td scope="col">Expertise</td> -->
                    <!-- <td scope="col">Awards</td> -->
                    <td scope="col">Slots</td>
                    <!-- <td scope="col">Status</td> -->
                    <td scope="col">Action</td>
                </tr>
            </thead>
            <tbody>
            	@foreach($doctors_data as $doctor)
                <tr>
                    <!-- <td>{{$loop->iteration??''}}</td> -->
                    <td><img src="{{URL::to('public/uploads/doctors/'.$doctor->photo??'')}}" class="img-fluid" width="50px" /></td>
                 <td>
    @if(!empty($doctor->name))
        <b>Name: </b>{{ Str::title($doctor->name ?? '') }}<br>
    @endif

    @if(!empty($doctor->designation))
        <b>Designation: </b>{{ nl2br(e($doctor->designation ??'')) }}<br>
    @endif

@if(!empty($doctor->qualification))
    <b>Qualification: </b><br>
    {!! nl2br(e($doctor->qualification)) !!}<br>
@endif

    @if(!empty($doctor->experience))
        <b>Experience: </b>{{ nl2br(e(Str::title($doctor->experience))) }}
    @endif

@if(isset($doctor->online_payment) && $doctor->online_payment)
    <b>Online Payment:</b>
    {{ $doctor->online_payment ? 'Accepted' : '' }}
@endif
</td>

                    <td>{{Str::title($doctor->dept_name??'')}}</td>
                    <!-- <td>{{Str::title($doctor->expertise??'')}}</td> -->
                    <!-- <td>{{Str::title($doctor->awards??'')}}</td> -->
                    <td>

@php
    $slots = json_decode($doctor->slots, true);
@endphp

@if(!empty($slots) && is_array($slots))
    @foreach ($slots as $slot)
        <b>Days:</b>
        {{ !empty($slot['days']) ? implode(', ', $slot['days']) : 'N/A' }}
        <br>

        <b>Session:</b>
        {{ !empty($slot['session']) ? implode(', ', $slot['session']) : 'N/A' }}
        <br>

        <b>Timing:</b>
        {{ $slot['start_time'] ?? 'N/A' }} - {{ $slot['end_time'] ?? 'N/A' }}
        <br><br>
    @endforeach
@else

@endif

                    </td>
                    <!-- <td>
                        @if($doctor->is_active==1)
                        <span class="badge bg-success">Active</span>
                        @else

                        @endif
                </td> -->
                    <td>
                        <a href="#offcanvasRight" data-id="{{$doctor->id??''}}" data-bs-toggle="offcanvas" role="button" aria-controls="offcanvasRight" class="editPost"><i class="fa-solid fa-pen-to-square"></i></a>
                        &nbsp;&nbsp;
                        <a href="{{ route ('admin.doctor.delete',["ID"=>Crypt::encryptString($doctor->id)] ) }}" title="Delete" onclick="return confirm('Are you sure to delete this?')"><i class="fa-solid fa-trash-can"></i></a>
                 &nbsp;&nbsp;
    <!-- Appointments Link -->
    <a href="{{ route('admin.appointments.report') }}?doctor={{ $doctor->id }}&from_date=&to_date=" title="View Appointments">
        Appointments
    </a>
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
            <h5 class="offcanvas-title" id="offcanvasRightLabel">Add Doctor</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
           <form action="{{route('admin.doctor.store')}}" method="post" id="Doctor_Form" enctype="multipart/form-data">
            <input type="hidden" name="doctor_id" value="" id="id">
           	@csrf
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
           				<input readonly type="text" name="slug" class="form-control slugForName" id="slug">
           			</div>
           		</div>
           	</div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group" id="selection">
                        <label>Department<span class="imp_str">*</span></label>
                        <select class="form-control" name="department_id" id="department_id">
                            <option value="">-- Select --</option>
                            @foreach($departments_data as $department)
                            <option value="{{$department->id??''}}">{{$department->dept_name??''}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Designation<span class="imp_str">*</span></label>
                        <input type="text" name="designation" class="form-control" id="designation">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Educational Qualification<span class="imp_str">*</span></label>
                        <textarea class="form-control" name="qualification" rows="2" id="qualification"></textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Experience<span class="imp_str">*</span></label>
                        <textarea class="form-control" name="experience" rows="2" id="experience"></textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Photo<span class="imp_str">*</span>&nbsp;&nbsp;<a href="#" data-bs-toggle="tooltip" data-bs-title="Upload PNG, JPG"><i class="fa-solid fa-circle-info"></i></a></label>
                        <input type="file" class="file-input form-control" name="profile_pic">
                        <img src="" class="img-fluid pt-1" width="50px" id="profile_pic">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                       <!--  <label>Expertise</label>
                        <textarea class="form-control" name="expertise" rows="2" id="expertise"></textarea> -->
                    </div>
                </div>
            </div>
           	<div class="row">
           		 <div class="form-group">
           			<label>Bio</label>
                    <textarea name="bio" id="bio"></textarea>
           		</div>
                <!--
           		<div class="form-group">
                    <label>Educational Qualifications</label>
                    <textarea name="educational_qualification" id="educational_qualification"></textarea>
                </div>
                <div class="form-group">
                    <label>Expertise</label>
                    <textarea name="expertise" id="expertise"></textarea>
                </div>
                <div class="form-group">
                    <label>Awards</label>
                    <textarea name="awards" id="awards"></textarea>
                </div> -->

           	</div>

               <!-- <div class="row">
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
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Sort Order<span class="imp_str">*</span></label>
                        <input type="number" min="1" name="sort_order" class="form-control" id="sort_order" value="1">
                    </div>
                </div>
                <div>
                </div>
            </div> -->


           <div class="mb-3">
    <h6>Doctor Availability</h6>

    <div id="slot-wrapper">

        <!-- Slot Row Template -->
        <div class="slot-row border p-3 mb-3">

            <div class="row">
                <!-- Days -->
                <div class="col-md-6">
                    <label><b>Days</b></label><br>
                    @php
                        $days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
                    @endphp

                    @foreach($days as $day)
                        <label class="me-2">
                            <input type="checkbox" name="slots[0][days][]" value="{{ $day }}"> {{ $day }}
                        </label>
                    @endforeach
                </div>

                <!-- Session -->
                <div class="col-md-6">
                    <label><b>Session</b></label><br>

                    @php
                        $sessions = ['Morning','Afternoon','Evening','Full Day'];
                    @endphp

                    @foreach($sessions as $session)
                        <label class="me-2">
                            <input type="checkbox" name="slots[0][session][]" value="{{ $session }}"> {{ $session }}
                        </label>
                    @endforeach
                </div>

                <!-- Timing -->
                <div class="col-md-4 mt-3">
                    <label><b>Timing</b></label>
                    <div class="d-flex gap-2">
                        <input type="time" name="slots[0][start_time]" class="form-control">
                        <input type="time" name="slots[0][end_time]" class="form-control">
                    </div>
                </div>
            </div>

        </div>

    </div>

    <button type="button" id="add-slot" class="btn btn-sm btn-primary">Add More Slots</button>
</div>

           	<div>
 <div class="mb-3">
    <label class="form-label">Online Payment Acceptance?</label><br>

    <label class="switch">
        <input type="checkbox" name="online_payment" value="1">
        <span class="slider round"></span>
    </label>
</div>



           		<button type="submit" class="btn btn-brand btn-wide btn-sm">Save</button>
           	</div>
           </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
        CKEDITOR.replace( 'bio' );
        CKEDITOR.replace( 'expertise' );
        CKEDITOR.replace( 'educational_qualification' );
        CKEDITOR.replace( 'awards' );
</script>

<script>
$(document).ready(function() {

    $("#Doctor_Form").validate({
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
            designation: {
                required: true,
            },

        },
        messages: {
            dept_name: {
                required: "Doctor Name is required",
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

    $.get("{{ route('admin.doctor.edit') }}/" + id, function (data) {

        // Basic Fields
        $('#offcanvasRightLabel').html("Edit Doctor");
        $('#id').val(data.id);
        $('#name').val(data.name);
        $('#slug').val(data.slug);
        $('#designation').val(data.designation);
        $('#qualification').val(data.qualification);
        $('#experience').val(data.experience);
        $('#sort_order').val(data.sort_order);

        // Online Payment
        $('input[name="online_payment"]').prop('checked', data.online_payment == 1);

        // Active
        $('input[name="is_active"][value="' + data.is_active + '"]').prop('checked', true);

        // Department
        $('#department_id option[value="' + data.department_id + '"]').prop('selected', true);

        // Photo
        if (data.photo) {
            $('#profile_pic').attr('src', "{{ URL::to('public/uploads/doctors') }}/" + data.photo).show();
        } else {
            $('#profile_pic').hide();
        }

        // ======================
        // LOAD SLOTS
        // ======================
        $('#slot-wrapper').html('');

        let slots = [];
        try {
            slots = JSON.parse(data.slots ?? '[]');
        } catch(e) {
            slots = [];
        }

        if (Array.isArray(slots) && slots.length > 0) {

            slots.forEach((slot, index) => {

                let html = `
                <div class="slot-row border p-3 mb-3">

                    <div class="row">

                        <!-- Days -->
                        <div class="col-md-4">
                            <label><b>Days</b></label><br>
                            ${['Mon','Tue','Wed','Thu','Fri','Sat','Sun'].map(day => `
                                <label class="me-2">
                                    <input type="checkbox"
                                        name="slots[${index}][days][]"
                                        value="${day}"
                                        ${(slot.days ?? []).includes(day) ? 'checked' : ''}>
                                    ${day}
                                </label>
                            `).join('')}
                        </div>

                        <!-- Session -->
                        <div class="col-md-4">
                            <label><b>Session</b></label><br>
                            ${['Morning','Afternoon','Evening','Full Day'].map(s => `
                                <label class="me-2">
                                    <input type="checkbox"
                                        name="slots[${index}][session][]"
                                        value="${s}"
                                        ${(slot.session ?? []).includes(s) ? 'checked' : ''}>
                                    ${s}
                                </label>
                            `).join('')}
                        </div>

                        <!-- Timing -->
                        <div class="col-md-4">
                            <label><b>Timing</b></label>
                            <div class="d-flex gap-2 mt-1">
                                <input type="time"
                                    name="slots[${index}][start_time]"
                                    class="form-control"
                                    value="${slot.start_time ?? ''}">

                                <input type="time"
                                    name="slots[${index}][end_time]"
                                    class="form-control"
                                    value="${slot.end_time ?? ''}">
                            </div>
                        </div>

                    </div>

                </div>
                `;

                $('#slot-wrapper').append(html);
            });
        }

        // CKEditor loading
        CKEDITOR.instances['bio'].setData(data.bio);
        CKEDITOR.instances['expertise'].setData(data.expertise);
        CKEDITOR.instances['educational_qualification'].setData(data.educational_qualification);
        CKEDITOR.instances['awards'].setData(data.awards);

    });
});

</script>
<script>
let slotIndex = 1;

document.getElementById('add-slot').addEventListener('click', function () {

    let slotHTML = `
        <div class="slot-row border p-3 mb-3">

            <div class="row">
                <div class="col-md-4">
                    <label><b>Days</b></label><br>
                    @foreach($days as $day)
                        <label class="me-2">
                            <input type="checkbox" name="slots[\${slotIndex}][days][]" value="{{ $day }}"> {{ $day }}
                        </label>
                    @endforeach
                </div>

                <div class="col-md-4">
                    <label><b>Session</b></label><br>
                    @foreach($sessions as $session)
                        <label class="me-2">
                            <input type="checkbox" name="slots[\${slotIndex}][session][]" value="{{ $session }}"> {{ $session }}
                        </label>
                    @endforeach
                </div>

                <div class="col-md-4">
                    <label><b>Timing</b></label>
                    <div class="d-flex gap-2">
                        <input type="time" name="slots[\${slotIndex}][start_time]" class="form-control">
                        <input type="time" name="slots[\${slotIndex}][end_time]" class="form-control">
                    </div>
                </div>
            </div>

        </div>
    `;

    document.getElementById('slot-wrapper').insertAdjacentHTML('beforeend', slotHTML);
    slotIndex++;
});
</script>


@endpush