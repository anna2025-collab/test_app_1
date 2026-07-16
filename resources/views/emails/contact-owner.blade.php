<!doctype html>
<html lang="ru">
<body>
<h1>Новое обращение с сайта</h1>
<p><strong>Имя:</strong> {{ $contact->name }}</p>
<p><strong>Телефон:</strong> {{ $contact->phone }}</p>
<p><strong>Email:</strong> {{ $contact->email }}</p>
<p><strong>Комментарий:</strong></p>
<p>{{ $contact->comment }}</p>
<p><strong>Тональность AI:</strong> {{ $contact->ai_sentiment }}</p>
<p><strong>Категория AI:</strong> {{ $contact->ai_category }}</p>
<p><strong>Fallback AI использован:</strong> {{ $contact->ai_available ? 'нет' : 'да' }}</p>
</body>
</html>
