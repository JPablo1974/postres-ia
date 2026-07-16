/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/app/**/*.{js,jsx}",
    "./src/components/**/*.{js,jsx}",
  ],
  theme: {
    extend: {
      colors: {
        masa: "#FDF5F1",       // fondo, "leche con fresa"
        crema: "#FFFDFB",      // superficies / tarjetas
        espresso: "#2B1B18",   // tinta principal (cacao oscuro)
        mocha: "#8A716B",      // texto secundario
        frambuesa: {
          DEFAULT: "#E23E6A",
          dark: "#C22F58",
        },
        cajeta: "#CE8A34",     // acento cálido (dulce de leche)
        pistache: "#7FA65C",   // acento fresco (sin horno / saludable)
        hairline: "#F0E1DA",
      },
      fontFamily: {
        display: ["var(--font-fraunces)", "Georgia", "serif"],
        sans: ["var(--font-jakarta)", "system-ui", "sans-serif"],
      },
      borderRadius: {
        card: "1.5rem",
      },
      boxShadow: {
        dulce: "0 1px 2px rgba(43,27,24,.04), 0 24px 48px -28px rgba(226,62,106,.35)",
        chip: "0 1px 0 rgba(43,27,24,.04)",
      },
      keyframes: {
        rise: {
          "0%": { opacity: "0", transform: "translateY(10px)" },
          "100%": { opacity: "1", transform: "translateY(0)" },
        },
        bounceDot: {
          "0%,80%,100%": { transform: "translateY(0)", opacity: ".5" },
          "40%": { transform: "translateY(-6px)", opacity: "1" },
        },
      },
      animation: {
        rise: "rise .5s cubic-bezier(.2,.7,.2,1) both",
        bounceDot: "bounceDot 1.2s infinite ease-in-out",
      },
    },
  },
  plugins: [],
};
