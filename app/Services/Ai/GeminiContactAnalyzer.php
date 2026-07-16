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
                ->post(sprintf(
                    'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent',
                    config('services.gemini.model'),
                ), [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $this->prompt($contact)],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                    ],
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
        foreach (($payload['candidates'] ?? []) as $candidate) {
            foreach (Arr::get($candidate, 'content.parts', []) as $part) {
                $text = Arr::get($part, 'text');

                if (is_string($text)) {
                    return trim($text);
                }
            }
        }

        return '';
    }
}
