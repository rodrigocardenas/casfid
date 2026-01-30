#!/bin/bash

# 🧪 Test Suite para Fase 3.2 - Pokemon API
#
# Script para probar todos los endpoints de Pokémon
# Uso: bash test-pokemon.sh
#

set -e

# Colores para output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuración
API_URL="http://localhost:8000/api/v1"
PASS_COUNT=0
FAIL_COUNT=0

echo -e "${BLUE}╔════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║      🧪 POKEMON API TEST SUITE - FASE 3.2         ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════╝${NC}"
echo ""

# ============================================================================
# Helper Functions
# ============================================================================

test_endpoint() {
    local test_name="$1"
    local method="$2"
    local endpoint="$3"
    local expected_status="$4"
    local description="$5"

    echo -e "${YELLOW}Test: $test_name${NC}"
    echo "  → $description"

    # Ejecutar curl
    response=$(curl -s -w "\n%{http_code}" -X "$method" "$API_URL$endpoint" -H "Accept: application/json")

    # Separar body y status code
    http_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | head -n-1)

    # Verificar status code
    if [ "$http_code" = "$expected_status" ]; then
        echo -e "  ${GREEN}✓ Status: $http_code (expected $expected_status)${NC}"
        PASS_COUNT=$((PASS_COUNT + 1))

        # Mostrar respuesta si es 200
        if [ "$http_code" = "200" ]; then
            # Verificar que es JSON válido
            if echo "$body" | jq . > /dev/null 2>&1; then
                echo -e "  ${GREEN}✓ Valid JSON response${NC}"
                # Mostrar estructura básica
                if echo "$body" | jq -e '.success' > /dev/null 2>&1; then
                    success=$(echo "$body" | jq -r '.success')
                    echo -e "  ${GREEN}✓ success: $success${NC}"
                fi
            else
                echo -e "  ${RED}✗ Invalid JSON${NC}"
                FAIL_COUNT=$((FAIL_COUNT + 1))
            fi
        fi
    else
        echo -e "  ${RED}✗ Status: $http_code (expected $expected_status)${NC}"
        FAIL_COUNT=$((FAIL_COUNT + 1))
        echo "  Response: $body"
    fi
    echo ""
}

# ============================================================================
# Test 1: GET /pokemon (Listado básico)
# ============================================================================

test_endpoint \
    "1. Pokemon Listado Básico" \
    "GET" \
    "/pokemon" \
    "200" \
    "Obtener listado completo de Pokémon"

# ============================================================================
# Test 2: GET /pokemon con paginación
# ============================================================================

test_endpoint \
    "2. Pokemon Listado con Página 2" \
    "GET" \
    "/pokemon?page=2&per_page=10" \
    "200" \
    "Obtener página 2 con 10 items"

# ============================================================================
# Test 3: GET /pokemon con búsqueda
# ============================================================================

test_endpoint \
    "3. Pokemon Búsqueda por Nombre" \
    "GET" \
    "/pokemon?search=bulbasaur" \
    "200" \
    "Buscar por nombre 'bulbasaur'"

# ============================================================================
# Test 4: GET /pokemon con filtro de tipo
# ============================================================================

test_endpoint \
    "4. Pokemon Filtro por Tipo" \
    "GET" \
    "/pokemon?type=water" \
    "200" \
    "Filtrar pokémon de tipo water"

# ============================================================================
# Test 5: GET /pokemon con filtro y búsqueda combinados
# ============================================================================

test_endpoint \
    "5. Pokemon Filtro + Búsqueda" \
    "GET" \
    "/pokemon?type=grass&search=bulba" \
    "200" \
    "Filtrar tipo grass Y buscar 'bulba'"

# ============================================================================
# Test 6: GET /pokemon/{id} (Detalles)
# ============================================================================

test_endpoint \
    "6. Pokemon Detalle #1 (Bulbasaur)" \
    "GET" \
    "/pokemon/1" \
    "200" \
    "Obtener detalles de Pokémon #1"

# ============================================================================
# Test 7: GET /pokemon/{id} (Otro)
# ============================================================================

