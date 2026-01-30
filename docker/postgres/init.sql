-- ============================================
-- PostgreSQL Initialization Script
-- ============================================
-- Este script corre automáticamente al inicializar el contenedor de PostgreSQL
-- Crea la estructura base de la aplicación

-- ============================================
-- Create Database
-- ============================================
-- El database ya está creado por POSTGRES_DB env variable
-- pero aquí podemos agregar extensiones necesarias

CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";  -- Para búsquedas de texto
CREATE EXTENSION IF NOT EXISTS "pgcrypto"; -- Para funciones de encriptación

-- ============================================
-- Create Schemas
-- ============================================
-- Separar diferentes contextos de la aplicación
CREATE SCHEMA IF NOT EXISTS public;
CREATE SCHEMA IF NOT EXISTS auth;
CREATE SCHEMA IF NOT EXISTS pokemon;
CREATE SCHEMA IF NOT EXISTS logs;

-- ============================================
-- Grant Permissions
-- ============================================
-- Otorgar permisos al usuario de aplicación
GRANT ALL PRIVILEGES ON SCHEMA public TO pokemon_user;
GRANT ALL PRIVILEGES ON SCHEMA auth TO pokemon_user;
GRANT ALL PRIVILEGES ON SCHEMA pokemon TO pokemon_user;
GRANT ALL PRIVILEGES ON SCHEMA logs TO pokemon_user;

GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO pokemon_user;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA auth TO pokemon_user;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA pokemon TO pokemon_user;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA logs TO pokemon_user;

-- ============================================
-- Create Initial Tables (Optional)
-- ============================================
-- Las tablas principales se crearán mediante Laravel migrations
-- pero podemos crear tablas de auditoría aquí

CREATE TABLE IF NOT EXISTS logs.audit_log (
    id BIGSERIAL PRIMARY KEY,
    entity_type VARCHAR(255) NOT NULL,
    entity_id BIGINT,
    action VARCHAR(50) NOT NULL, -- CREATE, UPDATE, DELETE
    old_values JSONB,
    new_values JSONB,
    user_id BIGINT,
    ip_address INET,
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at DESC)
);

-- ============================================
-- Set Default Search Path
-- ============================================
ALTER DATABASE pokemon_bff SET search_path TO public, auth, pokemon;
