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

{{-- Category Filter --}}
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET">
            <div class="row">
                <div class="col-md-3">
                    <label for="">Service Categories</label>
                    <select name="category_id"
                            class="form-select"
                            onchange="this.form.submit()">
                        <option value="">-- All --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}"
                                {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="t-job-sheet container-fluid g-0">
    <div class="t-table table-responsive">
        <table class="table table-borderless table-hover"
               id="default-datatable"
               style="width:100%;">

            <thead>
                <tr>
                    <th>Category</th>
                    <th>Service Name</th>
                    <th>Amount</th>
                    <th>Billing</th>
                    <th>GST %</th>
                    <th>Total</th>
                    <th width="120">Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($services as $service)
                    <tr>
                        <td>{{ $service->parent->name ?? '-' }}</td>

                        <td>{{ $service->name }}</td>

                        <td>
                            ₹ {{ $service->amount
                                ? number_format($service->amount,2)
                                : '-' }}
                        </td>

                        <td>
                            {{ ucfirst($service->billing_type ?? '-') }}
                        </td>

                        <td>
                            @if($service->gst_applicable)
                                {{ $service->gst_percentage }}%
                            @else
                                No
                            @endif
                        </td>

                        <td>
                            @if($service->amount)
                                ₹ {{ number_format(
                                    $service->gst_applicable
                                        ? $service->amount + ($service->amount * $service->gst_percentage / 100)
                                        : $service->amount
                                    ,2)
                                }}
                            @else
                                -
                            @endif
                        </td>

                        <td>
                            <a href="{{ route('admin.services.edit', $service->id) }}"
                               class="text-warning me-2">
                                <i class="fa fa-edit"></i>
                            </a>

                            <form action="{{ route('admin.services.destroy', $service->id) }}"
                                  method="POST"
                                  style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        style="border:none;background:none;"
                                        onclick="return confirm('Delete?')">
                                    <i class="fa fa-trash text-danger"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            No services found
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>
</div>

@endsection