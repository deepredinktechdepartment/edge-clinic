@php
    $history = $consultation->patient->consultationHistory;
    $isPrescription = ($printMode ?? 'case_sheet') === 'prescription';
@endphp
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    @page { size: A4; margin: 6mm; }
    body { font-family: Arial, sans-serif; font-size: 12px; color: #273341; background: #fff; margin: 0; }
    .page-shell {
        width: 100%;
        padding: 0 6mm;
        page-break-inside: avoid;
    }
    .page-header {
        text-align: center;
        padding: 2mm 0 3mm;
        border-bottom: 1px solid #666;
    }
    .page-header img {
        width: 148px;
        display: inline-block;
    }
    .page-title {
        text-align: center;
        font-size: 18px;
        font-weight: 700;
        color: #1f2d3d;
        margin-top: 8px;
        letter-spacing: 0.04em;
    }
    .page-content {
        padding-top: 6mm;
        page-break-inside: avoid;
    }
    .page-footer {
        margin-top: 4mm;
        padding-top: 2mm;
        border-top: 1px solid #000;
        text-align: center;
        font-size: 10px;
        line-height: 1.35;
        page-break-inside: avoid;
    }
    .page-footer strong {
        font-size: 11px;
    }
    .page-footer img {
        width: 32px;
        margin-top: 2px;
    }

    .card { border: 1px solid #d8e2eb; border-radius: 12px; padding: 14px; margin-bottom: 12px; page-break-inside: avoid; }
    .card-title { font-size: 14px; font-weight: 700; color: #2f7aa9; margin-bottom: 10px; padding-bottom: 6px; border-bottom: 1px solid #e5edf4; }
    .grid { display: grid; gap: 10px 16px; }
    .grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .field-label { font-size: 10px; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; color: #789; margin-bottom: 3px; }
    .field-value { min-height: 18px; line-height: 1.45; }
    .muted { color: #708090; }
    .pill { display: inline-block; padding: 4px 10px; border-radius: 999px; background: #eef6fc; color: #2f7aa9; margin: 3px 6px 0 0; font-size: 11px; }
    .row { padding: 8px 0; border-bottom: 1px solid #edf2f6; }
    .row:last-child { border-bottom: none; }
    .rx-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
    .rx-table th, .rx-table td { border: 1px solid #d7e0e8; padding: 8px; text-align: left; vertical-align: top; }
    .rx-table th { background: #f3f7fb; font-size: 11px; text-transform: uppercase; color: #2f7aa9; }
    .rx-no { width: 40px; text-align: center; }
    .rx-name { font-weight: 700; }
    .rx-notes { color: #6f7f8e; font-size: 11px; margin-top: 2px; }
    .sign-row { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 30px; margin-top: 26px; }
    .sign-box { padding-top: 26px; border-top: 1px solid #9aa8b4; text-align: center; font-size: 11px; color: #556371; }

    @media screen {
        body { background: #f5f7fa; }
        .page-shell {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 8px 30px rgba(16, 24, 40, 0.08);
            padding: 6mm 12mm;
        }
    }
</style>

<div class="page-shell">
    <header class="page-header">
        <img src="{{ asset('storage/app/public/edge_logo.png') }}" alt="Edge Clinic">
        @if($isPrescription)
            <div class="page-title">Prescription</div>
        @endif
    </header>

    <div class="page-content">
        @if($isPrescription)
            @include('consultation_print.prescription', ['consultation' => $consultation])
        @else
            @include('consultation_print.case_sheet', ['consultation' => $consultation, 'history' => $history])
        @endif
    </div>

    <footer class="page-footer">
        <p><strong>4th Floor, The Medical Centre, HITEC City</strong></p>
        <p>Survey No. 64, Huda Techno Park, Phase 2, Hyderabad - 500081</p>
        <p>Ph: 9392585050</p>
        <img src="{{ asset('storage/app/public/plus-icons.png') }}" alt="Edge Clinic icon">
    </footer>
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
