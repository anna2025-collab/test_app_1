<!doctype html>
<html lang="en">
<body>
<h1>Your request was received</h1>
<p>Hello, {{ $contact->name }}.</p>
<p>{{ $contact->ai_auto_reply }}</p>
<p>Your message:</p>
<p>{{ $contact->comment }}</p>
</body>
</html>
