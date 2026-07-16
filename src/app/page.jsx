import Link from "next/link";
import Header from "@/components/Header";
import RecipeGenerator from "@/components/RecipeGenerator";
import RecipeList from "@/components/RecipeList";

export default function HomePage() {
  return (
    <>
      <Header />
      <main className="mx-auto max-w-3xl px-5 pb-24">
        <section className="pt-10 sm:pt-16">
          <p className="text-xs font-bold uppercase tracking-[0.2em] text-frambuesa">
            Repostería con IA
          </p>
          <h1 className="mt-3 font-display text-4xl font-semibold leading-[1.05] text-espresso sm:text-6xl">
            ¿Qué se te
            <br />
            antoja <span className="italic text-frambuesa">hoy</span>?
          </h1>
          <p className="mt-4 max-w-md text-[17px] leading-relaxed text-mocha">
            Descríbelo o dime qué tienes en la alacena. Te devuelvo una receta
            de postre lista para preparar y compartir.
          </p>
        </section>

        <section className="mt-8">
          <RecipeGenerator />
        </section>

        <section className="mt-16">
          <div className="mb-5 flex items-baseline justify-between">
            <h2 className="font-display text-2xl font-semibold text-espresso">
              Recién horneadas
            </h2>
            <Link href="/recetas" className="text-sm font-medium text-frambuesa hover:text-frambuesa-dark">
              Ver todas
            </Link>
          </div>
          <RecipeList limit={6} />
        </section>
      </main>

      <footer className="border-t border-hairline">
        <div className="mx-auto max-w-3xl px-5 py-8 text-sm text-mocha">
          Ricos Postres IA · Recetas generadas con inteligencia artificial.
        </div>
      </footer>
    </>
  );
}
