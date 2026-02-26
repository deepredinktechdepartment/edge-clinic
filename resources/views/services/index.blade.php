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
                    <th>CGST %</th>
                    <th>SGST %</th>
                    <th>IGST %</th>
                    <th class="text-center">Intra State Total<br><small>(CGST + SGST)</small></th>
                    <th class="text-center">Inter State Total<br><small>(IGST)</small></th>
                    <th width="120">Action</th>
                </tr>
            </thead>

            <tbody>
                @foreach($services as $service)

                    @php
                        $isCategory = is_null($service->parent_id);

                        $amount = $service->amount ?? 0;

                        $cgst = $service->cgst ?? 0;
                        $sgst = $service->sgst ?? 0;
                        $igst = $service->igst ?? 0;

                        $intraPercent = $cgst + $sgst;
                        $interPercent = $igst;

                        $intraFinal = $amount + (($amount * $intraPercent) / 100);
                        $interFinal = $amount + (($amount * $interPercent) / 100);
                    @endphp

                    <tr class="{{ $isCategory ? 'table-primary' : '' }}">

                        {{-- Category Column --}}
                        <td>
                            @if($isCategory)
                                <strong>{{ $service->name }}</strong>
                            @else
                                {{ $service->parent->name ?? '-' }}
                            @endif
                        </td>

                        {{-- Service Name --}}
                        <td>
                            @if($isCategory)
                                <span class="badge bg-primary">Category</span>
                            @else
                                {{ $service->name }}
                            @endif
                        </td>

                        {{-- Amount --}}
                        <td>
                            @if($isCategory)
                                -
                            @else
                                ₹ {{ number_format($amount,2) }}
                            @endif
                        </td>

                        {{-- GST Columns --}}
                        <td>{{ $isCategory ? '-' : $cgst.'%' }}</td>
                        <td>{{ $isCategory ? '-' : $sgst.'%' }}</td>
                        <td>{{ $isCategory ? '-' : $igst.'%' }}</td>

                        {{-- Intra --}}
                        <td class="text-center">
                            {{ $isCategory ? '-' : '₹ '.number_format($intraFinal,2) }}
                        </td>

                        {{-- Inter --}}
                        <td class="text-center">
                            {{ $isCategory ? '-' : '₹ '.number_format($interFinal,2) }}
                        </td>

                        {{-- Action --}}
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

                                <input type="hidden" name="confirm_delete" value="1">

                                <button type="submit"
                                        style="border:none;background:none;"
                                        onclick="return confirm('Are you sure?')">
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