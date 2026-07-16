// Cliente de la API (backend PHP). La URL base viene de .env.local
// NEXT_PUBLIC_API_URL=http://localhost:8080
// NEXT_PUBLIC_API_BASE_PATH=http://localhost/postres-ia/backend
//
// Contrato esperado del backend (a implementar en PHP):
//
//   POST /api/recipes/generate
//     body: { prompt: string, tags?: string[], max_minutes?: number|null }
//     -> 200 { recipe: Recipe, share_url: string }
//
//   GET  /api/recipes?limit=12
//     -> 200 { recipes: Recipe[] }
//
//   GET  /api/recipes/:id
//     -> 200 { recipe: Recipe }
//
//   Recipe = {
//     id, slug, title,
//     prep_minutes: number|null,
//     difficulty: "facil"|"media"|"dificil",
//     servings: number|null,
//     ingredients: string[]  // o [{ name, amount }]
//     steps: string[],
//     share_url: string,     // URL para compartir la receta
//     created_at
//   }

const BASE_URL = (
  process.env.NEXT_PUBLIC_API_URL || "http://localhost:8080"
).replace(/\/$/, "");

async function request(path, options = {}) {
  let res;
  try {
    res = await fetch(`${BASE_URL}${path}`, {
      headers: { "Content-Type": "application/json", ...(options.headers || {}) },
      ...options,
    });
  } catch (err) {
    throw new ApiError("No se pudo conectar con el servidor.", 0);
  }

  const data = await res.json().catch(() => ({}));
  if (!res.ok) {
    throw new ApiError(data?.message || "Ocurrió un error inesperado.", res.status);
  }
  return data;
}

export class ApiError extends Error {
  constructor(message, status) {
    super(message);
    this.name = "ApiError";
    this.status = status;
  }
}

export const api = {
  generateRecipe: (payload) =>
    request("/api/recipes/generate", {
      method: "POST",
      body: JSON.stringify(payload),
    }).then((d) => d.recipe),

  listRecipes: (limit = 12) =>
    request(`/api/recipes?limit=${limit}`).then((d) => d.recipes || []),

  getRecipe: (id) => request(`/api/recipes/${id}`).then((d) => d.recipe),
};
