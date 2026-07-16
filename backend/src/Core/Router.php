<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<int,array{method:string,path:string,handler:callable}> */
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [
            'method'  => $method,
            'path'    => rtrim($path, '/') ?: '/',
            'handler' => $handler,
        ];
    }

    public function dispatch(Request $request): void
    {
        $methodMismatch = false;

        foreach ($this->routes as $route) {
            $params = $this->match($route['path'], $request->path);
            if ($params === null) {
                continue;
            }
            if ($route['method'] !== $request->method) {
                $methodMismatch = true;
                continue;
            }
            ($route['handler'])($request, $params);
            return;
        }

        Response::json(
            ['message' => $methodMismatch ? 'Método no permitido.' : 'Recurso no encontrado.'],
            $methodMismatch ? 405 : 404
        );
    }

    /** @return array<string,string>|null */
    private function match(string $pattern, string $path): ?array
    {
        $patternSegments = explode('/', trim($pattern, '/'));
        $pathSegments = explode('/', trim($path, '/'));

        if (count($patternSegments) !== count($pathSegments)) {
            return null;
        }

        $params = [];
        foreach ($patternSegments as $i => $segment) {
            if (preg_match('/^\{(\w+)\}$/', $segment, $m)) {
                $params[$m[1]] = urldecode($pathSegments[$i]);
            } elseif ($segment !== $pathSegments[$i]) {
                return null;
            }
        }
        return $params;
    }
}
