<?php

namespace App\Services\Ai;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Throwable;

class GeminiContactAnalyzer implements ContactAnalyzer
{
    use ContactAnalysisFallback;

    public function analyze(array $contact): array
    {
        $apiKey = config('services.gemini.api_key');

        if (! $apiKey) {
            return $this->fallback('GEMINI_API_KEY не настроен.');
        }

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->timeout(config('services.gemini.timeout'))
                ->withHeaders([
                    'x-goog-api-key' => $apiKey,
                ])
                ->post('https://generativelanguage.googleapis.com/v1beta/interactions', [
                    'model' => config('services.gemini.model'),
                    'input' => $this->prompt($contact),
                    'store' => false,
                ]);

            if (! $response->successful()) {
                return $this->fallback('Запрос Gemini завершился ошибкой со статусом '.$response->status().'.');
            }

            $analysis = json_decode($this->extractText($response->json()), true);

            if (! is_array($analysis)) {
                return $this->fallback('Gemini вернул невалидный JSON.');
            }

            return [
                'available' => true,
                'sentiment' => $analysis['sentiment'] ?? 'neutral',
                'category' => $analysis['category'] ?? 'other',
                'auto_reply' => $analysis['auto_reply'] ?? $this->defaultReply(),
                'error' => null,
            ];
        } catch (Throwable $exception) {
            return $this->fallback($exception->getMessage());
        }
    }

    private function prompt(array $contact): string
    {
        return sprintf(
            <<<'PROMPT'
Проанализируй обращение с формы обратной связи.
Верни только JSON без markdown и пояснений.

Схема:
{
  "sentiment": "positive|neutral|negative",
  "category": "project_request|support|partnership|hiring|other",
  "auto_reply": "короткий ответ пользователю на русском языке"
}

Имя: %s
Телефон: %s
Email: %s
Комментарий: %s
PROMPT,
            $contact['name'],
            $contact['phone'],
            $contact['email'],
            $contact['comment'],
        );
    }

    private function extractText(array $payload): string
    {
        if (is_string($payload['output_text'] ?? null)) {
            return $payload['output_text'];
        }

        foreach (($payload['steps'] ?? []) as $step) {
            foreach (($step['content'] ?? []) as $content) {
                $text = Arr::get($content, 'text');

                if (is_string($text)) {
                    return trim($text);
                }
            }
        }

        return '';
    }
}
