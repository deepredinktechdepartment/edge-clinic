@php
    $history = $consultation->patient->consultationHistory;
    $isPrescription = ($printMode ?? 'case_sheet') === 'prescription';
@endphp
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    @page { size: A4; margin: 12mm; }
    body { font-family: Arial, sans-serif; font-size: 12px; color: #273341; background: #fff; }
    .letterhead { width: 100%; max-width: 186mm; margin: 0 auto; display: flex; flex-direction: column; gap: 8px; min-height: 270mm; }
    .header { text-align: center; padding: 6px 0 10px; border-bottom: 1px solid #666; }
    .header img { width: 72px; }
    .doc-title { text-align: center; font-size: 20px; font-weight: 700; color: #2f7aa9; margin-top: 4px; }
    .doc-subtitle { text-align: center; color: #66788a; font-size: 11px; margin-top: 2px; }
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
    .footer { text-align: center; border-top: 1px solid #000; padding: 10px 0 0; font-size: 11px; display: flex; gap: 20px; justify-content: center; align-items: center; margin-top: auto; }
    .footer img { width: 40px; }
    .casesheet-page { display: flex; flex-direction: column; flex: 1; }
    .casesheet-simple { border: none; border-radius: 0; padding: 0 0 8px; margin-bottom: 6px; }
    .casesheet-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px 20px; font-size: 11px; }
    .casesheet-vitals { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 8px 18px; padding-top: 10px; border-top: 1px solid #bbb; border-bottom: 1px solid #bbb; padding-bottom: 10px; }
    .casesheet-field-label { font-size: 10px; font-weight: 700; color: #333; }
    .casesheet-field-value { min-height: 16px; color: #222; }
    .casesheet-blank { flex: 1; position: relative; min-height: 150mm; max-height: 165mm; }
    .casesheet-visit-note { position: absolute; right: 8px; bottom: 8px; font-size: 10px; color: #444; }
    @media print {
        .no-print { display: none !important; }
    }
    @media screen {
        body { padding: 18px 0; background: #f5f7fa; }
        .letterhead { background: #fff; padding: 16px; box-shadow: 0 8px 30px rgba(16, 24, 40, 0.08); }
    }
</style>

<div class="letterhead">
    <header class="header">
        <img src="{{ asset('storage/app/public/edge_logo.png') }}" alt="Edge Clinic">
        <div class="doc-title">{{ $isPrescription ? 'Prescription' : 'Case Sheet' }}</div>
        <div class="doc-subtitle">Edge Clinic</div>
    </header>

    @if($isPrescription)
        @include('consultation_print.prescription', ['consultation' => $consultation])
    @else
        @include('consultation_print.case_sheet', ['consultation' => $consultation, 'history' => $history])
    @endif

    <footer class="footer">
        <div>
            <p><strong>4th Floor, The Medical Centre, HITEC City</strong></p>
            <p>Survey No. 64, Huda Techno Park, Phase 2, Hyderabad - 500081</p>
            <p>Ph: 9392585050</p>
        </div>
        <div>
            <img src="{{ asset('storage/app/public/plus-icons.png') }}" alt="Edge Clinic icon">
        </div>
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
