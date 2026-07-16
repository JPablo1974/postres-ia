"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { api } from "@/lib/api";

const DIFFICULTY_LABEL = { facil: "Fácil", media: "Media", dificil: "Difícil" };

function Card({ recipe }) {
  return (
    <Link
      href={`/recetas/${recipe.slug || recipe.id}`}
      className="group flex flex-col justify-between rounded-2xl border border-hairline bg-crema p-5 shadow-chip transition-all hover:-translate-y-0.5 hover:shadow-dulce"
    >
      <h3 className="font-display text-lg font-semibold leading-snug text-espresso group-hover:text-frambuesa">
        {recipe.title}
      </h3>
      <div className="mt-4 flex gap-3 text-xs font-medium text-mocha">
        {recipe.prep_minutes && <span>{recipe.prep_minutes} min</span>}
        {recipe.difficulty && <span>· {DIFFICULTY_LABEL[recipe.difficulty]}</span>}
      </div>
    </Link>
  );
}

export default function RecipeList({ limit = 6 }) {
  const [state, setState] = useState("loading"); // loading | ready | empty | error
  const [recipes, setRecipes] = useState([]);

  useEffect(() => {
    let alive = true;
    api
      .listRecipes(limit)
      .then((data) => {
        if (!alive) return;
        setRecipes(data);
        setState(data.length ? "ready" : "empty");
      })
      .catch(() => alive && setState("error"));
    return () => {
      alive = false;
    };
  }, [limit]);

  if (state === "loading") {
    return (
      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
        {Array.from({ length: 4 }).map((_, i) => (
          <div key={i} className="h-28 animate-pulse rounded-2xl bg-hairline/50" />
        ))}
      </div>
    );
  }

  if (state === "empty") {
    return (
      <div className="rounded-2xl border border-dashed border-hairline bg-crema/50 p-8 text-center">
        <p className="text-espresso">Aún no hay recetas por aquí.</p>
        <p className="mt-1 text-sm text-mocha">Genera la primera arriba y aparecerá al instante.</p>
      </div>
    );
  }

  if (state === "error") {
    return (
      <div className="rounded-2xl border border-hairline bg-crema/50 p-8 text-center text-sm text-mocha">
        No pudimos cargar las recetas. Vuelve a intentarlo más tarde.
      </div>
    );
  }

  return (
    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
      {recipes.map((r) => (
        <Card key={r.id} recipe={r} />
      ))}
    </div>
  );
}
