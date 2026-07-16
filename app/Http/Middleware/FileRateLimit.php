<?php

namespace App\Http\Middleware;

use App\Repositories\JsonFileRepository;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FileRateLimit
{
    public function __construct(private readonly JsonFileRepository $files)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('api/contact') || ! $request->isMethod('POST')) {
            return $next($request);
        }

        $path = storage_path('app/rate-limit/contact.json');
        $limit = (int) env('CONTACT_RATE_LIMIT_MAX', 5);
        $decay = (int) env('CONTACT_RATE_LIMIT_DECAY_SECONDS', 300);
        $key = sha1((string) $request->ip());
        $now = time();
        $data = $this->files->read($path);
        $entry = $data[$key] ?? ['count' => 0, 'reset_at' => $now + $decay];

        if (($entry['reset_at'] ?? 0) <= $now) {
            $entry = ['count' => 0, 'reset_at' => $now + $decay];
        }

        if (($entry['count'] ?? 0) >= $limit) {
            return response()->json([
                'message' => 'Too many requests.',
                'retry_after' => max(1, ($entry['reset_at'] ?? $now) - $now),
            ], 429);
        }

        $entry['count'] = (int) $entry['count'] + 1;
        $data[$key] = $entry;
        $this->files->write($path, $data);

        return $next($request);
    }
}
