<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequest;
use App\Services\ContactService;
use App\Services\MetricsService;
use Illuminate\Http\JsonResponse;
use Throwable;

class ContactController extends Controller
{
    public function __construct(
        private readonly ContactService $contacts,
        private readonly MetricsService $metrics,
    ) {}

    public function __invoke(StoreContactRequest $request): JsonResponse
    {
        try {
            $contact = $this->contacts->handle($request->validated());

            return response()->json([
                'message' => 'Обращение принято.',
                'data' => [
                    'id' => $contact->id,
                    'ai' => [
                        'provider' => 'gemini',
                        'available' => $contact->ai_available,
                        'sentiment' => $contact->ai_sentiment,
                        'category' => $contact->ai_category,
                        'auto_reply' => $contact->ai_auto_reply,
                    ],
                ],
            ], 201);
        } catch (Throwable $exception) {
            $this->metrics->increment('failed');

            report($exception);

            return response()->json([
                'message' => 'Не удалось обработать обращение.',
            ], 500);
        }
    }
}
