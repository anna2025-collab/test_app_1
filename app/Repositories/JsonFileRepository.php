<?php

namespace App\Repositories;

class JsonFileRepository
{
    public function read(string $path, array $default = []): array
    {
        if (! file_exists($path)) {
            return $default;
        }

        $content = file_get_contents($path);
        $data = $content === false ? null : json_decode($content, true);

        return is_array($data) ? $data : $default;
    }

    public function write(string $path, array $data): void
    {
        $this->ensureDirectory($path);

        $handle = fopen($path, 'c+');

        if ($handle === false) {
            throw new \RuntimeException("Cannot open JSON file: {$path}");
        }

        try {
            flock($handle, LOCK_EX);
            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    public function appendJsonLine(string $path, array $data): void
    {
        $this->ensureDirectory($path);

        $line = json_encode($data, JSON_UNESCAPED_UNICODE).PHP_EOL;
        file_put_contents($path, $line, FILE_APPEND | LOCK_EX);
    }

    private function ensureDirectory(string $path): void
    {
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
    }
}
