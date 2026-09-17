<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Feedback | Edge Clinic</title></head>
<body style="font-family:Arial,sans-serif;max-width:620px;margin:40px auto;padding:0 20px;color:#1f2937">
    <h1>How was your visit?</h1>
    <p>Please rate your visit with Dr. {{ preg_replace('/^Dr\.\s*/i', '', $payment->doctor?->name ?? 'your doctor') }}.</p>
    <form method="post" action="{{ route('feedback.store', ['payment' => $payment->id, 'expires' => request('expires'), 'signature' => request('signature')]) }}">
        @csrf
        <label for="score">Rating (0 to 10)</label><br>
        <select id="score" name="score" required style="width:100%;padding:10px;margin:8px 0 18px">
            <option value="">Choose a rating</option>
            @for ($score = 0; $score <= 10; $score++)<option value="{{ $score }}">{{ $score }}</option>@endfor
        </select>
        <label for="comment">Comments (optional)</label><br>
        <textarea id="comment" name="comment" rows="5" style="width:100%;padding:10px;margin:8px 0 18px"></textarea>
        <button type="submit" style="background:#0b6e99;color:#fff;border:0;padding:12px 20px;border-radius:4px">Submit feedback</button>
    </form>
</body>
</html>
