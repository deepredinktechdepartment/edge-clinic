@extends('template_v1')

@section('content')

<div class="tt-posts">
   	<div class="d-flex justify-content-between tt-wrap mb-3">
	  	<div class="p-2 bd-highlight">
            <h5 class="mb-0 pb-0">{{$pageTitle??''}}</h5>
        </div>
	  	<div class="p-2 bd-highlight">
	  		@if(isset($addlink) && !empty($addlink))
	  		<a href="#offcanvasRight" data-bs-toggle="offcanvas" role="button" aria-controls="offcanvasRight">
                <i class="fa-solid fa-circle-plus"></i>
            </a>
	  		@endif
	  	</div>
	</div>
</div>

<div class="t-job-sheet container-fluid g-0">
    <div class="t-table table-responsive">
        <table class="table table-borderless table-hover" id="default-datatable" style="width: 100%;">
            <thead>
                <tr>
                    <td>Photo</td>
                    <td>Doctor Details</td>
                    <td>Speciality</td>
                    <td>Slots</td>
                    <td>Action</td>
                </tr>
            </thead>
            <tbody>
            	@foreach($doctors_data as $doctor)
                <tr>
                    <td>
                        <img src="{{URL::to('public/uploads/doctors/'.$doctor->photo??'')}}"
                             class="img-fluid" width="50px" />
                    </td>

                    <td>
                        <h6 class="mb-0 pb-0">{{ Str::title($doctor->name ?? '') }}</h6>                  
                        {!! nl2br(e($doctor->designation ??'')) !!}<br>
                        {!! nl2br(e($doctor->qualification ??'')) !!}<br>            
                        {!! nl2br(e(Str::title($doctor->experience ??''))) !!}
                        @if($doctor->appointment_fee)
                         Online Fee ₹{{ $doctor->appointment_fee ?? 0 }}<br>
                         @endif
                        @if($doctor->online_payment)
                            Online Payment Accepted
                        @endif
                    </td>

                    <td>{{ Str::title($doctor->dept_name ?? '') }}</td>

                   <td>
    <a href="javascript:void(0)"
       class="view-slots afontopt text-decoration-none link-underline link-underline-opacity-0 link-underline-opacity-100-hover"
       data-drid="{{ $doctor->id }}"
       data-drkey="{{ $doctor->dr_key }}"
       data-bs-toggle="modal"
       data-bs-target="#slotsModal">
        <i class="fas fa-clock me-1 text-warning"></i> View
    </a>
</td>
                    



                    <td>
                        <a href="#offcanvasRight"
                           data-id="{{$doctor->id}}"
                           data-bs-toggle="offcanvas"
                           class="editPost">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>

                        &nbsp;&nbsp;

                        <a href="{{ route('admin.doctor.delete',['ID'=>Crypt::encryptString($doctor->id)]) }}"
                           onclick="return confirm('Are you sure to delete this?')">
                            <i class="fa-solid fa-trash-can"></i>
                        </a>

                        &nbsp;&nbsp;

                        <a class="afontopt" href="{{ route('admin.appointments.report') }}?doctor={{ $doctor->id }}">
                            <i class="fas fa-calendar-check me-1"></i> Appointments
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- ================= OFFCANVAS ================= --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasRightLabel">Add Doctor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body">
        <form action="{{route('admin.doctor.store')}}"
              method="post"
              id="Doctor_Form"
              enctype="multipart/form-data">

            @csrf
            <input type="hidden" name="doctor_id" id="id">

            <div class="row">
                <div class="col-md-6">
                    <label>Name *</label>
                    <input type="text" name="name" class="form-control" id="name">
                </div>
                      {{-- ✅ Appointment Fee --}}
            <div class="col-md-6">
                <label>Appointment Fee (₹) *</label>
                <input type="number"
                       name="appointment_fee"
                       id="appointment_fee"
                       class="form-control only-number"
                       min="0"
                       step="1"
                       required>
            </div>
           
            </div>

            <div class="row mt-2">
                <div class="col-md-6">
                    <label>Speciality*</label>
                    <select class="form-select" name="department_id" id="department_id">
                        <option value="">-- Select --</option>
                        @foreach($departments_data as $department)
                            <option value="{{$department->id}}">
                                {{$department->dept_name}}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Designation *</label>
                    <input type="text" name="designation" class="form-control" id="designation">
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-6">
                    <label>Educational Qualification *</label>
                    <textarea class="form-control" name="qualification" id="qualification"></textarea>
                </div>
                <div class="col-md-6">
                    <label>Experience *</label>
                    <textarea class="form-control" name="experience" id="experience"></textarea>
                </div>
            </div>

      

            <div class="mt-2">
                <label>Photo</label>
                <input type="file" class="form-control" name="profile_pic">
                <img src="" class="img-fluid pt-1" width="50" id="profile_pic">
            </div>

            <div class="mt-2">
                <label>Bio</label>
                <textarea name="bio" id="bio"></textarea>
            </div>

            <div class="mt-3">
                <label>Online Payment Acceptance?</label><br>
                <label class="switch">
                    <input type="checkbox" name="online_payment" value="1">
                    <span class="slider round"></span>
                </label>
            </div>

            <button type="submit" class="btn btn-brand btn-sm mt-3">Save</button>
        </form>
    </div>