test_endpoint \
    "7. Pokemon Detalle #25 (Pikachu)" \
    "GET" \
    "/pokemon/25" \
    "200" \
    "Obtener detalles de Pokémon #25"

# ============================================================================
# Test 8: GET /pokemon/{id} fuera de rango
# ============================================================================

test_endpoint \
    "8. Pokemon ID Inválido (999)" \
    "GET" \
    "/pokemon/999" \
    "400" \
    "ID fuera de rango debe retornar 400"

# ============================================================================
# Test 9: GET /pokemon/{id} No encontrado (mayor a 150)
# ============================================================================

test_endpoint \
    "9. Pokemon ID > 150" \
    "GET" \
    "/pokemon/151" \
    "400" \
    "ID 151 fuera de generación 1 debe retornar 400"

# ============================================================================
# Test 10: GET /pokemon/filters (Tipos disponibles)
# ============================================================================

test_endpoint \
    "10. Pokemon Filtros (Tipos)" \
    "GET" \
    "/pokemon/filters" \
    "200" \
    "Obtener lista de tipos disponibles"

# ============================================================================
# Test 11: GET /pokemon con per_page excesivo
# ============================================================================

test_endpoint \
    "11. Pokemon per_page Máximo" \
    "GET" \
    "/pokemon?per_page=50" \
    "200" \
    "per_page=50 es válido (máximo permitido)"

# ============================================================================
# Test 12: GET /pokemon con per_page inválido
# ============================================================================

test_endpoint \
    "12. Pokemon per_page > 50" \
    "GET" \
    "/pokemon?per_page=100" \
    "422" \
    "per_page > 50 debe retornar 422 (validación)"

# ============================================================================
# Test 13: Verificar estructura de respuesta
# ============================================================================

echo -e "${YELLOW}Test: 13. Estructura de Respuesta${NC}"
echo "  → Verificar que respuesta tiene estructura correcta"

response=$(curl -s "$API_URL/pokemon?page=1&per_page=3" | jq .)

if echo "$response" | jq -e '.success' > /dev/null 2>&1; then
    echo -e "  ${GREEN}✓ Has 'success' field${NC}"
    PASS_COUNT=$((PASS_COUNT + 1))
else
    echo -e "  ${RED}✗ Missing 'success' field${NC}"
    FAIL_COUNT=$((FAIL_COUNT + 1))
fi

if echo "$response" | jq -e '.data' > /dev/null 2>&1; then
    echo -e "  ${GREEN}✓ Has 'data' array${NC}"
    PASS_COUNT=$((PASS_COUNT + 1))
else
    echo -e "  ${RED}✗ Missing 'data' array${NC}"
    FAIL_COUNT=$((FAIL_COUNT + 1))
fi

if echo "$response" | jq -e '.pagination' > /dev/null 2>&1; then
    echo -e "  ${GREEN}✓ Has 'pagination' object${NC}"
    PASS_COUNT=$((PASS_COUNT + 1))
else
    echo -e "  ${RED}✗ Missing 'pagination' object${NC}"
    FAIL_COUNT=$((FAIL_COUNT + 1))
fi

if echo "$response" | jq -e '.timestamp' > /dev/null 2>&1; then
    echo -e "  ${GREEN}✓ Has 'timestamp' field${NC}"
    PASS_COUNT=$((PASS_COUNT + 1))
else
    echo -e "  ${RED}✗ Missing 'timestamp' field${NC}"
    FAIL_COUNT=$((FAIL_COUNT + 1))
fi

echo ""

# ============================================================================
# Test 14: Verificar estructura de Pokémon
# ============================================================================

echo -e "${YELLOW}Test: 14. Estructura de Pokémon${NC}"
echo "  → Verificar que cada Pokémon tiene campos requeridos"

pokemon=$(curl -s "$API_URL/pokemon?page=1&per_page=1" | jq '.data[0]')

if echo "$pokemon" | jq -e '.id' > /dev/null 2>&1; then
    echo -e "  ${GREEN}✓ Has 'id'${NC}"
    PASS_COUNT=$((PASS_COUNT + 1))
