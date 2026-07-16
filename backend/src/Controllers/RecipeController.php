<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Config;
use App\Core\Request;
use App\Core\Response;
use App\Models\Recipe;
use App\Services\Logger;
use App\Services\OpenAIService;
use RuntimeException;

final class RecipeController
{
    public function __construct(
        private Recipe $recipes,
        private OpenAIService $openai,
        private Logger $logger
    ) {}

    /** POST /api/recipes/generate */
    public function generate(Request $request): void
    {
        $data = $request->json();
        $prompt = trim((string) ($data['prompt'] ?? ''));
        $tags = is_array($data['tags'] ?? null)
            ? array_values(array_filter(array_map('strval', $data['tags'])))
            : [];
        $maxMinutes = isset($data['max_minutes']) ? (int) $data['max_minutes'] : null;

        if ($prompt === '' && $tags === []) {
            Response::json(['message' => 'Describe qué postre quieres o elige al menos una preferencia.'], 422);
            return;
        }

        try {
            $generated = $this->openai->generate($prompt, $tags, $maxMinutes);
        } catch (RuntimeException $e) {
            Response::json(['message' => $e->getMessage()], 502);
            return;
        }

        $recipe = $this->recipes->create([
            'title'        => $generated['title'],
            'prompt'       => $prompt,
            'tags'         => $tags,
            'ingredients'  => $generated['ingredients'],
            'steps'        => $generated['steps'],
            'difficulty'   => $generated['difficulty'],
            'prep_minutes' => $generated['prep_minutes'],
            'servings'     => $generated['servings'],
        ]);

        $this->logger->info('recipe', 'Receta generada', [
            'id'    => $recipe['id'],
            'title' => $recipe['title'],
        ]);

        $shareUrl = Config::apiBasePath() . '/recetas/' . ($recipe['slug'] ?? $recipe['id']);

        Response::json(['recipe' => $recipe, 'share_url' => $shareUrl], 201);
    }

    /** GET /api/recipes */
    public function index(Request $request): void
    {
        $limit = (int) $request->query('limit', 12);
        $recipes = $this->recipes->all($limit);
        $basePath = Config::apiBasePath();
        foreach ($recipes as &$r) {
            $r['share_url'] = $basePath . '/recetas/' . ($r['slug'] ?? $r['id']);
        }
        Response::json(['recipes' => $recipes]);
    }

    /** GET /api/recipes/{id} */
    public function show(Request $request, array $params): void
    {
        $recipe = $this->recipes->find($params['id'] ?? '');
        if ($recipe === null) {
            Response::json(['message' => 'Receta no encontrada.'], 404);
            return;
        }
        $this->recipes->incrementViews($recipe['id']);
        $recipe['share_url'] = Config::apiBasePath() . '/recetas/' . ($recipe['slug'] ?? $recipe['id']);
        Response::json(['recipe' => $recipe]);
    }
}
