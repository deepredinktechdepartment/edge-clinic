@extends('template_v1')

@section('content')
@include('cabins.partials.styles')

<div class="cabin-shell">
    @include('cabins.partials.page-header', [
        'title' => $invoice->invoice_number,
        'subtitle' => ($invoice->doctor->name ?? '-') . ' · ' . ucfirst($invoice->billing_type) . ' cabin billing',
        'actions' => [
            ['url' => route('admin.cabins.invoices.print', $invoice->id), 'label' => 'Print', 'icon' => 'bi bi-printer', 'class' => 'btn-outline-secondary', 'target' => '_blank'],
            ['url' => route('admin.cabins.invoices.pdf', $invoice->id), 'label' => 'PDF', 'icon' => 'bi bi-file-earmark-pdf', 'class' => 'btn-outline-secondary'],
            ['url' => route('admin.cabins.invoices.index'), 'label' => 'Back to Invoices', 'icon' => 'bi bi-arrow-left', 'class' => 'btn-brand'],
        ],
    ])

    <div class="cabin-split">
        <div class="cabin-panel">
            <div class="panel-head">
                <h5 class="mb-0">Invoice Detail</h5>
            </div>
            <div class="panel-body">
                <div class="booking-invoice-meta">
                    <div class="booking-invoice-meta-box">
                        <div class="mini-label">Invoice Date</div>
                        <div class="mini-value">{{ optional($invoice->invoice_date)->format('d M Y') }}</div>
                    </div>
                    <div class="booking-invoice-meta-box">
                        <div class="mini-label">Due Date</div>
                        <div class="mini-value">{{ optional($invoice->due_date)->format('d M Y') }}</div>
                    </div>
                    <div class="booking-invoice-meta-box">
                        <div class="mini-label">Status</div>
                        <div class="mini-value">{{ ucfirst($invoice->status) }}</div>
                    </div>
                    <div class="booking-invoice-meta-box">
                        <div class="mini-label">Sent Via</div>
                        <div class="mini-value">{{ $invoice->sent_via ? ucfirst($invoice->sent_via) : 'Not sent' }}</div>
                    </div>
                    <div class="booking-invoice-meta-box">
                        <div class="mini-label">Period Start</div>
                        <div class="mini-value">{{ optional($invoice->period_start)->format('d M Y') }}</div>
                    </div>
                    <div class="booking-invoice-meta-box">
                        <div class="mini-label">Period End</div>
                        <div class="mini-value">{{ optional($invoice->period_end)->format('d M Y') }}</div>
                    </div>
                    <div class="booking-invoice-meta-box">
                        <div class="mini-label">Billing Type</div>
                        <div class="mini-value">{{ ucfirst($invoice->billing_type) }}</div>
                    </div>
                    <div class="booking-invoice-meta-box">
                        <div class="mini-label">Doctor</div>
                        <div class="mini-value">{{ $invoice->doctor->name ?? '-' }}</div>
                    </div>
                </div>

                <div class="booking-invoice-table-wrap">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 booking-invoice-table">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Qty</th>
                                    <th>Rate</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoice->items as $item)
                                    <tr>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ number_format((float) $item->quantity, 2) }}</td>
                                        <td>Rs {{ number_format((float) $item->unit_rate, 2) }}</td>
                                        <td class="text-end fw-semibold text-dark">Rs {{ number_format((float) $item->line_total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No invoice items available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="booking-invoice-totals">
                    <div class="booking-invoice-total-row">
                        <span>Sub-total</span>
                        <strong>Rs {{ number_format((float) $invoice->subtotal, 2) }}</strong>
                    </div>
                    <div class="booking-invoice-total-row">
                        <span>GST ({{ number_format((float) $invoice->gst_percent, 2) }}%)</span>
                        <strong>Rs {{ number_format((float) $invoice->gst_amount, 2) }}</strong>
                    </div>
                    <div class="booking-invoice-total-row booking-invoice-total-grand">
                        <span>Grand Total</span>
                        <strong>Rs {{ number_format((float) $invoice->total_amount, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="cabin-panel">
            <div class="panel-head">
                <h5 class="mb-0">Doctor Summary</h5>
            </div>
            <div class="panel-body">
                <div class="booking-invoice-meta" style="grid-template-columns: 1fr;">
                    <div class="booking-invoice-meta-box">
                        <div class="mini-label">Doctor</div>
                        <div class="mini-value">{{ $invoice->doctor->name ?? '-' }}</div>
                        <div class="text-muted mt-1">{{ $invoice->doctor->department->name ?? $invoice->doctor->designation ?? 'Doctor' }}</div>
                    </div>
                    <div class="booking-invoice-meta-box">
                        <div class="mini-label">Cabin</div>
                        <div class="mini-value">{{ $invoice->cabin->cabin_code ?? 'Mixed / Multiple' }}</div>
                        <div class="text-muted mt-1">{{ $invoice->cabin->name ?? 'Cabin allocation' }}</div>
                    </div>
                    <div class="booking-invoice-meta-box">
                        <div class="mini-label">Notes</div>
                        <div class="text-muted">{{ $invoice->notes ?: 'No additional notes for this invoice.' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
