<?php
declare(strict_types=1);

namespace App\Middleware;

final class Cors
{
    public static function handle(string $allowed): void
    {
        $origins = array_filter(array_map('trim', explode(',', $allowed)));
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if ($origin !== '' && (in_array('*', $origins, true) || in_array($origin, $origins, true))) {
            header("Access-Control-Allow-Origin: {$origin}");
            header('Vary: Origin');
        } elseif (in_array('*', $origins, true)) {
            header('Access-Control-Allow-Origin: *');
        }

        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Max-Age: 86400');

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }
}
