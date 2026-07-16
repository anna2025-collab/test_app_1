<?php

namespace App\Http\Middleware;

use App\Repositories\JsonFileRepository;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiRequestLogger
{
    public function __construct(private readonly JsonFileRepository $files)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $started = microtime(true);
        $response = $next($request);

        $this->files->appendJsonLine(storage_path('app/logs/api-requests.jsonl'), [
            'time' => now()->toIso8601String(),
            'method' => $request->method(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'status' => $response->getStatusCode(),
            'duration_ms' => round((microtime(true) - $started) * 1000, 2),
            'payload' => $request->except(['password', 'token']),
        ]);

        return $response;
    }
}
