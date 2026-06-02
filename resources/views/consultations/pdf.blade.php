<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Consultation PDF</title>
</head>
<body>
    @if(($printMode ?? 'case_sheet') === 'case_sheet')
        @include('consultation_print.case_sheet_document', [
            'consultation' => $consultation,
            'autoPrint' => false,
        ])
    @else
        @include('consultation_print.document', [
            'consultation' => $consultation,
            'printMode' => $printMode ?? 'case_sheet',
            'autoPrint' => false,
        ])
    @endif
</body>
</html>
