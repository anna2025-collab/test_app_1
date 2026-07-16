<!doctype html>
<html lang="en">
<body>
<h1>New contact request</h1>
<p><strong>Name:</strong> {{ $contact->name }}</p>
<p><strong>Phone:</strong> {{ $contact->phone }}</p>
<p><strong>Email:</strong> {{ $contact->email }}</p>
<p><strong>Comment:</strong></p>
<p>{{ $contact->comment }}</p>
<p><strong>AI sentiment:</strong> {{ $contact->ai_sentiment }}</p>
<p><strong>AI category:</strong> {{ $contact->ai_category }}</p>
<p><strong>AI fallback used:</strong> {{ $contact->ai_available ? 'no' : 'yes' }}</p>
</body>
</html>
