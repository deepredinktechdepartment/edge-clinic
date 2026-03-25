@extends('template_v1')

@section('title', 'ICD-10 Codes')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-clipboard2-pulse text-primary"></i> ICD-10 Codes</h4>
    <span class="text-muted small">{{ $codes->total() }} records found</span>
</div>

{{-- Search --}}
<form method="GET" action="{{ route('admin.icd10.index') }}" class="row g-2 mb-4">
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text"
                   name="search"
                   class="form-control"
                   placeholder="Search by code or description..."
                   value="{{ request('search') }}">
        </div>
    </div>
    <div class="col-md-auto">
        <button type="submit" class="btn btn-brand btn-sm mt-0">
            <i class="bi bi-funnel"></i> Search
        </button>
        <a href="{{ route('admin.icd10.index') }}" class="btn btn-brand btn-sm mt-0">
            <i class="bi bi-x-circle"></i> Clear
        </a>
    </div>
</form>

{{-- Table --}}
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table t-table table-hover table-bordered mb-0">
                <thead>
                    <tr>
                        <th>Full Code</th>
                        <th>Parent Code</th>
                        <th>Sub Code</th>
                        <th>Short Description</th>
                        <th>Long Description</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($codes as $code)
                    <tr>
                        <td>
                            <span class="badge bg-primary fs-6">{{ $code->full_code }}</span>
                        </td>
                        <td class="text-muted small">{{ $code->parent_code }}</td>
                        <td class="text-muted small">{{ $code->sub_code }}</td>
                        <td class="fw-semibold">{{ $code->short_description }}</td>
                        <td class="text-muted small">{{ $code->long_description }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                            No ICD-10 codes found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Pagination --}}
<div class="d-flex justify-content-between align-items-center mt-3">
    <span class="text-muted small">
        Showing {{ $codes->firstItem() }}–{{ $codes->lastItem() }} of {{ $codes->total() }}
    </span>
    {{ $codes->links('pagination::bootstrap-5') }}
</div>

@endsection