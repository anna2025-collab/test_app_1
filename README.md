# Backend-сервис для лендинга разработчика

Laravel-проект для лендинг-презентации разработчика с REST API, хранением обращений в MySQL, email-уведомлениями, логированием запросов в JSONL, файловым rate limiting, метриками в JSON, AI-анализом через Google Gemini/OpenAI с graceful fallback, Blade-фронтом и OpenAPI-документацией.
Рабочий API

Проект развернут на Railway:

https://testapp1-production-ba44.up.railway.app

Swagger/OpenAPI документация:

https://testapp1-production-ba44.up.railway.app/docs


## Стек технологий

- Backend: PHP 8.3, Laravel 13
- База данных: MySQL для хранения обращений
- Файловое хранение: JSON/JSONL для rate limiting, метрик и логов запросов
- Frontend: Blade, CSS, Fetch API
- Email: Laravel Mail
- AI: Google Gemini API или OpenAI Responses API через Laravel HTTP client
- Документация: OpenAPI 3.0 и Swagger UI

## Запуск проекта

Установить зависимости:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Создать базу MySQL:

```sql
CREATE DATABASE developer_landing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Настроить подключение к базе данных и сервисам в `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=developer_landing
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=log
MAIL_FROM_ADDRESS=hello@example.com
CONTACT_OWNER_EMAIL=owner@example.com

AI_PROVIDER=gemini

OPENAI_API_KEY=
OPENAI_MODEL=gpt-4.1-mini
OPENAI_TIMEOUT=8

GEMINI_API_KEY=
GEMINI_MODEL=gemini-3.1-flash-lite
GEMINI_TIMEOUT=8

CONTACT_RATE_LIMIT_MAX=5
CONTACT_RATE_LIMIT_DECAY_SECONDS=300
CORS_ALLOWED_ORIGINS=*
```

Запустить миграции и локальный сервер:

```bash
php artisan migrate
php artisan serve
```

Фронтенд: `http://127.0.0.1:8000`

Swagger UI: `http://127.0.0.1:8000/docs`

OpenAPI-файл: `http://127.0.0.1:8000/openapi.yaml`

## Архитектура

Проект построен по слоистой архитектуре:

- Controllers: принимают HTTP-запросы и возвращают API-ответы
- Form Requests: валидируют входящие данные
- Services: выполняют бизнес-логику обработки обращения, AI и метрик
- Repositories: изолируют работу с MySQL и JSON-файлами
- Mailables: формируют письма владельцу сайта и пользователю
- Middleware: CORS, логирование запросов и файловый rate limiting

Laravel выбран потому, что он дает готовую инфраструктуру для API, валидации, миграций, mail, middleware и конфигурации через `.env`. Слои разделены так, чтобы контроллер не содержал бизнес-логику, а хранение данных было вынесено в репозитории.

## API

### POST `/api/contact`

Полный цикл обработки:

`запрос -> валидация -> санитизация -> AI-анализ -> сохранение в MySQL -> письмо владельцу -> письмо пользователю -> JSON-ответ`

Пример запроса:

```bash
curl -X POST http://127.0.0.1:8000/api/contact \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Иван Петров",
    "phone": "+79990000000",
    "email": "ivan@example.com",
    "comment": "Нужен backend на Laravel для лендинга."
  }'
```

Успешный ответ, `201`:

```json
{
  "message": "Обращение принято.",
  "data": {
    "id": 1,
    "ai": {
      "provider": "gemini",
      "available": false,
      "sentiment": "neutral",
      "category": "other",
      "auto_reply": "Спасибо за обращение. Я получил ваше сообщение и скоро свяжусь с вами."
    }
  }
}
```

Ошибка валидации, `422`:

```json
{
  "message": "Ошибка валидации.",
  "errors": {
    "email": ["Email должен быть корректным."]
  }
}
```

Ошибка rate limiting, `429`:

```json
{
  "message": "Слишком много запросов.",
  "retry_after": 120
}
```

Ошибка обработки, `500`:

```json
{
  "message": "Не удалось обработать обращение."
}
```

### GET `/api/health`

```bash
curl http://127.0.0.1:8000/api/health
```

Возвращает статус сервиса, доступность базы данных, возможность записи в `storage/app` и timestamp.

### GET `/api/metrics`

```bash
curl http://127.0.0.1:8000/api/metrics
```

Возвращает счетчики из `storage/app/metrics/contact.json` и количество обращений из MySQL, если база данных доступна.

## Валидация и обработка ошибок

Поля формы обратной связи:

- `name`: обязательная строка, 2-100 символов
- `phone`: обязательная строка в формате телефона
- `email`: обязательный валидный email, максимум 255 символов
- `comment`: обязательная строка, 10-2000 символов

Ошибки API возвращаются в JSON. Глобальная обработка исключений настроена в `bootstrap/app.php`:

- `422` - ошибка валидации
- `429` - превышен rate limit
- `500` - ошибка обработки обращения

## AI-интеграция

AI-логика находится в `app/Services/Ai`.

Backend отправляет данные обращения выбранному AI-провайдеру и просит вернуть структурированный JSON:

