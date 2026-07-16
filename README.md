# Ricos Postres IA 🍰

Aplicación web que genera **recetas de postres rápidas y auténticas** usando
inteligencia artificial (API de OpenAI). Los usuarios describen lo que quieren
—o los ingredientes que tienen en casa— y la IA devuelve una receta lista para
preparar y **compartir en redes sociales**.

Cada receta generada se **guarda en base de datos**, de modo que con el tiempo
la plataforma acumula un banco propio de recetas validadas: ese es el activo
principal del proyecto.

---

## 🎯 Objetivo

- **MVP funcionando y en producción en 3–4 días.**
- Que sea **auto-mantenible**: una vez desplegado, funciona solo. El dueño solo
  entra al back office de vez en cuando a revisar métricas y errores.
- Preparado para **monetizar con Google Ads** más adelante (integrado pero
  desactivado desde el inicio).

---

## 🧱 Arquitectura

Arquitectura **desacoplada**: frontend moderno + backend en la tecnología que ya
domina el equipo.

```
┌────────────────────────┐         HTTPS / JSON        ┌────────────────────────┐
│   FRONTEND (Next.js)   │  ─────────────────────────▶ │   BACKEND API (PHP)    │
│   React + Tailwind     │                             │   PHP 8 + MySQL        │
│   Diseño moderno       │  ◀───────────────────────── │   Apache + .htaccess   │
│   Puerto 3000 (dev)    │         respuestas          │   Puerto 8080 (dev)    │
└────────────────────────┘                             └───────────┬────────────┘
                                                                    │
                                                        ┌───────────▼────────────┐
                                                        │   OpenAI API           │
                                                        │   (genera recetas)     │
                                                        └────────────────────────┘
```

- **`frontend/`** → Next.js (App Router) con Tailwind. Solo consume la API. Casi
  sin mantenimiento una vez desplegado. Aquí vive la etiqueta de Google Ads
  (desactivada por defecto).
- **`backend/`** → API en PHP + MySQL sobre Apache. Hace todo el trabajo pesado:
  llama a OpenAI, guarda recetas, registra eventos y sirve el **back office**.
- **`database/`** → esquema SQL y datos de arranque.
- **CORS obligatorio** en desarrollo: frontend y backend corren en la misma
  máquina pero en **puertos distintos**, así que el navegador los trata como
  orígenes diferentes. El middleware de CORS del backend lo resuelve.

---

## ✨ Funcionalidades del MVP

1. **Generador de recetas con IA** — el usuario pide un postre o lista sus
   ingredientes; OpenAI devuelve la receta.
2. **Guardado en base de datos** — toda receta generada se almacena (banco de datos).
3. **Historial de recetas** — listado y detalle de recetas ya creadas.
4. **Compartir en redes** — botones para copiar / compartir la receta.
5. **Back office (panel de administración)** con:
   - recetas generadas hoy / total,
   - recetas más populares,
   - usuarios / peticiones,
   - estado de la API de OpenAI,
   - **log de errores** con histórico (para saber qué falló sin vigilar).
6. **Preparado para Google Ads** — integración lista, desactivada por defecto.
6. **Imágenes AI** — cada receta muestra una imagen generada automáticamente vía Pollinations.ai, usando el título como prompt.

### Ideas para fases siguientes (fuera del MVP)
- Votación / ranking de recetas por la comunidad.
- Favoritos por usuario.
- Retos semanales (challenges) para traer usuarios de vuelta.
- Niveles de dificultad.
- Buscador sobre el banco de recetas ya generadas (evita re-generar y ahorra API).

---

## 📁 Estructura del repositorio

```
postres-ia/
├── README.md
├── .gitignore
├── .editorconfig
│
├── backend/                    # API en PHP + MySQL (Apache)
│   ├── composer.json
│   ├── .env.example
│   ├── .htaccess
│   ├── public/                 # Document root público
│   │   ├── index.php           # Front controller / router
│   │   └── .htaccess           # Reescritura a index.php (con soporte VirtualHost)
│   ├── src/
│   │   ├── Config/             # Conexión a BD, carga de config
│   │   ├── Core/               # Router, Request, Response
│   │   ├── Middleware/         # CORS y otros
│   │   ├── Controllers/        # Recetas, Admin
│   │   ├── Services/           # OpenAIService, Logger
│   │   └── Models/             # Recipe, EventLog
│   ├── admin/                  # Back office (PHP server-rendered)
│   └── logs/                   # Logs de errores/eventos
│
├── frontend/                   # App Next.js (App Router)
│   ├── package.json
│   ├── next.config.js
│   ├── tailwind.config.js
│   ├── postcss.config.js
│   ├── jsconfig.json
│   ├── .env.local.example
│   ├── public/
│   └── src/
│       ├── app/                # Rutas (home, /recetas, /recetas/[id])
│       ├── components/         # RecipeGenerator, RecipeCard, ShareButtons
│       └── lib/                # Cliente de API
│
├── database/
│   ├── schema.sql              # Estructura de tablas
│   └── seed.sql                # Datos de arranque (opcional)
│
└── docs/
    └── ARQUITECTURA.md
```

