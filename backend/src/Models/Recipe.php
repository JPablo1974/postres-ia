<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

final class Recipe
{
    public function __construct(private PDO $pdo) {}

    public function create(array $data): array
    {
        $slug = $this->uniqueSlug((string) $data['title']);

        $stmt = $this->pdo->prepare(
            'INSERT INTO recipes
                (slug, title, prompt, tags, ingredients, steps, difficulty, prep_minutes, servings)
             VALUES
                (:slug, :title, :prompt, :tags, :ingredients, :steps, :difficulty, :prep_minutes, :servings)'
        );
        $stmt->execute([
            ':slug'         => $slug,
            ':title'        => $data['title'],
            ':prompt'       => $data['prompt'] ?? '',
            ':tags'         => !empty($data['tags']) ? json_encode($data['tags'], JSON_UNESCAPED_UNICODE) : null,
            ':ingredients'  => json_encode($data['ingredients'], JSON_UNESCAPED_UNICODE),
            ':steps'        => json_encode($data['steps'], JSON_UNESCAPED_UNICODE),
            ':difficulty'   => $data['difficulty'],
            ':prep_minutes' => $data['prep_minutes'],
            ':servings'     => $data['servings'],
        ]);

        return $this->find((int) $this->pdo->lastInsertId());
    }

    /** @return array<int,array> */
    public function all(int $limit = 12): array
    {
        $limit = max(1, min($limit, 50));
        $stmt = $this->pdo->prepare('SELECT * FROM recipes ORDER BY created_at DESC LIMIT :lim');
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    public function find(int|string $idOrSlug): ?array
    {
        $column = ctype_digit((string) $idOrSlug) ? 'id' : 'slug';
        $stmt = $this->pdo->prepare("SELECT * FROM recipes WHERE {$column} = ? LIMIT 1");
        $stmt->execute([$idOrSlug]);
        $row = $stmt->fetch();

        return $row ? $this->hydrate($row) : null;
    }

    public function incrementViews(int $id): void
    {
        $this->pdo->prepare('UPDATE recipes SET views = views + 1 WHERE id = ?')->execute([$id]);
    }

    private function hydrate(array $row): array
    {
        return [
            'id'           => (int) $row['id'],
            'slug'         => $row['slug'],
            'title'        => $row['title'],
            'difficulty'   => $row['difficulty'],
            'prep_minutes' => $row['prep_minutes'] !== null ? (int) $row['prep_minutes'] : null,
            'servings'     => $row['servings'] !== null ? (int) $row['servings'] : null,
            'ingredients'  => json_decode((string) ($row['ingredients'] ?? '[]'), true) ?: [],
            'steps'        => json_decode((string) ($row['steps'] ?? '[]'), true) ?: [],
            'tags'         => json_decode((string) ($row['tags'] ?? '[]'), true) ?: [],
            'views'        => (int) $row['views'],
            'created_at'   => $row['created_at'],
        ];
    }

    private function uniqueSlug(string $title): string
    {
        $base = $this->slugify($title);
        $slug = $base;
        $n = 1;
        $check = $this->pdo->prepare('SELECT 1 FROM recipes WHERE slug = ? LIMIT 1');

        while (true) {
            $check->execute([$slug]);
            if (!$check->fetch()) {
                return $slug;
            }
            $slug = $base . '-' . (++$n);
        }
    }

    private function slugify(string $text): string
    {
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($ascii !== false) {
            $text = $ascii;
        }
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
        $text = trim($text, '-');
        $text = substr($text, 0, 120);

        return $text !== '' ? $text : 'postre-' . bin2hex(random_bytes(3));
    }
}
