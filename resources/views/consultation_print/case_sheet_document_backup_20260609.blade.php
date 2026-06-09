@php
    $appointmentNumber = $consultation->token_number ?: ('EDGE-' . $consultation->id);
    $visitDate = optional($consultation->visit_date)->format('d/m/Y') ?? '-';
    $visitDateTime = trim($visitDate . ' ' . ($consultation->visit_time ?: ''));
    $dob = $consultation->patient->dob ?? '-';
    $ageGender = trim(($consultation->patient->age ?: '-') . ' / ' . ($consultation->patient->gender ?: '-'));
@endphp
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    @page { size: A4; margin: 6mm; }
    html, body { width: 100%; height: 100%; overflow: hidden; }
    body { font-family: Arial, sans-serif; font-size: 12px; color: #1f2937; background: #fff; overflow: hidden; page-break-after: avoid; }
    .cs-page {
        position: relative;
        width: 182mm;
        height: 279mm;
        margin: 0 auto;
        overflow: hidden;
        page-break-inside: avoid;
        page-break-after: avoid;
    }
    .cs-header {
        position: absolute;
        top: 0;
        left: 8mm;
        right: 8mm;
        text-align: center;
        border-bottom: 1px solid #8f8f8f;
        padding: 3mm 0 3mm;
    }
    .cs-header img {
        width: 150px;
        display: inline-block;
    }
    .cs-title {
        margin-top: 0;
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.02em;
        text-align: center;
        margin-bottom: 3mm;
    }
    .cs-main {
        position: absolute;
        top: 28mm;
        left: 8mm;
        right: 8mm;
        bottom: 18mm;
        overflow: hidden;
    }
    .cs-info {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .cs-info > tbody > tr > td {
        vertical-align: top;
        width: 50%;
    }
    .cs-col-left {
        padding-right: 1mm;
    }
    .cs-col-right {
        padding-left: 6mm;
    }
    .cs-fields {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .cs-fields td {
        font-size: 11px;
        padding: 3px 0;
        vertical-align: top;
    }
    .cs-fields .label {
        width: 68px;
        font-weight: 700;
    }
    .cs-fields .colon {
        width: 8px;
        text-align: center;
    }
    .cs-fields .value {
        padding-left: 0;
    }
    .cs-fields-left .label {
        width: 42px;
    }
    .cs-fields-left .colon {
        width: 6px;
    }
    .cs-fields-right .label {
        width: 64px;
    }
    .cs-fields-right .colon {
        width: 6px;
    }
    .cs-line {
        border-top: 1px solid #cfcfcf;
        margin: 8px 0;
    }
    .cs-vitals {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .cs-vitals td {
        font-size: 11px;
        padding: 4px 0;
        vertical-align: top;
    }
    .cs-vitals .v-colon {
        display: inline-block;
        width: 10px;
        text-align: center;
    }
    .cs-writing {
        position: relative;
        height: 146mm;
        overflow: hidden;
    }
    .cs-watermark {
        position: absolute;
        top: 52%;
        left: 50%;
        width: 90mm;
        transform: translate(-50%, -50%);
        opacity: 0.075;
    }
    .cs-next {
        position: absolute;
        right: 10px;
        bottom: 2px;
        font-size: 10px;
        color: #444;
    }
    .cs-footer {
        position: absolute;
        left: 8mm;
        right: 8mm;
        bottom: 0;
        border-top: 1px solid #8f8f8f;
        padding-top: 2mm;
        text-align: center;
        font-size: 10px;
        line-height: 1.35;
        background: #fff;
        page-break-inside: avoid;
    }
    .cs-footer strong {
        font-size: 11px;
    }
    .cs-footer img {
        width: 30px;
        margin-top: 2px;
    }
    @media screen {
        body {
            background: #f5f7fa;
            padding: 0;
        }
        .cs-page {
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 8px 30px rgba(16, 24, 40, 0.08);
        }
    }
</style>

<div class="cs-page">
    <div class="cs-header">
        <img src="{{ asset('storage/app/public/edge_logo.png') }}" alt="Edge Clinic">
    </div>

    <div class="cs-main">
        <div class="cs-title">CASESHEET</div>
        <table class="cs-info">
            <tr>
                <td class="cs-col-left">
                    <table class="cs-fields cs-fields-left">
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
                <td class="cs-col-right">
                    <table class="cs-fields cs-fields-right">
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

        <div class="cs-line"></div>

        <table class="cs-vitals">
            <tr>
                <td><strong>Height</strong> <span class="v-colon">:</span> {{ $consultation->height ?: '-' }}</td>
                <td><strong>Weight</strong> <span class="v-colon">:</span> {{ $consultation->weight ?: '-' }}</td>
                <td><strong>BMI</strong> <span class="v-colon">:</span> {{ $consultation->bmi ?: '-' }}</td>
                <td><strong>Temp</strong> <span class="v-colon">:</span> {{ $consultation->temperature ?: '-' }}</td>
            </tr>
            <tr>
                <td><strong>BP</strong> <span class="v-colon">:</span> {{ ($consultation->bp_systolic ?: '-') . ' / ' . ($consultation->bp_diastolic ?: '-') }}</td>
                <td colspan="3"><strong>Remarks</strong> <span class="v-colon">:</span></td>
            </tr>
        </table>

        <div class="cs-line"></div>

        <div class="cs-writing">
            <img class="cs-watermark" src="{{ asset('storage/app/public/edge_logo.png') }}" alt="Watermark">
            <div class="cs-next">Next visit :</div>
        </div>
    </div>

    <div class="cs-footer">
        <p><strong>4th Floor, The Medical Centre, HITEC City</strong></p>
        <p>Survey No. 64, Huda Techno Park, Phase 2, Hyderabad - 500081</p>
        <p>Ph: 9392585050</p>
        <img src="{{ asset('storage/app/public/plus-icons.png') }}" alt="Edge Clinic icon">
    </div>
</div>

@if(!empty($autoPrint))
    <script>
        window.onload = function () {
            window.print();
            window.onafterprint = function () {
                window.close();
            };
        };
    </script>
@endif
