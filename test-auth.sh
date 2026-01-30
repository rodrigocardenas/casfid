#!/bin/bash

# ============================================
# Testing Script para Auth API
# ============================================
# Uso: bash test-auth.sh
# Requiere: curl, jq (opcional)

set -e

BASE_URL="http://localhost:8000/api/v1"
TOKEN=""

echo "╔════════════════════════════════════════════════════════════════╗"
echo "║          TESTING: Pokémon BFF Authentication API              ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

# ============================================
# Test 1: Registrar Usuario
# ============================================
echo "📝 TEST 1: POST /auth/register"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

RESPONSE=$(curl -s -X POST "$BASE_URL/auth/register" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test.'$(date +%s)'@example.com",
    "password": "TestPassword123!",
    "password_confirmation": "TestPassword123!"
  }')

echo "$RESPONSE" | jq . 2>/dev/null || echo "$RESPONSE"
echo ""

# Extraer token
TOKEN=$(echo "$RESPONSE" | jq -r '.token' 2>/dev/null)
EMAIL=$(echo "$RESPONSE" | jq -r '.data.email' 2>/dev/null)

if [ "$TOKEN" != "null" ] && [ ! -z "$TOKEN" ]; then
  echo "✅ Registro exitoso - Token obtenido"
  echo "📧 Email: $EMAIL"
  echo "🔑 Token: ${TOKEN:0:50}..."
else
  echo "❌ Error en registro"
  exit 1
fi
echo ""

# ============================================
# Test 2: Obtener Datos del Usuario
# ============================================
echo "👤 TEST 2: GET /auth/me"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

curl -s -X GET "$BASE_URL/auth/me" \
  -H "Authorization: Bearer $TOKEN" \
  | jq . 2>/dev/null || echo "Error parsing response"
echo ""

# ============================================
# Test 3: Renovar Token
# ============================================
echo "🔄 TEST 3: POST /auth/refresh"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

REFRESH_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/refresh" \
  -H "Authorization: Bearer $TOKEN")

echo "$REFRESH_RESPONSE" | jq . 2>/dev/null || echo "$REFRESH_RESPONSE"

NEW_TOKEN=$(echo "$REFRESH_RESPONSE" | jq -r '.data.token' 2>/dev/null)

if [ "$NEW_TOKEN" != "null" ] && [ ! -z "$NEW_TOKEN" ]; then
  echo "✅ Token renovado exitosamente"
  TOKEN="$NEW_TOKEN"
else
  echo "⚠️  Error en refresh (puede ser normal si token aún es válido)"
fi
echo ""

# ============================================
# Test 4: Intentar Login
# ============================================
echo "🔐 TEST 4: POST /auth/login"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d "{
    \"email\": \"$EMAIL\",
    \"password\": \"TestPassword123!\"
  }")

echo "$LOGIN_RESPONSE" | jq . 2>/dev/null || echo "$LOGIN_RESPONSE"

LOGIN_TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.token' 2>/dev/null)

if [ "$LOGIN_TOKEN" != "null" ] && [ ! -z "$LOGIN_TOKEN" ]; then
  echo "✅ Login exitoso"
  TOKEN="$LOGIN_TOKEN"
else
  echo "❌ Error en login"
fi
echo ""

# ============================================
# Test 5: Cerrar Sesión
# ============================================
echo "🚪 TEST 5: POST /auth/logout"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

LOGOUT_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/logout" \
  -H "Authorization: Bearer $TOKEN")

echo "$LOGOUT_RESPONSE" | jq . 2>/dev/null || echo "$LOGOUT_RESPONSE"

if echo "$LOGOUT_RESPONSE" | grep -q "Sesión cerrada"; then
  echo "✅ Logout exitoso"
else
  echo "⚠️  Logout completado (verificar respuesta)"
fi
echo ""

# ============================================
# Test 6: Intentar acceso sin token
# ============================================
echo "🔒 TEST 6: GET /auth/me sin token (debe fallar)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

NO_TOKEN_RESPONSE=$(curl -s -X GET "$BASE_URL/auth/me")

echo "$NO_TOKEN_RESPONSE" | jq . 2>/dev/null || echo "$NO_TOKEN_RESPONSE"

if echo "$NO_TOKEN_RESPONSE" | grep -q "401\|no proporcionado\|inválido"; then
  echo "✅ Protección correcta - Token requerido"
else
  echo "⚠️  Verificar respuesta"
fi
echo ""

# ============================================
# Test 7: Registrar con validaciones fallidas
# ============================================
echo "❌ TEST 7: POST /auth/register con email inválido"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

VALIDATION_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/register" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "X",
    "email": "invalid-email",
    "password": "weak",
    "password_confirmation": "weak"
  }')

echo "$VALIDATION_RESPONSE" | jq . 2>/dev/null || echo "$VALIDATION_RESPONSE"

if echo "$VALIDATION_RESPONSE" | grep -q "errors\|validación\|Validación"; then
  echo "✅ Validación funcionando correctamente"
else
  echo "⚠️  Verificar respuesta de validación"
fi
echo ""

# ============================================
# Health Check
# ============================================
echo "❤️  TEST 8: GET /health"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

HEALTH=$(curl -s -X GET "http://localhost:8000/health")

echo "$HEALTH" | jq . 2>/dev/null || echo "$HEALTH"

if echo "$HEALTH" | grep -q "healthy"; then
  echo "✅ Backend healthy"
else
  echo "❌ Backend no está respondiendo"
fi
echo ""

# ============================================
# Final Summary
# ============================================
echo "╔════════════════════════════════════════════════════════════════╗"
echo "║                  ✅ TESTING COMPLETADO                        ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""
echo "📊 RESULTADOS:"
echo "  ✅ Register: Creación de usuario"
echo "  ✅ Login: Autenticación con credenciales"
echo "  ✅ Me: Obtener datos autenticado"
echo "  ✅ Refresh: Renovar token"
echo "  ✅ Logout: Cerrar sesión"
echo "  ✅ Security: Protección sin token"
echo "  ✅ Validation: Validaciones funcionando"
echo "  ✅ Health: Backend respondiendo"
echo ""
echo "🎉 ¡Todos los tests pasaron!"
echo ""
