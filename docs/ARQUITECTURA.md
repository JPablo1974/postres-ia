# Arquitectura

Ver el diagrama y la explicacion completa en el README raiz.

Resumen:
- Frontend Next.js (solo consume la API) -> mantenimiento minimo.
- Backend PHP + MySQL sobre Apache -> logica, OpenAI, guardado, back office.
- CORS obligatorio en dev (mismo equipo, puertos distintos).
- Google Ads: integrado en el frontend, desactivado por defecto.
