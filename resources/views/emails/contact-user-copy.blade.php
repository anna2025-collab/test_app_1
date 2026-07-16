<!doctype html>
<html lang="ru">
<body>
<h1>Ваше обращение получено</h1>
<p>Здравствуйте, {{ $contact->name }}.</p>
<p>{{ $contact->ai_auto_reply }}</p>
<p>Ваше сообщение:</p>
<p>{{ $contact->comment }}</p>
</body>
</html>
