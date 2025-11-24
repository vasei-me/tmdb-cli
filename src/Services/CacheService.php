<?php
namespace App\Services;

class CacheService
{
    private string $dir;

    public function __construct(string $dir = null)
    {
        $this->dir = $dir ?? __DIR__ . '/../../cache';
        if (!is_dir($this->dir)) {
            @mkdir($this->dir, 0777, true);
        }
    }

    private function path(string $key): string
    {
        return rtrim($this->dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $key . '.json';
    }

    public function get(string $key)
    {
        $p = $this->path($key);
        if (!file_exists($p)) {
            return null;
        }

        $raw = @file_get_contents($p);
        if ($raw === false) return null;

        $data = @json_decode($raw, true);
        if (!is_array($data) || !isset($data['expires']) || !isset($data['payload'])) {
            return null;
        }

        if (time() > (int)$data['expires']) {
            @unlink($p);
            return null;
        }

        return $data['payload'];
    }

    public function set(string $key, $payload, int $ttl = 600): bool
    {
        $p = $this->path($key);
        $data = [
            'expires' => time() + $ttl,
            'payload' => $payload,
        ];
        return (bool)@file_put_contents($p, json_encode($data));
    }
}
