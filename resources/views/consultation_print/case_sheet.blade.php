@php
    $appointmentNumber = $consultation->token_number ?: ('EDGE-' . $consultation->id);
    $visitDate = optional($consultation->visit_date)->format('d/m/Y') ?? '-';
    $visitDateTime = trim($visitDate . ' ' . ($consultation->visit_time ?: ''));
    $dob = $consultation->patient->dob ?? '-';
    $ageGender = trim(($consultation->patient->age ?: '-') . ' / ' . ($consultation->patient->gender ?: '-'));
@endphp

<style>
    .casesheet-page {
        position: relative;
        width: 100%;
        overflow: hidden;
        page-break-inside: avoid;
    }
    .casesheet-watermark {
        position: absolute;
        top: 52%;
        left: 50%;
        width: 92mm;
        transform: translate(-50%, -50%);
        opacity: 0.045;
        z-index: 0;
    }
    .casesheet-content {
        position: relative;
        z-index: 1;
        width: 100%;
    }
    .casesheet-heading {
        text-align: center;
        font-size: 13px;
        font-weight: 700;
        color: #222;
        margin-bottom: 10px;
    }
    .casesheet-info-table,
    .casesheet-vitals-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .casesheet-info-table {
        margin-bottom: 8px;
    }
    .casesheet-info-col {
        width: 50%;
        vertical-align: top;
    }
    .casesheet-fields {
        width: 100%;
        border-collapse: collapse;
    }
    .casesheet-fields td {
        padding: 4px 0;
        font-size: 11px;
        vertical-align: top;
    }
    .casesheet-fields .label {
        width: 56px;
        font-weight: 700;
    }
    .casesheet-fields .colon {
        width: 14px;
        text-align: center;
    }
    .casesheet-divider {
        border-top: 1px solid #c3c9cf;
        margin: 6px 0 6px;
    }
    .casesheet-vitals-table td {
        padding: 6px 0;
        font-size: 11px;
        vertical-align: top;
    }
    .casesheet-vitals-table .colon {
        display: inline-block;
        width: 10px;
        text-align: center;
    }
    .casesheet-writing-area {
        height: 112mm;
    }
    .casesheet-next-visit {
        text-align: right;
        padding-right: 10px;
        font-size: 10px;
        color: #444;
        margin-top: 2mm;
    }
</style>

<div class="casesheet-page">
    <img class="casesheet-watermark" src="{{ asset('storage/app/public/edge_logo.png') }}" alt="Watermark">

    <div class="casesheet-content">
        <div class="casesheet-heading">CASESHEET</div>

        <table class="casesheet-info-table">
            <tr>
                <td class="casesheet-info-col">
                    <table class="casesheet-fields">
                        <tr>
                            <td class="label">ID</td>
                            <td class="colon">:</td>
                            <td>{{ $appointmentNumber }}</td>
                        </tr>
                        <tr>
                            <td class="label">Name</td>
                            <td class="colon">:</td>
                            <td>{{ $consultation->patient->name ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Mobile</td>
                            <td class="colon">:</td>
                            <td>{{ $consultation->patient->mobile ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">DOB</td>
                            <td class="colon">:</td>
                            <td>{{ $dob }}</td>
                        </tr>
                        <tr>
                            <td class="label">Age/Gender</td>
                            <td class="colon">:</td>
                            <td>{{ $ageGender }}</td>
                        </tr>
                    </table>
                </td>
                <td class="casesheet-info-col">
                    <table class="casesheet-fields">
                        <tr>
                            <td class="label">Visit Date</td>
                            <td class="colon">:</td>
                            <td>{{ $visitDateTime ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Dr</td>
                            <td class="colon">:</td>
                            <td>{{ $consultation->doctor?->name ?? '-' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="casesheet-divider"></div>

        <table class="casesheet-vitals-table">
            <tr>
                <td><strong>Height</strong> <span class="colon">:</span> {{ $consultation->height ?: '-' }}</td>
                <td><strong>Weight</strong> <span class="colon">:</span> {{ $consultation->weight ?: '-' }}</td>
                <td><strong>BMI</strong> <span class="colon">:</span> {{ $consultation->bmi ?: '-' }}</td>
                <td><strong>Temp</strong> <span class="colon">:</span> {{ $consultation->temperature ?: '-' }}</td>
            </tr>
            <tr>
                <td><strong>BP</strong> <span class="colon">:</span> {{ ($consultation->bp_systolic ?: '-') . ' / ' . ($consultation->bp_diastolic ?: '-') }}</td>
                <td colspan="3"><strong>Remarks</strong> <span class="colon">:</span></td>
            </tr>
        </table>

        <div class="casesheet-divider"></div>
        <div class="casesheet-writing-area"></div>
        <div class="casesheet-next-visit">Next visit :</div>
    </div>
</div>
