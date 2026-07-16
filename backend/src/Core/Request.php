<?php
declare(strict_types=1);

namespace App\Core;

final class Request
{
    public string $method;
    public string $path;
    private ?array $body = null;
    private static ?string $basePath = null;

    public static function setBasePath(string $basePath): void
    {
        self::$basePath = $basePath;
    }

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        // Normalizar: quitar prefijo del VirtualHost si está configurado
        $basePath = self::$basePath ?? getenv('API_BASE_PATH_PATH') ?: '';
        if ($basePath !== '') {
            $basePath = rtrim(parse_url($basePath, PHP_URL_PATH) ?: '', '/');
            if ($basePath !== '' && str_starts_with($path, $basePath . '/')) {
                $path = substr($path, strlen($basePath));
            }
        }
        $this->path = rtrim($path, '/') ?: '/';
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public function json(): array
    {
        if ($this->body === null) {
            $raw = file_get_contents('php://input') ?: '';
            $decoded = json_decode($raw, true);
            $this->body = is_array($decoded) ? $decoded : [];
        }
        return $this->body;
    }
}
