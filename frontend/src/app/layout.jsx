import { Fraunces, Plus_Jakarta_Sans } from "next/font/google";
import Script from "next/script";
import "./globals.css";

const fraunces = Fraunces({
  subsets: ["latin"],
  variable: "--font-fraunces",
  display: "swap",
});

const jakarta = Plus_Jakarta_Sans({
  subsets: ["latin"],
  variable: "--font-jakarta",
  display: "swap",
});

export const metadata = {
  title: "Ricos Postres IA — Recetas de postres con inteligencia artificial",
  description:
    "Describe lo que se te antoja o los ingredientes que tienes y recibe una receta de postre lista para preparar y compartir.",
  openGraph: {
    title: "Ricos Postres IA",
    description: "Recetas de postres generadas con inteligencia artificial",
    type: "website",
    locale: "es_ES",
  },
  other: {
    "og:image": "https://image.pollinations.ai/prompt/ricos-postres-ia?width=1200&height=630&model=flux&nologo=true",
  },
};

export const viewport = {
  themeColor: "#FDF5F1",
  width: "device-width",
  initialScale: 1,
  maximumScale: 5,
};

const ADS_ENABLED = process.env.NEXT_PUBLIC_ADS_ENABLED === "true";
const ADS_CLIENT = process.env.NEXT_PUBLIC_ADSENSE_CLIENT || "";

export default function RootLayout({ children }) {
  return (
    <html lang="es" className={`${fraunces.variable} ${jakarta.variable}`}>
<body className="font-sans antialiased">
         {children}
        {/* Google Ads: desactivado por defecto. Se activa con
            NEXT_PUBLIC_ADS_ENABLED=true y NEXT_PUBLIC_ADSENSE_CLIENT. */}
        {ADS_ENABLED && ADS_CLIENT && (
          <Script
            async
            strategy="afterInteractive"
            src={`https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=${ADS_CLIENT}`}
            crossOrigin="anonymous"
          />
        )}
      </body>
    </html>
  );
}
