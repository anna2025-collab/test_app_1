<?php

namespace App\Services;

use App\Repositories\JsonFileRepository;

class MetricsService
{
    private string $path;

    public function __construct(private readonly JsonFileRepository $files)
    {
        $this->path = storage_path('app/metrics/contact.json');
    }

    public function increment(string $key): void
    {
        $metrics = $this->all();
        $metrics[$key] = (int) ($metrics[$key] ?? 0) + 1;
        $metrics['updated_at'] = now()->toIso8601String();

        $this->files->write($this->path, $metrics);
    }

    public function all(): array
    {
        return $this->files->read($this->path, [
            'total' => 0,
            'successful' => 0,
            'failed' => 0,
            'ai_available' => 0,
            'ai_fallback' => 0,
            'updated_at' => null,
        ]);
    }
}
