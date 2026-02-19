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
<div class="t-job-sheet container-fluid g-0">
    <div class="t-table table-responsive">
        <table class="table table-borderless table-hover" id="default-datatable" style="width: 100%;">
            <thead>
                <tr>
                    <th>Amount</th>
                    <th>Validity</th>
                    <th>Discount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
            @foreach($fees as $fee)
                <tr>
                    <td>₹{{ $fee->amount }}</td>
                    <td>{{ $fee->validity_days }} Days</td>
                    <td>
                        {{ $fee->discount_type
                            ? ucfirst($fee->discount_type).' - '.$fee->discount_value
                            : '—' }}
                    </td>
                    <td>
                        <span class="badge bg-{{ $fee->is_active ? 'success' : 'secondary' }}">
                            {{ $fee->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.registration-fees.edit', $fee) }}"><i class="fa-solid fa-pen-to-square"></i></a>&nbsp;

                        <form action="{{ route('admin.registration-fees.status', $fee) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-sm {{ $fee->is_active ? 'btn-warning' : 'btn-success' }}"
                                    title="{{ $fee->is_active ? 'Deactivate' : 'Activate' }}">
                                <i class="fa-solid {{ $fee->is_active ? 'fa-circle-pause' : 'fa-circle-play' }}"></i>
                            </button>
                        </form>

                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')

@endpush
