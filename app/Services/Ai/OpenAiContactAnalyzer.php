<?php

namespace App\Services\Ai;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Throwable;

class OpenAiContactAnalyzer
{
    public function analyze(array $contact): array
    {
        $apiKey = config('services.openai.api_key');

        if (! $apiKey) {
            return $this->fallback('OPENAI_API_KEY is not configured.');
        }

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->asJson()
                ->timeout(config('services.openai.timeout'))
                ->post('https://api.openai.com/v1/responses', [
                    'model' => config('services.openai.model'),
                    'input' => [
                        [
                            'role' => 'system',
                            'content' => 'Analyze a website contact form request. Return only JSON matching the schema.',
                        ],
                        [
                            'role' => 'user',
                            'content' => sprintf(
                                "Name: %s\nPhone: %s\nEmail: %s\nComment: %s",
                                $contact['name'],
                                $contact['phone'],
                                $contact['email'],
                                $contact['comment'],
                            ),
                        ],
                    ],
                    'text' => [
                        'format' => [
                            'type' => 'json_schema',
                            'name' => 'contact_analysis',
                            'strict' => true,
                            'schema' => [
                                'type' => 'object',
                                'additionalProperties' => false,
                                'required' => ['sentiment', 'category', 'auto_reply'],
                                'properties' => [
                                    'sentiment' => [
                                        'type' => 'string',
                                        'enum' => ['positive', 'neutral', 'negative'],
                                    ],
                                    'category' => [
                                        'type' => 'string',
                                        'enum' => ['project_request', 'support', 'partnership', 'hiring', 'other'],
                                    ],
                                    'auto_reply' => [
                                        'type' => 'string',
                                        'maxLength' => 600,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'max_output_tokens' => 400,
                ]);

            if (! $response->successful()) {
                return $this->fallback('OpenAI request failed with status '.$response->status().'.');
            }

            $text = $this->extractText($response->json());
            $analysis = json_decode($text, true);

            if (! is_array($analysis)) {
                return $this->fallback('OpenAI returned non-JSON content.');
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

    private function extractText(array $payload): string
    {
        if (is_string($payload['output_text'] ?? null)) {
            return $payload['output_text'];
        }

        foreach (($payload['output'] ?? []) as $item) {
            foreach (($item['content'] ?? []) as $content) {
                $text = Arr::get($content, 'text');

                if (is_string($text)) {
                    return $text;
                }
            }
        }

        return '';
    }

    private function fallback(string $error): array
    {
        return [
            'available' => false,
            'sentiment' => 'neutral',
            'category' => 'other',
            'auto_reply' => $this->defaultReply(),
            'error' => $error,
        ];
    }

    private function defaultReply(): string
    {
        return 'Спасибо за обращение. Я получил ваше сообщение и скоро свяжусь с вами.';
    }
}
