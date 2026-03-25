@extends('template_v1')

@section('title', 'Medicines')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-capsule text-primary"></i> Medicines</h4>
    <span class="text-muted small">{{ $medicines->total() }} records found</span>
</div>

{{-- Search & Filter --}}
<form method="GET" action="{{ route('admin.medicines.index') }}" class="row g-2 mb-4">
    <div class="col-md-5">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text"
                   name="search"
                   class="form-control"
                   placeholder="Search by name, manufacturer, composition..."
                   value="{{ request('search') }}">
        </div>
    </div>
    <div class="col-md-4">
        <select name="type" class="form-select">
            <option value="">All Therapeutic Classes</option>
            @foreach($therapeuticClasses as $class)
                <option value="{{ $class }}" {{ request('type') == $class ? 'selected' : '' }}>
                    {{ $class }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-auto">
        <button type="submit" class="btn btn-brand btn-sm mt-0">
            <i class="bi bi-funnel"></i> Filter
        </button>
        <a href="{{ route('admin.medicines.index') }}" class="btn btn-brand btn-sm mt-0">
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
                        <th>#</th>
                        <th>Name</th>
                        <th>Price (₹)</th>
                        <th>Composition</th>
                        <th>Manufacturer</th>
                        <th>Therapeutic Class</th>
                        <th>Pack Size</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($medicines as $medicine)
                    <tr>
                        <td class="text-muted small">{{ $medicine->id }}</td>
                        <td class="fw-semibold">{{ $medicine->name }}</td>
                        <td>₹{{ number_format($medicine->price_inr, 2) }}</td>
                        <td>
                            <span class="text-primary">{{ $medicine->short_composition1 }}</span>
                            @if($medicine->short_composition2)
                                <br><span class="text-secondary small">{{ $medicine->short_composition2 }}</span>
                            @endif
                        </td>
                        <td class="small">{{ $medicine->manufacturer_name }}</td>
                        <td>
                            @if($medicine->therapeutic_class)
                                <span class="badge bg-info text-dark">{{ $medicine->therapeutic_class }}</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $medicine->pack_size_label }}</td>
                        <td>
                            @if($medicine->is_discontinued)
                                <span class="badge bg-danger">Discontinued</span>
                            @else
                                <span class="badge bg-success">Active</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                            No medicines found.
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
        Showing {{ $medicines->firstItem() }}–{{ $medicines->lastItem() }} of {{ $medicines->total() }}
    </span>
    {{ $medicines->links('pagination::bootstrap-5') }}
</div>

@endsection