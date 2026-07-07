@extends('template_v1')

@section('content')
@include('cabins.partials.styles')

<div class="cabin-shell">
    @include('cabins.partials.page-header', [
        'title' => $pageTitle,
        'subtitle' => 'Maintain the facility master used inside cabin forms and cabin view pages.',
        'actions' => [
            ['url' => route('admin.cabins.index'), 'label' => 'Cabins', 'icon' => 'bi bi-door-open', 'class' => 'btn-outline-secondary'],
            ['url' => $addlink, 'label' => 'Add Facility', 'icon' => 'bi bi-plus-circle', 'class' => 'btn-brand'],
        ],
    ])

    <div class="cabin-panel">
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="default-datatable">
                    <thead>
                        <tr>
                            <th>Facility</th>
                            <th>Pricing</th>
                            <th>Payment Note</th>
                            <th>Status</th>
                            <th>Used In</th>
                            <th>Order</th>
                            <th width="120">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($facilities as $facility)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $facility->name }}</div>
                                    <div class="small text-muted">{{ $facility->slug }}</div>
                                    @if($facility->description)
                                        <div class="small text-muted mt-1">{{ $facility->description }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($facility->pricing_type === 'paid')
                                        <div class="fw-semibold text-dark">Paid</div>
                                        <div class="small text-muted">Rs {{ number_format((float) $facility->rate, 2) }} {{ $facility->charge_label ?: 'Per Use' }}</div>
                                    @else
                                        <div class="fw-semibold text-success">Free</div>
                                    @endif
                                </td>
                                <td>{{ $facility->payment_note ?: '-' }}</td>
                                <td>{{ ucfirst($facility->status) }}</td>
                                <td>{{ $facility->cabins_count }} cabins</td>
                                <td>{{ $facility->sort_order }}</td>
                                <td>
                                    <a href="{{ route('admin.cabins.facilities.edit', $facility->id) }}" class="text-warning me-2"><i class="fa fa-edit"></i></a>
                                    <form action="{{ route('admin.cabins.facilities.destroy', $facility->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this facility?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="border-0 bg-transparent p-0"><i class="fa fa-trash text-danger"></i></button>
                                    </form>
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
