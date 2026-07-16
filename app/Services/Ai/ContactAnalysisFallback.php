<?php

namespace App\Services\Ai;

trait ContactAnalysisFallback
{
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
