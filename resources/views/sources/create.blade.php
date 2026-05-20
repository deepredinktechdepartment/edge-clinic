@extends('template_v1')

@section('content')

<div class="tt-posts">
    <div class="d-flex justify-content-between tt-wrap mb-3">
        <div class="p-2">
            <h5 class="mb-0 pb-0">{{ $pageTitle ?? '' }}</h5>
        </div>
    </div>
</div>

@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="row">
    <div class="col-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <form id="sourceForm"
                      method="POST"
                      action="{{ isset($source) ? route('admin.sources.update', $source->id) : route('admin.sources.store') }}">
                    @csrf
                    @if(isset($source))
                        @method('PUT')
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Source Name <span class="text-danger">*</span></label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               value="{{ old('name', $source->name ?? '') }}"
                               maxlength="255"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4">{{ old('description', $source->description ?? '') }}</textarea>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input"
                               type="checkbox"
                               name="status"
                               value="1"
                               {{ old('status', $source->status ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label">
                            Active
                        </label>
                    </div>

                    <div>
                        <button type="submit" class="btn btn-brand">
                            {{ isset($source) ? 'Update' : 'Save' }}
                        </button>
                        <a href="{{ route('admin.sources.index') }}" class="btn btn-danger text-white mt-2">Back</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function () {
    $('#sourceForm').validate({
        rules: {
            name: {
                required: true,
                maxlength: 255
            }
        },
        messages: {
            name: 'Please enter source name'
        },
        errorClass: 'text-danger',
        errorElement: 'small'
    });
});
</script>
@endpush