- `sentiment`: тональность обращения - `positive`, `neutral` или `negative`
- `category`: тип обращения - `project_request`, `support`, `partnership`, `hiring` или `other`
- `auto_reply`: короткий автоматический ответ пользователю

Основной провайдер выбирается через `.env`:

```dotenv
AI_PROVIDER=gemini
GEMINI_API_KEY=your_gemini_key
GEMINI_MODEL=gemini-3.1-flash-lite
```

Для OpenAI можно переключить:

```dotenv
AI_PROVIDER=openai
OPENAI_API_KEY=your_openai_key
OPENAI_MODEL=gpt-4.1-mini
```

Gemini API key создается в Google AI Studio: `https://aistudio.google.com/app/apikey`.

Используемый prompt для Gemini:

```text
Проанализируй обращение с формы обратной связи.
Верни только JSON без markdown и пояснений.
```

Fallback реализован в AI-анализаторах. Если `GEMINI_API_KEY`/`OPENAI_API_KEY` не задан, провайдер недоступен, произошел timeout или API вернул невалидный JSON, сервис продолжает работу и использует значения по умолчанию:

- `available`: `false`
- `sentiment`: `neutral`
- `category`: `other`
- стандартный текст автоответа

После AI-анализа результат сохраняется в MySQL вместе с обращением и используется в письме пользователю.

## Email-уведомления

После успешного сохранения обращения отправляются два письма:

- владельцу сайта на `CONTACT_OWNER_EMAIL`
- пользователю на email из формы

Классы писем:

- `app/Mail/OwnerContactMail.php`
- `app/Mail/UserContactCopyMail.php`

Шаблоны писем:

- `resources/views/emails/contact-owner.blade.php`
- `resources/views/emails/contact-user-copy.blade.php`

По умолчанию используется `MAIL_MAILER=log`, поэтому письма пишутся в `storage/logs/laravel.log`. Для реальной отправки нужно настроить SMTP-параметры в `.env`.

## Хранение данных

MySQL:

- таблица `contact_requests` хранит данные формы и результат AI-анализа

JSON-файлы:

- `storage/app/rate-limit/contact.json` - счетчики запросов по hash IP
- `storage/app/metrics/contact.json` - статистика обращений и AI
- `storage/app/logs/api-requests.jsonl` - лог всех API-запросов, по одному JSON-объекту на строку

Laravel-логи:

- `storage/logs/laravel.log` - ошибки приложения и email-логи при `MAIL_MAILER=log`

## Что сделано с помощью AI

AI использовался для подготовки начальной реализации проекта:

- слоистая структура Laravel-проекта
- контроллеры, сервисы, репозитории и middleware
- интеграция Gemini/OpenAI с fallback-логикой
- OpenAPI-документация
- README
- Blade-форма обратной связи

Что было исправлено и проверено вручную:

- цикл обработки обращения приведен к требованию ТЗ
- Gemini-запрос реализован через официальный REST endpoint `generateContent`
- OpenAI-запрос оставлен как альтернативный провайдер через Responses API
- хранение разделено по ТЗ: MySQL для обращений, JSON для rate limiting, метрик и логов
- email-шаблоны и темы писем переведены на русский язык
- OpenAPI-документация и AI prompt переведены на русский язык

## Деплой

### Railway

Проект подготовлен для Railway через `railway.toml`:

- Railway использует Railpack для сборки Laravel-приложения
- перед запуском выполняется `php artisan migrate --force`
- healthcheck настроен на `/api/health`

Шаги:

1. Зайти на `https://railway.com`.
2. Создать `New Project`.
3. Выбрать `Deploy from GitHub repo`.
4. Выбрать репозиторий `anna2025-collab/test_app_1`.
5. Добавить MySQL: `+ New` -> `Database` -> `MySQL`.
6. В сервисе Laravel открыть `Variables` и добавить:

```dotenv
APP_NAME=Лендинг разработчика
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://your-railway-domain.up.railway.app

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

MAIL_MAILER=log
MAIL_FROM_ADDRESS=hello@example.com
MAIL_FROM_NAME=Лендинг разработчика
CONTACT_OWNER_EMAIL=owner@example.com

AI_PROVIDER=gemini
GEMINI_API_KEY=your_gemini_key
GEMINI_MODEL=gemini-3.1-flash-lite
GEMINI_TIMEOUT=8

CONTACT_RATE_LIMIT_MAX=5
CONTACT_RATE_LIMIT_DECAY_SECONDS=300
CORS_ALLOWED_ORIGINS=*
```

`APP_KEY` можно взять локально командой:

```bash
php artisan key:generate --show
```

7. В `Settings` -> `Networking` нажать `Generate Domain`.
8. После деплоя проверить:

```text
https://your-railway-domain.up.railway.app/api/health
https://your-railway-domain.up.railway.app/docs
```

### Другой сервер

Команды для подготовки проекта на обычном сервере:

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan config:cache
php artisan route:cache
```

Document root веб-сервера должен указывать на директорию `public/`.

Если деплой невозможен, проект можно проверить локально по инструкции из раздела запуска.