---

## 🚀 Puesta en marcha (desarrollo local)

Todo se desarrolla en **una sola máquina**, en el **mismo repositorio**, con dos
servicios en puertos distintos.

### 1. Backend (PHP + MySQL + Apache)

Requisitos: PHP 8.1+ con las extensiones `pdo_mysql` y `curl`. **No requiere Composer**
(el backend trae su propio autoloader).

```bash
cd backend
cp .env.example .env          # EDITA las credenciales de MySQL y la API key de OpenAI
# Actualiza DB_PASS, OPENAI_API_KEY y CORS_ALLOWED_ORIGINS en .env antes de continuar
mysql -u root -p < ../database/schema.sql
```

Para desarrollo con Apache usa el VirtualHost `http://localhost/api-postres-ai/` (requiere
configuración en `httpd.conf` o `httpd-vhosts.conf`). O bien, sin Apache:

```bash
php -S localhost:8080 -t public/
```

> **Importante**: El backend debe estar activo antes de iniciar el frontend.
Si ves errores de CORS, verifica que la petición llegue al backend (curl -I la URL).

- Apunta el `DocumentRoot` de Apache (o un VirtualHost) a `backend/public/`.
El VirtualHost `http://localhost/api-postres-ai/` redirige automáticamente `/api/*` al router.
Back office: `http://localhost/api-postres-ai/admin/` (usuario y contraseña en el `.env`).

Endpoints (con VirtualHost):
- `GET  /api-postres-ai/api/health`
- `POST /api-postres-ai/api/recipes/generate`
- `GET  /api-postres-ai/api/recipes?limit=12`
- `GET  /api-postres-ai/api/recipes/:idOrSlug`

### 2. Frontend (Next.js)

```bash
cd frontend
cp .env.local.example .env.local   # define NEXT_PUBLIC_API_URL=http://localhost/api-postres-ai
npm install
npm run dev                        # http://localhost:3000
```

### 3. CORS

El backend permite el origen del frontend mediante el middleware de CORS
(`backend/src/Middleware/Cors.php`), configurable con `CORS_ALLOWED_ORIGINS` en
el `.env`. En dev: `http://localhost:3000`.

> **Nota sobre VirtualHost**: El backend soporta un prefijo en la URL mediante
`API_BASE_PATH` en `.env`. En desarrollo con VirtualHost Apache (`http://localhost/api-postres-ai/`),
la variable `API_BASE_PATH` debe coincidir con la ruta del VirtualHost. La lógica en
`Request.php` normaliza automáticamente las rutas sin afectar el código del router.

---

## 🌐 Despliegue en producción

- **Backend** → servidor Apache con PHP + MySQL, con el `DocumentRoot` en
  `backend/public/`. Ej.: `https://api.tudominio.com`.
- **Frontend** → Next.js desplegado (mismo servidor u otro). Ej.:
  `https://tudominio.com`.
- Ajusta `CORS_ALLOWED_ORIGINS` (backend) y `NEXT_PUBLIC_API_URL` (frontend) a
  los dominios reales.
- Google Ads: cambia el flag de activación en el frontend cuando decidas monetizar.

---

## 🔑 Variables de entorno

**backend/.env**
```
APP_ENV=development
DB_HOST=127.0.0.1
DB_NAME=postres_ia_db
DB_USER=root
DB_PASS="TU_PASSWORD_AQUI"
OPENAI_API_KEY="TU_API_KEY_AQUI"
OPENAI_MODEL=gpt-4o-mini
CORS_ALLOWED_ORIGINS=http://localhost:3000
ADMIN_USER=admin
ADMIN_PASS="CAMBIA-ESTO"
API_BASE_PATH=http://localhost/api-postres-ai
ALERT_EMAIL=
```

**frontend/.env.local**
```
NEXT_PUBLIC_API_URL=http://localhost/api-postres-ai
NEXT_PUBLIC_API_BASE_PATH=http://localhost/api-postres-ai
NEXT_PUBLIC_ADS_ENABLED=false
```

---

## 🗺️ Estado

- [x] Estructura del repositorio
- [x] Backend: router + CORS + conexión BD
- [x] Backend: servicio OpenAI + guardado de recetas
- [x] Backend: back office + logging
- [x] Frontend: generador + listado + compartir
- [ ] Integración Google Ads (desactivada)
- [ ] Despliegue

---

*Stack: Next.js · React · Tailwind · PHP 8 · MySQL · Apache · OpenAI API · Pollinations.ai*
