<?php
declare(strict_types=1);

namespace App\Controllers;

use PDO;

/** Métricas reales para el back office. */
final class AdminController
{
    public function __construct(private PDO $pdo) {}

    public function metrics(): array
    {
        return [
            'total'         => $this->scalar('SELECT COUNT(*) FROM recipes'),
            'today'         => $this->scalar("SELECT COUNT(*) FROM recipes WHERE DATE(created_at) = CURDATE()"),
            'errors24h'     => $this->scalar("SELECT COUNT(*) FROM event_logs WHERE level = 'error' AND created_at >= (NOW() - INTERVAL 24 HOUR)"),
            'totalViews'    => $this->scalar('SELECT COALESCE(SUM(views), 0) FROM recipes'),
            'top'           => $this->rows('SELECT id, slug, title, views FROM recipes ORDER BY views DESC, created_at DESC LIMIT 5'),
            'recent'        => $this->rows('SELECT id, slug, title, created_at FROM recipes ORDER BY created_at DESC LIMIT 6'),
            'recentErrors'  => $this->rows("SELECT source, message, created_at FROM event_logs WHERE level = 'error' ORDER BY created_at DESC LIMIT 8"),
        ];
    }

    private function scalar(string $sql): int
    {
        $value = $this->pdo->query($sql)->fetchColumn();
        return (int) $value;
    }

    private function rows(string $sql): array
    {
        return $this->pdo->query($sql)->fetchAll();
    }
}
