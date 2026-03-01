<?php
declare(strict_types=1);

namespace App\Log;

class Logger
{
    /** @var string */
    private $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
        $dir = dirname($filePath);
        if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
        if (!file_exists($filePath)) { @touch($filePath); }
    }

    public function error(string $message, array $context = []): void
    {
        $date = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $line = sprintf('[%s] ERROR: %s | context=%s%s',
            $date, $message, json_encode($context, JSON_UNESCAPED_UNICODE), PHP_EOL
        );
        @file_put_contents($this->filePath, $line, FILE_APPEND);
    }
}