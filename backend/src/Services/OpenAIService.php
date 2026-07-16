<?php
declare(strict_types=1);

namespace App\Services;

use RuntimeException;

/** Genera recetas reales llamando a la API de OpenAI (Chat Completions, JSON mode). */
final class OpenAIService
{
    private const ENDPOINT = 'https://api.openai.com/v1/chat/completions';

    public function __construct(
        private string $apiKey,
        private string $model,
        private Logger $logger
    ) {}

    /**
     * @param string[] $tags
     * @return array{title:string,prep_minutes:?int,difficulty:string,servings:?int,ingredients:string[],steps:string[]}
     */
    public function generate(string $prompt, array $tags = [], ?int $maxMinutes = null): array
    {
        if ($this->apiKey === '') {
            throw new RuntimeException('El servicio de IA no está configurado.');
        }

        $system = 'Eres un chef repostero. Respondes EXCLUSIVAMENTE con un objeto JSON válido, sin texto adicional, '
            . 'con una receta de postre real y preparable. Esquema exacto: '
            . '{"title": string, "prep_minutes": number, "difficulty": "facil"|"media"|"dificil", '
            . '"servings": number, "ingredients": string[] (cada elemento con su cantidad, ej. "2 tazas de harina"), '
            . '"steps": string[] (pasos claros y ordenados)}. Todo en español. '
            . 'Si la petición no es un postre, propón el postre más cercano y razonable.';

        $parts = [];
        if (trim($prompt) !== '') {
            $parts[] = 'Petición: ' . trim($prompt);
        }
        if ($tags) {
            $parts[] = 'Preferencias: ' . implode(', ', $tags);
        }
        if ($maxMinutes) {
            $parts[] = "Tiempo máximo de preparación: {$maxMinutes} minutos.";
        }
        $user = $parts ? implode("\n", $parts) : 'Un postre rápido y sencillo.';

        $payload = [
            'model'           => $this->model,
            'messages'        => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user',   'content' => $user],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature'     => 0.8,
        ];

        $ch = curl_init(self::ENDPOINT);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_TIMEOUT        => 45,
        ]);

        $raw = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            $this->logger->error('openai', 'Fallo de conexión con OpenAI', ['curl' => $curlError]);
            throw new RuntimeException('No se pudo conectar con el servicio de IA.');
        }

        $body = json_decode((string) $raw, true);
        if ($httpCode >= 400 || !isset($body['choices'][0]['message']['content'])) {
            $this->logger->error('openai', 'Respuesta con error de OpenAI', [
                'status' => $httpCode,
                'error'  => $body['error']['message'] ?? null,
            ]);
            throw new RuntimeException('El servicio de IA devolvió un error.');
        }

        $recipe = json_decode((string) $body['choices'][0]['message']['content'], true);
        if (!is_array($recipe)) {
            $this->logger->error('openai', 'JSON inválido devuelto por OpenAI', []);
            throw new RuntimeException('La IA devolvió una respuesta no válida.');
        }

        return $this->normalize($recipe);
    }

    private function normalize(array $r): array
    {
        $difficulty = in_array($r['difficulty'] ?? '', ['facil', 'media', 'dificil'], true)
            ? $r['difficulty']
            : 'facil';

        $ingredients = array_values(array_filter(array_map(
            static fn($v) => trim((string) $v),
            (array) ($r['ingredients'] ?? [])
        )));
        $steps = array_values(array_filter(array_map(
            static fn($v) => trim((string) $v),
            (array) ($r['steps'] ?? [])
        )));

        if (!$ingredients || !$steps) {
            throw new RuntimeException('La receta generada llegó incompleta.');
        }

        return [
            'title'        => trim((string) ($r['title'] ?? 'Postre sorpresa')) ?: 'Postre sorpresa',
            'prep_minutes' => isset($r['prep_minutes']) ? (int) $r['prep_minutes'] : null,
            'difficulty'   => $difficulty,
            'servings'     => isset($r['servings']) ? (int) $r['servings'] : null,
            'ingredients'  => $ingredients,
            'steps'        => $steps,
        ];
    }
}
