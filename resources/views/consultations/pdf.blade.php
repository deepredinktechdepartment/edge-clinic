<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Consultation PDF</title>
</head>
<body>
    @include('consultation_print.document', [
        'consultation' => $consultation,
        'printMode' => $printMode ?? 'case_sheet',
        'autoPrint' => false,
    ])
</body>
</html>
