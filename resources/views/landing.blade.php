<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Backend-разработчик</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #172026;
            --muted: #60707a;
            --line: #d7e0e5;
            --surface: #ffffff;
            --bg: #f5f7f4;
            --accent: #0f766e;
            --accent-dark: #115e59;
            --warn: #9a3412;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            color: var(--ink);
            background: var(--bg);
        }

        main {
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(360px, 460px);
            gap: 40px;
            align-items: center;
            max-width: 1120px;
            padding: 48px 24px;
            margin: 0 auto;
        }

        .intro {
            display: grid;
            gap: 24px;
        }

        h1 {
            margin: 0;
            font-size: 52px;
            line-height: 1.02;
            letter-spacing: 0;
        }

        p {
            margin: 0;
            color: var(--muted);
            font-size: 18px;
            line-height: 1.6;
            max-width: 620px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            max-width: 620px;
        }

        .stat {
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.65);
            padding: 18px;
            border-radius: 8px;
        }

        .stat strong {
            display: block;
            font-size: 24px;
        }

        .stat span {
            display: block;
            margin-top: 4px;
            color: var(--muted);
            font-size: 14px;
        }

        form {
            display: grid;
            gap: 14px;
            padding: 24px;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: 0 16px 40px rgba(23, 32, 38, 0.08);
        }

        label {
            display: grid;
            gap: 6px;
            font-size: 14px;
            color: var(--ink);
        }

        input,
        textarea {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 12px 14px;
            font: inherit;
            color: var(--ink);
            background: #fff;
        }

        textarea {
            min-height: 140px;
            resize: vertical;
        }

        button {
            border: 0;
            border-radius: 6px;
            padding: 13px 16px;
            color: #fff;
            background: var(--accent);
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }

        button:disabled {
            opacity: 0.7;
            cursor: wait;
        }

        .message {
            display: none;
            padding: 12px 14px;
            border-radius: 6px;
            font-size: 14px;
            line-height: 1.45;
        }

        .message.success {
            display: block;
            color: var(--accent-dark);
            background: #dff3ef;
        }

        .message.error {
            display: block;
            color: var(--warn);
            background: #ffedd5;
        }

        @media (max-width: 840px) {
            main {
                grid-template-columns: 1fr;
                padding: 32px 18px;
            }

            h1 {
                font-size: 38px;
            }

            .stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<main>
    <section class="intro" aria-label="Презентация разработчика">
        <h1>Backend-разработчик</h1>
        <p>Проектирую API на Laravel с валидацией, хранением данных, email-уведомлениями, логированием запросов, rate limiting и AI-обработкой обращений.</p>
        <div class="stats" aria-label="Основной стек">
            <div class="stat">
                <strong>PHP</strong>
                <span>Backend на Laravel</span>
            </div>
            <div class="stat">
                <strong>MySQL</strong>
                <span>Хранение обращений</span>
            </div>
            <div class="stat">
                <strong>ИИ</strong>
                <span>OpenAI и fallback</span>
            </div>
        </div>
    </section>

    <form id="contact-form">
        <label>
            Имя
            <input name="name" autocomplete="name" required minlength="2" maxlength="100">
        </label>
        <label>
            Телефон
            <input name="phone" autocomplete="tel" required>
        </label>
        <label>
            Email
            <input name="email" type="email" autocomplete="email" required maxlength="255">
        </label>
        <label>
            Комментарий
            <textarea name="comment" required minlength="10" maxlength="2000"></textarea>
        </label>
        <button type="submit">Отправить обращение</button>
        <div class="message" id="form-message" role="status"></div>
    </form>
</main>

<script>
    const form = document.querySelector('#contact-form');
    const message = document.querySelector('#form-message');
    const button = form.querySelector('button');

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        button.disabled = true;
        message.className = 'message';
        message.textContent = '';

        const payload = Object.fromEntries(new FormData(form).entries());

        try {
            const response = await fetch('/api/contact', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (!response.ok) {
                const errors = data.errors ? Object.values(data.errors).flat().join(' ') : data.message;
                throw new Error(errors || 'Не удалось отправить обращение.');
            }

            message.className = 'message success';
            message.textContent = data.data.ai.auto_reply;
            form.reset();
        } catch (error) {
            message.className = 'message error';
            message.textContent = error.message;
        } finally {
            button.disabled = false;
        }
    });
</script>
</body>
</html>
