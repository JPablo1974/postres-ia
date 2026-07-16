import Header from "@/components/Header";
import RecipeList from "@/components/RecipeList";

export const metadata = {
  title: "Recetas · Ricos Postres IA",
  description: "Todas las recetas de postres generadas con IA.",
};

export default function RecetasPage() {
  return (
    <>
      <Header />
      <main className="mx-auto max-w-3xl px-5 pb-24">
        <section className="pt-10 sm:pt-14">
          <h1 className="font-display text-4xl font-semibold text-espresso sm:text-5xl">
            El recetario
          </h1>
          <p className="mt-3 max-w-md text-[17px] leading-relaxed text-mocha">
            Todo lo que la comunidad ha horneado con ayuda de la IA.
          </p>
        </section>
        <section className="mt-10">
          <RecipeList limit={30} />
        </section>
      </main>
    </>
  );
}
