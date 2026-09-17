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
    html, body { width: 100%; margin: 0; padding: 0; }
    body {
        font-family: Arial, sans-serif;
        font-size: 12px;
        color: #1f2937;
        background: #fff;
    }
    .cs-page {
        width: 100%;
        max-width: 210mm;
        margin: 0 auto;
        page-break-inside: avoid;
        page-break-after: avoid;
        background: #fff;
    }
    .cs-content {
        padding: 42mm 12mm 22mm;
    }
    .cs-title {
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-align: center;
        margin-bottom: 4mm;
    }
    .cs-info {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin-bottom: 2mm;
    }
    .cs-info > tbody > tr > td {
        vertical-align: top;
        width: 50%;
    }
    .cs-col-left {
        padding-right: 2mm;
    }
    .cs-col-right {
        padding-left: 5mm;
    }
    .cs-fields {
        width: 100%;
        border-collapse: collapse;
        table-layout: auto;
    }
    .cs-fields td {
        font-size: 11px;
        padding: 3px 0;
        vertical-align: top;
    }
    .cs-fields .label {
        font-weight: 700;
        white-space: nowrap;
    }
    .cs-fields .colon {
        width: 12px;
        text-align: center;
        white-space: nowrap;
    }
    .cs-fields-left .label {
        width: 100px;
    }
    .cs-fields-right .label {
        width: 72px;
    }
    .cs-line {
        border-top: 1px solid #c7ced6;
        margin: 8px 0 7px;
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
        height: 168mm;
        overflow: hidden;
    }
    .cs-next {
        text-align: right;
        font-size: 10px;
        color: #444;
        margin-top: 2mm;
    }
    @media screen {
        body {
            background: #f5f7fa;
            padding: 18px 0;
        }
        .cs-page {
            margin: 0 auto;
            box-shadow: 0 8px 30px rgba(16, 24, 40, 0.08);
        }
    }
</style>

<div class="cs-page">
    <div class="cs-content">
        <div class="cs-title">CASESHEET</div>
        <table class="cs-info">
            <tr>
                <td class="cs-col-left">
                    <table class="cs-fields cs-fields-left">
                        <colgroup>
                            <col style="width: 100px">
                            <col style="width: 12px">
                            <col>
                        </colgroup>
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
                <td class="cs-col-right">
                    <table class="cs-fields cs-fields-right">
                        <colgroup>
                            <col style="width: 72px">
                            <col style="width: 12px">
                            <col>
                        </colgroup>
                        <tr>
                            <td class="label">Visit Date</td>
                            <td class="colon">:</td>
                            <td>{{ $visitDateTime ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Doctor</td>
                            <td class="colon">:</td>
                            <td>
                                {{ $consultation->doctor?->name ?? '-' }}
                                @if($consultation->doctor?->designation)
                                    <br>{{ $consultation->doctor->designation }}
                                @endif
                                @if($consultation->doctor?->qualification)
                                    <br>{{ $consultation->doctor->qualification }}
                                @endif
                                @if($consultation->doctor?->registration_number)
                                    <br>Dr. Reg. No: {{ $consultation->doctor->registration_number }}
                                @endif
                            </td>
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
                <td><strong>Temp</strong> <span class="v-colon">:</span> {{ $consultation->temperature ?: '-' }}</td>
            </tr>
            <tr>
                <td><strong>BP</strong> <span class="v-colon">:</span> {{ ($consultation->bp_systolic ?: '-') . ' / ' . ($consultation->bp_diastolic ?: '-') }}</td>
            </tr>
        </table>

        <div class="cs-line"></div>

        <div class="cs-writing">
            <div class="cs-next">Next visit :</div>
        </div>
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
