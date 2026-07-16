<?php
declare(strict_types=1);

use App\Config\Config;
use App\Config\Database;
use App\Core\Router;
use App\Core\Request;
use App\Core\Response;
use App\Middleware\Cors;
use App\Services\Logger;
use App\Services\OpenAIService;
use App\Models\Recipe;
use App\Controllers\RecipeController;

// --- Autoloader PSR-4 (sin necesidad de Composer) ---
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/../src/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

Config::load(__DIR__ . '/../.env');

// Normalizar el path del VirtualHost (si está configurado)
Request::setBasePath(Config::get('API_BASE_PATH', ''));
$request = new Request();

// CORS (maneja el preflight OPTIONS y termina si aplica)
Cors::handle(Config::get('CORS_ALLOWED_ORIGINS', ''));

// Health check: responde sin tocar la base de datos (para verificar el servidor)
if ($request->path === '/api/health') {
    Response::json(['ok' => true, 'time' => date('c')]);
    exit;
}

// Servir el admin del back office (path original sin normalizar)
$originalPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (str_starts_with($originalPath, '/api-postres-ai/admin')) {
    $adminPath = __DIR__ . '/../admin';
    // Servir assets del admin
    if (str_contains($originalPath, '/admin/assets/')) {
        $assetFile = $adminPath . substr($originalPath, strlen('/api-postres-ai'));
        if (is_file($assetFile)) {
            $mime = mime_content_type($assetFile);
            header("Content-Type: $mime");
            readfile($assetFile);
            exit;
        }
    }
    // Servir login.php
    if (str_ends_with($originalPath, '/admin/login') || str_ends_with($originalPath, '/admin/login.php')) {
        if (is_file($adminPath . '/login.php')) {
            include $adminPath . '/login.php';
            exit;
        }
    }
    // Servir logout.php
    if (str_ends_with($originalPath, '/admin/logout') || str_ends_with($originalPath, '/admin/logout.php')) {
        if (is_file($adminPath . '/logout.php')) {
            include $adminPath . '/logout.php';
            exit;
        }
    }
    // Servir index.php del admin
    if (str_ends_with($originalPath, '/admin') || str_ends_with($originalPath, '/admin/') || str_ends_with($originalPath, '/admin/index.php')) {
        if (is_file($adminPath . '/index.php')) {
            chdir($adminPath);
            include $adminPath . '/index.php';
            exit;
        }
    }
}

$logger = null;
try {
    $pdo = Database::connection();
    $logger = new Logger($pdo);

    $recipes = new Recipe($pdo);
    $openai = new OpenAIService(
        Config::get('OPENAI_API_KEY', ''),
        Config::get('OPENAI_MODEL', 'gpt-4o-mini'),
        $logger
    );
    $recipeController = new RecipeController($recipes, $openai, $logger);

    $router = new Router();
    $router->post('/api/recipes/generate', [$recipeController, 'generate']);
    $router->get('/api/recipes', [$recipeController, 'index']);
    $router->get('/api/recipes/{id}', [$recipeController, 'show']);

    $router->dispatch($request);
} catch (\Throwable $e) {
    if ($logger) {
        $logger->error('api', $e->getMessage(), ['type' => get_class($e)]);
    }
    Response::json(['message' => 'Error interno del servidor.'], 500);
}
