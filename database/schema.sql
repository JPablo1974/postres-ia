-- Postres IA - esquema
-- Ejecutar: mysql -u root -p < database/schema.sql

CREATE DATABASE IF NOT EXISTS postres_ia_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE postres_ia_db;

-- Recetas generadas por IA y guardadas (el banco de datos = activo principal)
CREATE TABLE IF NOT EXISTS recipes (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug          VARCHAR(200) NOT NULL UNIQUE,
  title         VARCHAR(255) NOT NULL,
  prompt        TEXT NOT NULL,                 -- lo que pidio el usuario
  tags          JSON NULL,                     -- preferencias elegidas
  ingredients   JSON NOT NULL,                 -- array de strings
  steps         JSON NOT NULL,                 -- array de strings
  difficulty    ENUM('facil','media','dificil') NOT NULL DEFAULT 'facil',
  prep_minutes  INT UNSIGNED NULL,
  servings      INT UNSIGNED NULL,
  views         INT UNSIGNED NOT NULL DEFAULT 0,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_created (created_at),
  INDEX idx_views (views)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Registro de eventos y errores (para el back office y las alertas)
CREATE TABLE IF NOT EXISTS event_logs (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  level       ENUM('info','warning','error') NOT NULL DEFAULT 'info',
  source      VARCHAR(100) NOT NULL,           -- ej: 'openai', 'db', 'api'
  message     TEXT NOT NULL,
  context     JSON NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_level (level),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
