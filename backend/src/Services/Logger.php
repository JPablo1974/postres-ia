<?php
declare(strict_types=1);

namespace App\Services;

use PDO;
use Throwable;

/** Registra eventos y errores en la tabla event_logs y en logs/app.log */
final class Logger
{
    public function __construct(private PDO $pdo) {}

    public function log(string $level, string $source, string $message, array $context = []): void
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO event_logs (level, source, message, context) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([
                $level,
                $source,
                $message,
                $context ? json_encode($context, JSON_UNESCAPED_UNICODE) : null,
            ]);
        } catch (Throwable) {
            // El logging nunca debe romper la petición.
        }

        $line = sprintf("[%s] %s.%s: %s\n", date('c'), strtoupper($level), $source, $message);
        @file_put_contents(__DIR__ . '/../../logs/app.log', $line, FILE_APPEND);
    }

    public function info(string $source, string $message, array $context = []): void
    {
        $this->log('info', $source, $message, $context);
    }

    public function warning(string $source, string $message, array $context = []): void
    {
        $this->log('warning', $source, $message, $context);
    }

    public function error(string $source, string $message, array $context = []): void
    {
        $this->log('error', $source, $message, $context);
    }
}