</div>
<div class="modal fade" id="slotsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h6 class="modal-title">Doctor Slots</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <input type="hidden" id="doctor_id">
                <input type="hidden" id="selectedDate">
                <input type="hidden" id="selectedTime">

                {{-- Dates & Slots --}}
                <div id="slotsSection" class="row g-3">

                    <div class="col-md-6">
                        <div class="card p-3 shadow-sm">
                            <h6 class="modal-title">Dates</h6>
                            <div id="dateContainer" class="d-flex flex-wrap gap-2"></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card p-3 shadow-sm">
                            <h6 class="modal-title">Time</h6>
                            <div id="timeContainer" class="d-flex flex-wrap gap-2"></div>
                            <div id="timeLoading" class="text-center d-none">Loading...</div>
                            <p id="noSlotsMsg" class="text-danger fw-bold d-none">No slots available</p>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>


@endsection

@push('scripts')

<script>
CKEDITOR.replace('bio');
</script>

<script>
$(document).ready(function() {
    $("#Doctor_Form").validate({
        rules: {
            name: { required: true, minlength: 2 },
            designation: { required: true },
            appointment_fee: { required: true, digits: true }
        }
    });
});
</script>

{{-- Number only --}}
<script>
$('.only-number').on('input', function () {
    this.value = this.value.replace(/[^0-9]/g, '');
});
</script>

{{-- Edit --}}
<script>
$('body').on('click', '.editPost', function () {

    let id = $(this).data('id');

    $.get("{{ route('admin.doctor.edit') }}/" + id, function (data) {

        $('#offcanvasRightLabel').html("Edit Doctor");
        $('#id').val(data.id);
        $('#name').val(data.name);
        $('#slug').val(data.slug);
        $('#designation').val(data.designation);
        $('#qualification').val(data.qualification);
        $('#experience').val(data.experience);
        $('#appointment_fee').val(data.appointment_fee);
        $('#department_id').val(data.department_id);

        $('input[name="online_payment"]').prop('checked', data.online_payment == 1);

        if (data.photo) {
            $('#profile_pic').attr('src',
                "{{ URL::to('public/uploads/doctors') }}/" + data.photo).show();
        }

        CKEDITOR.instances['bio'].setData(data.bio);
    });
});
</script>

<script>
let slotsData = {};

$('body').on('click', '.view-slots', function () {

    let doctorId = $(this).data('drid');
    let drKey    = $(this).data('drkey');

    $('#doctor_id').val(doctorId);

    // Reset UI
    $('#dateContainer, #timeContainer').html('');
    $('#selectedDate, #selectedTime').val('');
    $('#noSlotsMsg').addClass('d-none');
    $('#timeLoading').removeClass('d-none');

    $('#dateContainer').html('<div>Loading dates...</div>');

    $.get("{{ url('manualappointment/ajax-slots') }}/" + doctorId, { drKey: drKey }, function (res) {

        slotsData = res?.dates?.slots?.location1 || {};

        if (!Object.keys(slotsData).length) {
            $('#dateContainer').html('<div class="text-danger">No slots available</div>');
            $('#timeLoading').addClass('d-none');
            return;
        }

        $('#dateContainer').html('');
        let firstDate = null;

        Object.keys(slotsData).sort().forEach(dateKey => {

            let valid = slotsData[dateKey].filter(s => s !== 'weeklyoff');
            if (!valid.length) return;

            if (!firstDate) firstDate = dateKey;

            let d = new Date(
                dateKey.substr(0,4),
                dateKey.substr(4,2)-1,
                dateKey.substr(6,2)
            );

            let btn = $(`<button type="button" class="btn btn-outline-primary btn-sm">${d.toDateString()}</button>`)
                .data('date', dateKey);

            if (dateKey === firstDate) btn.addClass('active');
            $('#dateContainer').append(btn);
        });

        if (firstDate) {
            $('#selectedDate').val(firstDate);
            loadTimes(firstDate);
        }
    });
});

$(document).on('click', '#dateContainer button', function () {

    $('#dateContainer button').removeClass('active');
    $(this).addClass('active');

    let dateKey = $(this).data('date');
    $('#selectedDate').val(dateKey);
    loadTimes(dateKey);
});

function loadTimes(dateKey) {

    $('#timeContainer').html('');
    $('#timeLoading').removeClass('d-none');
    $('#noSlotsMsg').addClass('d-none');

    setTimeout(() => {

        $('#timeLoading').addClass('d-none');

        let slots = slotsData[dateKey] || [];
        let valid = slots.filter(s => s !== 'weeklyoff');

        if (!valid.length) {
            $('#noSlotsMsg').removeClass('d-none');
            return;
        }

        valid.forEach(time => {
            let btn = $(`<button type="button" class="btn btn-outline-primary btn-sm">${time}</button>`);
            $('#timeContainer').append(btn);
        });

    }, 300);
}
</script>
@endpush
