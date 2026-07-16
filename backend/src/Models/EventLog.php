<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

final class EventLog
{
    public function __construct(private PDO $pdo) {}

    /** @return array<int,array> */
    public function recent(string $level = 'error', int $limit = 8): array
    {
        $limit = max(1, min($limit, 50));
        $stmt = $this->pdo->prepare(
            'SELECT source, message, created_at FROM event_logs WHERE level = :level
             ORDER BY created_at DESC LIMIT :lim'
        );
        $stmt->bindValue(':level', $level);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countSince(string $level, string $interval = '24 HOUR'): int
    {
        $stmt = $this->pdo->query(
            "SELECT COUNT(*) AS c FROM event_logs
             WHERE level = " . $this->pdo->quote($level) . "
             AND created_at >= (NOW() - INTERVAL {$interval})"
        );
        return (int) ($stmt->fetch()['c'] ?? 0);
    }
}
