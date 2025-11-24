<?php
namespace App\Services;

class Logger
{
    private string $path;

    public function __construct(string $path = null)
    {
        $this->path = $path ?? __DIR__ . '/../../logs/app.log';
        $dir = dirname($this->path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
    }

    public function log(string $level, string $message): void
    {
        $line = sprintf("[%s] %s: %s\n", date('c'), strtoupper($level), $message);
        @file_put_contents($this->path, $line, FILE_APPEND | LOCK_EX);
    }
}
