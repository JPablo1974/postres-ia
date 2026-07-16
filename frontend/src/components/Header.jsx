import Link from "next/link";

export default function Header() {
  return (
    <header className="sticky top-0 z-20 border-b border-hairline/70 bg-masa/85 backdrop-blur">
      <div className="mx-auto flex h-14 max-w-3xl items-center justify-between px-5">
        <Link href="/" className="flex items-baseline gap-1">
          <span className="font-display text-xl font-semibold tracking-tight text-espresso">
            Ricos Postres
          </span>
          <span className="font-display text-xl font-semibold italic text-frambuesa">
            IA
          </span>
        </Link>
        <Link
          href="/recetas"
          className="text-sm font-medium text-mocha transition-colors hover:text-espresso"
        >
          Recetas
        </Link>
      </div>
    </header>
  );
}
