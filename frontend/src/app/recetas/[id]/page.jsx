"use client";

import { useEffect, useState } from "react";
import { useParams } from "next/navigation";
import Link from "next/link";
import Header from "@/components/Header";
import RecipeCard from "@/components/RecipeCard";
import { api } from "@/lib/api";

export default function RecipeDetailPage() {
  const { id } = useParams();
  const [state, setState] = useState("loading");
  const [recipe, setRecipe] = useState(null);

  useEffect(() => {
    let alive = true;
    api
      .getRecipe(id)
      .then((data) => {
        if (!alive) return;
        setRecipe(data);
        setState("ready");
      })
      .catch(() => alive && setStatus("error"));
    return () => {
      alive = false;
    };
  }, [id]);

  return (
    <>
      <Header />
      <main className="mx-auto max-w-3xl px-5 pb-24 pt-8">
        <Link href="/recetas" className="text-sm font-medium text-mocha hover:text-espresso">
          ← Volver al recetario
        </Link>
        <div className="mt-5">
          {state === "loading" && (
            <div className="h-72 animate-pulse rounded-card bg-hairline/50" />
          )}
          {state === "error" && (
            <div className="rounded-card border border-hairline bg-crema p-8 text-center text-mocha">
              No encontramos esta receta.
            </div>
          )}
          {state === "ready" && recipe && <RecipeCard recipe={recipe} />}
        </div>
      </main>
    </>
  );
}