else
    echo -e "  ${RED}✗ Missing 'id'${NC}"
    FAIL_COUNT=$((FAIL_COUNT + 1))
fi

if echo "$pokemon" | jq -e '.name' > /dev/null 2>&1; then
    echo -e "  ${GREEN}✓ Has 'name'${NC}"
    PASS_COUNT=$((PASS_COUNT + 1))
else
    echo -e "  ${RED}✗ Missing 'name'${NC}"
    FAIL_COUNT=$((FAIL_COUNT + 1))
fi

if echo "$pokemon" | jq -e '.image' > /dev/null 2>&1; then
    echo -e "  ${GREEN}✓ Has 'image'${NC}"
    PASS_COUNT=$((PASS_COUNT + 1))
else
    echo -e "  ${RED}✗ Missing 'image'${NC}"
    FAIL_COUNT=$((FAIL_COUNT + 1))
fi

if echo "$pokemon" | jq -e '.types' > /dev/null 2>&1; then
    echo -e "  ${GREEN}✓ Has 'types'${NC}"
    PASS_COUNT=$((PASS_COUNT + 1))
else
    echo -e "  ${RED}✗ Missing 'types'${NC}"
    FAIL_COUNT=$((FAIL_COUNT + 1))
fi

echo ""

# ============================================================================
# Test 15: Verificar estructura de Pokémon detalle
# ============================================================================

echo -e "${YELLOW}Test: 15. Estructura de Pokémon Detalle${NC}"
echo "  → Verificar campos adicionales en detalle"

detail=$(curl -s "$API_URL/pokemon/1" | jq '.data')

if echo "$detail" | jq -e '.height' > /dev/null 2>&1; then
    echo -e "  ${GREEN}✓ Has 'height'${NC}"
    PASS_COUNT=$((PASS_COUNT + 1))
else
    echo -e "  ${RED}✗ Missing 'height'${NC}"
    FAIL_COUNT=$((FAIL_COUNT + 1))
fi

if echo "$detail" | jq -e '.weight' > /dev/null 2>&1; then
    echo -e "  ${GREEN}✓ Has 'weight'${NC}"
    PASS_COUNT=$((PASS_COUNT + 1))
else
    echo -e "  ${RED}✗ Missing 'weight'${NC}"
    FAIL_COUNT=$((FAIL_COUNT + 1))
fi

if echo "$detail" | jq -e '.abilities' > /dev/null 2>&1; then
    echo -e "  ${GREEN}✓ Has 'abilities'${NC}"
    PASS_COUNT=$((PASS_COUNT + 1))
else
    echo -e "  ${RED}✗ Missing 'abilities'${NC}"
    FAIL_COUNT=$((FAIL_COUNT + 1))
fi

if echo "$detail" | jq -e '.stats' > /dev/null 2>&1; then
    echo -e "  ${GREEN}✓ Has 'stats'${NC}"
    PASS_COUNT=$((PASS_COUNT + 1))
else
    echo -e "  ${RED}✗ Missing 'stats'${NC}"
    FAIL_COUNT=$((FAIL_COUNT + 1))
fi

echo ""

# ============================================================================
# Resumen
# ============================================================================

TOTAL=$((PASS_COUNT + FAIL_COUNT))

echo -e "${BLUE}╔════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║                   RESUMEN DE TESTS                ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "Total Tests: ${TOTAL}"
echo -e "Passed:  ${GREEN}${PASS_COUNT}${NC}"
echo -e "Failed:  ${RED}${FAIL_COUNT}${NC}"
echo ""

if [ $FAIL_COUNT -eq 0 ]; then
    echo -e "${GREEN}╔════════════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║          ✅ TODOS LOS TESTS PASARON ✅             ║${NC}"
    echo -e "${GREEN}╚════════════════════════════════════════════════════╝${NC}"
    exit 0
else
    echo -e "${RED}╔════════════════════════════════════════════════════╗${NC}"
    echo -e "${RED}║          ❌ ALGUNOS TESTS FALLARON ❌              ║${NC}"
    echo -e "${RED}╚════════════════════════════════════════════════════╝${NC}"
    exit 1
fi
