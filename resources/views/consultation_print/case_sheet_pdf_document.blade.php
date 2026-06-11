@php
    $appointmentNumber = $consultation->token_number ?: ('EDGE-' . $consultation->id);
    $visitDate = optional($consultation->visit_date)->format('d/m/Y') ?? '-';
    $visitDateTime = trim($visitDate . ' ' . ($consultation->visit_time ?: ''));
    $dob = $consultation->patient->dob ?? '-';
    $ageGender = trim(($consultation->patient->age ?: '-') . ' / ' . ($consultation->patient->gender ?: '-'));
@endphp
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    @page { size: A4; margin: 0; }
    body {
        font-family: Arial, sans-serif;
        font-size: 11px;
        color: #1f2937;
        background: #fff;
    }
    .pdf-case-sheet {
        width: 186mm;
        margin: 0 auto;
        padding-top: 39mm;
        padding-bottom: 16mm;
        page-break-inside: avoid;
        page-break-after: avoid;
    }
    .pdf-case-sheet-title {
        text-align: center;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.05em;
        margin-bottom: 4mm;
    }
    .pdf-case-sheet-info {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .pdf-case-sheet-info td {
        vertical-align: top;
    }
    .pdf-left {
        width: 54%;
        padding-right: 4mm;
    }
    .pdf-right {
        width: 46%;
        padding-left: 3mm;
    }
    .pdf-fields {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .pdf-fields td {
        padding: 2px 0;
        vertical-align: top;
        line-height: 1.25;
    }
    .pdf-fields .label {
        font-weight: 700;
        white-space: nowrap;
    }
    .pdf-fields .colon {
        width: 8px;
        text-align: center;
        white-space: nowrap;
    }
    .pdf-fields-left .label {
        width: 98px;
    }
    .pdf-fields-right .label {
        width: 68px;
    }
    .pdf-divider {
        border-top: 1px solid #c7ced6;
        margin: 6px 0;
    }
    .pdf-vitals {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .pdf-vitals td {
        padding: 3px 0;
        line-height: 1.25;
        vertical-align: top;
    }
    .pdf-vitals .colon {
        display: inline-block;
        width: 9px;
        text-align: center;
    }
    .pdf-writing {
        height: 152mm;
        overflow: hidden;
    }
    .pdf-next {
        text-align: right;
        margin-top: 2mm;
        font-size: 10px;
        color: #444;
    }
</style>

<div class="pdf-case-sheet">
    <div class="pdf-case-sheet-title">CASESHEET</div>

    <table class="pdf-case-sheet-info">
        <tr>
            <td class="pdf-left">
                <table class="pdf-fields pdf-fields-left">
                    <tr>
                        <td class="label">Appointment No</td>
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
            <td class="pdf-right">
                <table class="pdf-fields pdf-fields-right">
                    <tr>
                        <td class="label">Visit Date</td>
                        <td class="colon">:</td>
                        <td>{{ $visitDateTime ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Doctor</td>
                        <td class="colon">:</td>
                        <td>{{ $consultation->doctor?->name ?? '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="pdf-divider"></div>

    <table class="pdf-vitals">
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

    <div class="pdf-divider"></div>

    <div class="pdf-writing"></div>
    <div class="pdf-next">Next visit :</div>
</div>
