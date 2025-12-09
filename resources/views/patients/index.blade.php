@extends('layouts.app')

@section('content')
<div class="container">

    <h4 class="mb-3">Patient Profiles</h4>
    <button class="btn btn-primary mb-3" id="addBtn">+ Add Patient</button>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Mobile</th>
                <th>Email</th>
                <th width="160">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($patients as $p)
            <tr>
                <td>{{ $p->patient_code }}</td>
                <td>{{ $p->name }}</td>
                <td>{{ $p->mobile }}</td>
                <td>{{ $p->email }}</td>
                <td>
                    <button class="btn btn-sm btn-warning editBtn" data-id="{{ $p->id }}">Edit</button>
                    <button class="btn btn-sm btn-danger deleteBtn" data-id="{{ $p->id }}">Delete</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>

@include('patients.modal')

@endsection
@push('scripts')
<script>

// Add
$('#addBtn').click(function () {
    $('#patientForm')[0].reset();
    $('#id').val('');
    $('#patientModal').modal('show');
});

// Save or Update
$('#patientForm').on('submit', function (e) {
    e.preventDefault();

    let id = $('#id').val();
    let url = id ? `/patients/update/${id}` : `/patients/store`;

    $.post(url, $(this).serialize(), function (res) {
        if (res.success) location.reload();
    });
});

// Edit
$('body').on('click', '.editBtn', function () {
    let id = $(this).data('id');

    $.get(`/patients/edit/${id}`, function (data) {
        $('#id').val(data.id);
        $('#patient_code').val(data.patient_code);
        $('#name').val(data.name);
        $('#mobile').val(data.mobile);
        $('#email').val(data.email);
        $('#address').val(data.address);

        $('#patientModal').modal('show');
    });
});

// Delete
$('body').on('click', '.deleteBtn', function () {
    if (!confirm("Delete patient?")) return;

    let id = $(this).data('id');

    $.ajax({
        url: `/patients/delete/${id}`,
        type: 'DELETE',
        data: { _token: '{{ csrf_token() }}' },
        success: function (res) {
            if (res.success) location.reload();
        }
    });
});
</script>
@endpush
