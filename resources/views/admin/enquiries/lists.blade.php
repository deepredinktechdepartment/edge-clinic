@extends('template_v1')

@section('content')
<div class="tt-posts">
    <div class="d-flex justify-content-between tt-wrap mb-3">
        <div class="p-2 bd-highlight">
            <h5 class="mb-0 pb-0">{{$pageTitle??''}}</h5>
        </div>
        <div class="p-2 bd-highlight">
            @if(isset($addlink) && !empty($addlink))
            <a href="#offcanvasRight" data-bs-toggle="offcanvas" role="button" aria-controls="offcanvasRight"><i
                    class="fa-solid fa-circle-plus"></i></a>
            @else
            @endif
        </div>
    </div>
</div>

<div class="t-job-sheet container-fluid g-0">
    <div class="row">
        <div class="col-sm-6">
            <div class="t-table table-responsive">
                <table class="table table-borderless table-hover" id="default-datatable" style="width: 100%;">
                    <thead>
                        <tr>
                            <td scope="col" width="5">{{ Config::get('constants.SNO') }}</td>
                            <td scope="col">Name</td>
                            <td scope="col">Phone</td>
                            <td scope="col" width="5">Action</td>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach ($enquiries_data as $user)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ Str::title($user->name ?? '') }}</td>
                            <td>{{ $user->phone ?? '' }}</td>


                            <td>

                                <a href="{{ route('admin.enquiries.delete', ['ID' => Crypt::encryptString($user->id)]) }}"
                                    title="Delete" onclick="return confirm('Are you sure to delete this?')">
                                    <i class="fa-solid fa-trash-can"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>


@endsection
@push('scripts')


@endpush