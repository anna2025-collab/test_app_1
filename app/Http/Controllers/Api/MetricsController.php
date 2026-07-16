<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\ContactRepository;
use App\Services\MetricsService;
use Illuminate\Http\JsonResponse;
use Throwable;

class MetricsController extends Controller
{
    public function __construct(
        private readonly MetricsService $metrics,
        private readonly ContactRepository $contacts,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $metrics = $this->metrics->all();

        try {
            $metrics['stored_contacts'] = $this->contacts->count();
        } catch (Throwable) {
            $metrics['stored_contacts'] = null;
        }

        return response()->json([
            'data' => $metrics,
        ]);
    }
}
