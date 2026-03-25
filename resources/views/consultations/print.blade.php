<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>{{ $pageTitle ?? 'Consultation Print' }}</title></head>
<body>
@include('consultations._print_content', ['consultation' => $consultation])
<script>window.print();</script>
</body>
</html>
