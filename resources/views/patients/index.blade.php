@extends('template_v1')

@section('content')
<div class="tt-posts">
    <div class="d-flex justify-content-between tt-wrap mb-3">
        <div class="p-2 bd-highlight"><h5 class="mb-0 pb-0">{{$pageTitle??''}}</h5></div>
        <div class="p-2 bd-highlight">
            @if(isset($addlink) && !empty($addlink))
            <a href="{{$addlink??'#'}}" ><i class="fa-solid fa-circle-plus"></i></a>
            @else
            @endif      
        </div>
    </div>
</div>


<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ url()->current() }}" class="row gy-2 gx-3 align-items-end">

            <!-- Phone Number -->
            <div class="col-md-1">
                <label class="form-label">Phone Number</label>
                <input type="text"
                       name="phone"
                       class="form-control form-control-sm"
                       placeholder="Search phone"
                       value="{{ request('phone') }}">
            </div>
            <div class="col-md-1">
    <label class="form-label">Patient Name</label>
    <input type="text"
           name="name"
           class="form-control form-control-sm"
           placeholder="Search name"
           value="{{ request('name') }}">
</div>

         <div class="col-md-3">
    <label class="form-label">Registered Date</label>
    <div class="input-group input-group-sm">
        <input type="date"
               name="from_date"
               class="form-control"
               value="{{ request('from_date') }}">

        <span class="input-group-text">to</span>

        <input type="date"
               name="to_date"
               class="form-control"
               value="{{ request('to_date') }}">
    </div>
</div>

            <!-- Buttons -->
            <div class="col-md-3 d-flex">
                <button class="btn btn-success btn-sm me-2">
                    Go
                </button>

                <a href="{{ url()->current() }}" class="btn btn-secondary btn-sm">
                    Reset
                </a>
            </div>

        </form>
    </div>
</div>


<div class="t-job-sheet container-fluid g-0">
    <div class="t-table table-responsive">
@if(!empty($patients) && $patients->isNotEmpty())
    <table class="table table-borderless table-hover table-centered align-middle table-nowrap mb-0">
 <thead>
<tr>
    <th>#</th>
    <th>Basic Info</th>
    <th>Personal Info</th>
    <th>Registered</th>
    <th>Action</th>
</tr>
</thead>




<tbody>
@foreach($patients as $patient)
<tr>
    <td>{{ $loop->iteration }}</td>

    <!-- GROUP 1 -->
<td>
    <div>
        <strong>Name:</strong> {{ $patient->name }}

        @if($patient->is_primary_account)
            <span class="badge bg-success ms-2">
                Primary
            </span>
        @endif
    </div>

    <div>
        <strong>Email:</strong> {{ $patient->email ?? '—' }}
    </div>

    <div>
        <strong>Phone:</strong>
        @if(!empty($patient->mobile) && !empty($patient->country_code))
            +{{ $patient->country_code }} {{ $patient->mobile }}
        @else
            —
        @endif
    </div>
</td>


    <!-- GROUP 2 -->
    <td>
        <div><strong>Gender:</strong> {{ $patient->gender ?? '—' }}</div>
        <div><strong>Age:</strong> {{ $patient->age ?? '—' }}</div>
        <div><strong>Booking For:</strong> {{ $patient->bookingfor ?? '—' }}</div>
    </td>

    <!-- GROUP 3 -->
    <td>
        <div><strong>Date:</strong>
            {{ \Carbon\Carbon::parse($patient->created_at)->format('d M Y') }}
        </div>
        <div><strong>Time:</strong>
            {{ \Carbon\Carbon::parse($patient->created_at)->format('h:i A') }}
        </div>
    </td>

    <!-- ACTION -->
    <td>

<a href="{{ route('patients.edit', ['ID' => Crypt::encryptString($patient->id)]) }}"
   title="Edit">
    <i class="fa-solid fa-pen-to-square text-primary"></i>
</a>
      &nbsp;&nbsp;

        <a href="javascript:void(0)"
   class="deletePatient"
   data-url="{{ route('patients.delete', ['ID' => Crypt::encryptString($patient->id)]) }}"
   data-redirect="{{ url()->full() }}"
   title="Delete">
    <i class="fa-solid fa-trash-can text-danger"></i>
</a>
    </td>
</tr>
@endforeach
</tbody>

    </table>

@else

<!-- NO DATA FOUND -->
<div class="text-center p-4 text-muted bg-white rounded">
    <i class="fa-solid fa-user-slash fa-2x mb-2"></i>
    <div><strong>No patients found</strong></div>
</div>

@endif
</div>
</div>


@include('patients.modal')
@endsection
@push('scripts')
<script>




/* ===============================
   DELETE
================================ */

$('body').on('click', '.deletePatient', function () {

    let deleteUrl   = $(this).data('url');
    let redirectUrl = $(this).data('redirect');

    Swal.fire({
        title: 'Are you sure?',
        text: "This patient record will be permanently deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Delete'
    }).then((result) => {

        if (result.isConfirmed) {

            $.post(deleteUrl, {
                _token: $('meta[name="csrf-token"]').attr('content'),
                redirecturl: redirectUrl
            })
            .done(function (res) {

                if (res.success) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    });

                    setTimeout(() => {
                        window.location.href = res.redirect;
                    }, 1500);

                } else {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Action not allowed',
                        text: res.message
                    });
                }

            })
            .fail(function () {

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Server error. Please try again later.'
                });
            });
        }
    });
});


</script>
@endpush
