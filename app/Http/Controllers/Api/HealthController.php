<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $database = true;

        try {
            DB::select('select 1');
        } catch (Throwable) {
            $database = false;
        }

        return response()->json([
            'status' => $database ? 'ok' : 'degraded',
            'database' => $database,
            'storage_writable' => is_writable(storage_path('app')),
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
