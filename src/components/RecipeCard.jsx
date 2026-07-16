"use client";

import { useEffect } from "react";
import ShareButtons from "./ShareButtons";

export function getFriendlyTitle(title) {
  return (title || "")
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036c]/g, "")
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "");
}

const DIFFICULTY_LABEL = { facil: "Fácil", media: "Media", dificil: "Difícil" };

function Meta({ label, value }) {
  if (!value) return null;
  return (
    <div className="flex flex-col">
      <span className="text-[11px] font-semibold uppercase tracking-widest text-mocha">
        {label}
      </span>
      <span className="text-sm font-semibold text-espresso">{value}</span>
    </div>
  );
}

function ingredientText(item) {
  if (typeof item === "string") return item;
  return [item.amount, item.name].filter(Boolean).join(" ");
}

export default function RecipeCard({ recipe, share = true }) {
  const ingredients = recipe.ingredients || [];
  const steps = recipe.steps || [];
  const imageUrl = `https://image.pollinations.ai/prompt/${getFriendlyTitle(recipe.title)}?width=800&height=800&model=flux&nologo=true`;

  // Actualizar meta tags Open Graph cuando se muestra en detalle
  useEffect(() => {
    if (!share) return;
    const ogImage = document.querySelector('meta[property="og:image"]');
    const ogTitle = document.querySelector('meta[property="og:title"]');
    const ogDesc = document.querySelector('meta[property="og:description"]');
    if (ogImage) ogImage.content = imageUrl;
    if (ogTitle) ogTitle.content = recipe.title;
    if (ogDesc) ogDesc.content = `Receta de ${recipe.title} — ${recipe.ingredients?.slice(0, 3).join(", ") || "postre delicioso"}`;
  }, [share, imageUrl, recipe]);

  return (
    <article className="animate-rise overflow-hidden rounded-card border border-hairline bg-crema shadow-dulce">
      <div className="h-1.5 w-full bg-gradient-to-r from-frambuesa via-cajeta to-pistache" />
      <div className="p-6 sm:p-8">
        <h2 className="font-display text-2xl font-semibold leading-tight text-espresso sm:text-3xl">
          {recipe.title}
        </h2>

        <img
          src={imageUrl}
          alt={recipe.title}
          className="mt-4 w-full rounded-lg object-cover aspect-square"
          loading="lazy"
        />

        <div className="mt-5 flex flex-wrap gap-x-8 gap-y-3 border-b border-hairline pb-5">
          <Meta label="Tiempo" value={recipe.prep_minutes ? `${recipe.prep_minutes} min` : null} />
          <Meta label="Dificultad" value={DIFFICULTY_LABEL[recipe.difficulty]} />
          <Meta label="Porciones" value={recipe.servings || null} />
        </div>

        {ingredients.length > 0 && (
          <section className="mt-6">
            <h3 className="text-xs font-bold uppercase tracking-widest text-frambuesa">
              Ingredientes
            </h3>
            <ul className="mt-3 space-y-2">
              {ingredients.map((item, i) => (
                <li key={i} className="flex gap-3 text-[15px] leading-relaxed text-espresso">
                  <span className="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-cajeta" />
                  {ingredientText(item)}
                </li>
              ))}
            </ul>
          </section>
        )}

        {steps.length > 0 && (
          <section className="mt-8">
            <h3 className="text-xs font-bold uppercase tracking-widest text-frambuesa">
              Preparación
            </h3>
            <ol className="mt-3 space-y-4">
              {steps.map((step, i) => (
                <li key={i} className="flex gap-4">
                  <span className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-frambuesa/10 font-display text-sm font-semibold text-frambuesa">
                    {i + 1}
                  </span>
                  <p className="pt-0.5 text-[15px] leading-relaxed text-espresso">{step}</p>
                </li>
              ))}
            </ol>
          </section>
        )}

        {share && (
          <div className="mt-8 border-t border-hairline pt-6">
            <ShareButtons recipe={recipe} />
          </div>
        )}
      </div>
    </article>
  );
}
