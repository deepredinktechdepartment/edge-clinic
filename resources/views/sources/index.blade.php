@extends('template_v1')

@section('content')
<div class="tt-posts">
    <div class="d-flex justify-content-between tt-wrap mb-3">
        <div class="p-2 bd-highlight">
            <h5 class="mb-0 pb-0">{{ $pageTitle ?? '' }}</h5>
        </div>
        <div class="p-2 bd-highlight">
            @if(isset($addlink) && !empty($addlink))
                <a href="{{ $addlink }}">
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
                    <th>Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th width="120">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sources as $source)
                    <tr>
                        <td>{{ $source->name }}</td>
                        <td>{{ $source->description ?: '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $source->status ? 'success' : 'secondary' }}">
                                {{ $source->status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.sources.edit', $source->id) }}" class="text-warning me-2">
                                <i class="fa fa-edit"></i>
                            </a>

                            <form action="{{ route('admin.sources.destroy', $source->id) }}"
                                  method="POST"
                                  style="display:inline;"
                                  onsubmit="return confirm('Are you sure you want to delete this source?')">
                                @csrf
                                @method('DELETE')

                                <button type="submit" style="border:none;background:none;">
                                    <i class="fa fa-trash text-danger"></i>
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
