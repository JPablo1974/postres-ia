"use client";

import { useState } from "react";
import { api, ApiError } from "@/lib/api";
import RecipeCard from "./RecipeCard";

const CRAVINGS = [
  "Chocolate",
  "Sin horno",
  "En 15 min",
  "Con lo que tengo",
  "Fresa",
  "Saludable",
  "Para niños",
  "Clásico mexicano",
];

function Loading() {
  return (
    <div className="flex flex-col items-center gap-3 py-10 text-mocha">
      <div className="flex gap-1.5">
        <span className="h-2.5 w-2.5 rounded-full bg-frambuesa animate-bounceDot" />
        <span className="h-2.5 w-2.5 rounded-full bg-cajeta animate-bounceDot [animation-delay:.15s]" />
        <span className="h-2.5 w-2.5 rounded-full bg-pistache animate-bounceDot [animation-delay:.3s]" />
      </div>
      <p className="text-sm font-medium">Batiendo ideas…</p>
    </div>
  );
}

export default function RecipeGenerator() {
  const [prompt, setPrompt] = useState("");
  const [tags, setTags] = useState([]);
  const [status, setStatus] = useState("idle"); // idle | loading | done | error
  const [recipe, setRecipe] = useState(null);
  const [error, setError] = useState("");

  function toggleTag(tag) {
    setTags((prev) =>
      prev.includes(tag) ? prev.filter((t) => t !== tag) : [...prev, tag]
    );
  }

  async function generate() {
    if (!prompt.trim() && tags.length === 0) return;
    setStatus("loading");
    setError("");
    setRecipe(null);
    try {
      const result = await api.generateRecipe({ prompt: prompt.trim(), tags });
      setRecipe(result);
      setStatus("done");
    } catch (err) {
      setError(
        err instanceof ApiError && err.status === 0
          ? "No se pudo conectar con el servidor. Revisa tu conexión e inténtalo de nuevo."
          : "No se pudo generar la receta. Inténtalo de nuevo en un momento."
      );
      setStatus("error");
    }
  }

  const canSubmit = prompt.trim().length > 0 || tags.length > 0;

  return (
    <div>
      <div className="rounded-card border border-hairline bg-crema p-4 shadow-dulce sm:p-5">
        <textarea
          value={prompt}
          onChange={(e) => setPrompt(e.target.value)}
          rows={3}
          placeholder="Un pastel de chocolate sin horno para 4…"
          className="w-full resize-none rounded-2xl bg-masa/60 p-4 text-[16px] leading-relaxed text-espresso placeholder:text-mocha/70 focus:outline-none"
        />

        <div className="mt-3 flex flex-wrap gap-2">
          {CRAVINGS.map((c) => {
            const active = tags.includes(c);
            return (
              <button
                key={c}
                type="button"
                onClick={() => toggleTag(c)}
                aria-pressed={active}
                className={`h-9 rounded-full px-3.5 text-sm font-medium shadow-chip transition-colors ${
                  active
                    ? "bg-espresso text-crema"
                    : "border border-hairline bg-masa/50 text-mocha hover:border-frambuesa hover:text-espresso"
                }`}
              >
                {c}
              </button>
            );
          })}
        </div>

        <button
          onClick={generate}
          disabled={!canSubmit || status === "loading"}
          className="mt-4 flex h-14 w-full items-center justify-center rounded-full bg-frambuesa text-base font-semibold text-white transition-colors hover:bg-frambuesa-dark disabled:cursor-not-allowed disabled:opacity-40"
        >
          {status === "loading" ? "Generando…" : "Generar receta"}
        </button>
      </div>

      <div className="mt-6" aria-live="polite">
        {status === "loading" && <Loading />}
        {status === "error" && (
          <div className="rounded-2xl border border-frambuesa/30 bg-frambuesa/5 p-4 text-sm text-frambuesa-dark">
            {error}
          </div>
        )}
        {status === "done" && recipe && <RecipeCard recipe={recipe} />}
      </div>
    </div>
  );
}
