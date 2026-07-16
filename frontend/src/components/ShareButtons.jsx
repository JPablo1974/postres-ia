"use client";

import { useState } from "react";

export default function ShareButtons({ recipe }) {
  const [copied, setCopied] = useState(false);

  const url = recipe.share_url ||
    (typeof window !== "undefined"
      ? `${window.location.origin}/recetas/${recipe.slug || recipe.id}`
      : "");
  const text = `${recipe.title} — receta de postre`;

  async function nativeShare() {
    if (navigator.share) {
      try {
        await navigator.share({ title: recipe.title, text, url });
      } catch {
        /* el usuario canceló */
      }
    } else {
      copyLink();
    }
  }

  async function copyLink() {
    try {
      await navigator.clipboard.writeText(url);
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    } catch {
      /* sin permiso de portapapeles */
    }
  }

  const enc = encodeURIComponent;
  const links = {
    whatsapp: `https://wa.me/?text=${enc(`${text} ${url}`)}`,
    x: `https://twitter.com/intent/tweet?text=${enc(text)}&url=${enc(url)}`,
    facebook: `https://www.facebook.com/sharer/sharer.php?u=${enc(url)}`,
  };

  const pill =
    "inline-flex h-11 items-center justify-center gap-2 rounded-full px-4 text-sm font-semibold transition-colors";

  return (
    <div className="flex flex-wrap items-center gap-2">
      <button onClick={nativeShare} className={`${pill} bg-frambuesa text-white hover:bg-frambuesa-dark`}>
        Compartir
      </button>
      <button
        onClick={copyLink}
        className={`${pill} border border-hairline bg-crema text-espresso hover:border-frambuesa`}
      >
        {copied ? "¡Copiado!" : "Copiar enlace"}
      </button>
      <a href={links.whatsapp} target="_blank" rel="noopener noreferrer"
         className={`${pill} border border-hairline bg-crema text-espresso hover:border-pistache`}>
        WhatsApp
      </a>
      <a href={links.x} target="_blank" rel="noopener noreferrer"
         className={`${pill} border border-hairline bg-crema text-espresso hover:border-espresso`}>
        X
      </a>
    </div>
  );
}
