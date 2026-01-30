#!/bin/bash

###############################################################################
# test-favorites.sh
# 
# Script para testing manual de endpoints de Favoritos
# Incluye pruebas exitosas y casos de error
# Uso: bash test-favorites.sh
#
###############################################################################

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Variables
API_URL="http://localhost:8000/api/v1"
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0

# Headers
AUTH_HEADER=""

###############################################################################
# Funciones auxiliares
###############################################################################

print_header() {
    echo -e "${BLUE}╔════════════════════════════════════════════════════════╗${NC}"
    echo -e "${BLUE}║ Test Script: Favorites Endpoints                       ║${NC}"
    echo -e "${BLUE}╚════════════════════════════════════════════════════════╝${NC}"
    echo ""
}

print_section() {
    echo -e "\n${YELLOW}═══════════════════════════════════════════════════════${NC}"
    echo -e "${YELLOW}$1${NC}"
    echo -e "${YELLOW}═══════════════════════════════════════════════════════${NC}"
}

print_test() {
    echo -e "\n${BLUE}[TEST]${NC} $1"
}

print_request() {
    echo -e "${BLUE}Request:${NC} $1"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
    ((PASSED_TESTS++))
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
    ((FAILED_TESTS++))
}

print_info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_summary() {
    echo -e "\n${BLUE}╔════════════════════════════════════════════════════════╗${NC}"
    echo -e "${BLUE}║ Test Summary                                           ║${NC}"
    echo -e "${BLUE}╚════════════════════════════════════════════════════════╝${NC}"
    
    echo -e "Total Tests:  ${BLUE}$TOTAL_TESTS${NC}"
    echo -e "Passed:       ${GREEN}$PASSED_TESTS${NC}"
    echo -e "Failed:       ${RED}$FAILED_TESTS${NC}"
    
    if [ $FAILED_TESTS -eq 0 ]; then
        echo -e "\n${GREEN}All tests passed! ✓${NC}"
    else
        echo -e "\n${RED}Some tests failed! ✗${NC}"
    fi
}

###############################################################################
# Tests
###############################################################################

setup_auth() {
    print_section "SETUP: Obtener JWT Token"
    
    # Registrar usuario
    print_test "Register new user"
    
    RESPONSE=$(curl -s -X POST "$API_URL/auth/register" \
        -H "Content-Type: application/json" \
        -d '{
            "name": "Test User",
            "email": "test-'$(date +%s)'@example.com",
            "password": "Password123!",
            "password_confirmation": "Password123!"
        }')
    
    TOKEN=$(echo $RESPONSE | grep -o '"token":"[^"]*' | cut -d'"' -f4)
    
    if [ -z "$TOKEN" ]; then
        print_error "Failed to get auth token"
        echo "Response: $RESPONSE"
        exit 1
    fi
    
    print_success "Got JWT token: ${TOKEN:0:20}..."
    AUTH_HEADER="Authorization: Bearer $TOKEN"
}

test_post_favorite_success() {
    print_section "POST /favorites - Success"
    print_test "Add Pokemon 1 to favorites"
    
    ((TOTAL_TESTS++))
    
    RESPONSE=$(curl -s -X POST "$API_URL/favorites" \
        -H "Content-Type: application/json" \
        -H "$AUTH_HEADER" \
        -d '{
            "pokemon_id": 1
        }')
    
    STATUS=$(echo $RESPONSE | grep -o '"success":true' || echo "false")
    
    if [ "$STATUS" = '"success":true' ]; then
        print_success "Pokemon added to favorites (201)"
        echo "$RESPONSE" | jq '.'
    else
        print_error "Failed to add Pokemon to favorites"
        echo "$RESPONSE" | jq '.'
    fi
}

test_post_favorite_duplicate() {
    print_section "POST /favorites - Duplicate Error"
    print_test "Try adding same Pokemon twice"
    
    ((TOTAL_TESTS++))
    
    RESPONSE=$(curl -s -X POST "$API_URL/favorites" \
        -H "Content-Type: application/json" \
        -H "$AUTH_HEADER" \
        -d '{
            "pokemon_id": 1
        }')
    
    STATUS=$(echo $RESPONSE | grep -o '"success":false' || echo "failed")
    
    if [ "$STATUS" = '"success":false' ]; then
        print_success "Correctly rejected duplicate favorite (409)"
        echo "$RESPONSE" | jq '.'
    else
        print_error "Should have rejected duplicate"
        echo "$RESPONSE" | jq '.'
    fi
}

test_post_favorite_invalid_id() {
    print_section "POST /favorites - Invalid Pokemon ID"
    print_test "Try adding Pokemon with invalid ID (999)"
    
    ((TOTAL_TESTS++))
    
    RESPONSE=$(curl -s -X POST "$API_URL/favorites" \
        -H "Content-Type: application/json" \
        -H "$AUTH_HEADER" \
        -d '{
            "pokemon_id": 999
        }')
    
    ERROR=$(echo $RESPONSE | grep -o '"error"' || echo "no_error")
    
    if [ "$ERROR" = '"error"' ]; then
        print_success "Correctly rejected invalid Pokemon ID (400)"
        echo "$RESPONSE" | jq '.'
    else
        print_error "Should have rejected invalid ID"
        echo "$RESPONSE" | jq '.'
    fi
}

test_post_favorite_missing_id() {
    print_section "POST /favorites - Missing pokemon_id"
    print_test "Try adding favorite without pokemon_id"
    
    ((TOTAL_TESTS++))
    
    RESPONSE=$(curl -s -w "\n%{http_code}" -X POST "$API_URL/favorites" \
        -H "Content-Type: application/json" \
        -H "$AUTH_HEADER" \
        -d '{}')
    
    HTTP_CODE=$(echo "$RESPONSE" | tail -n1)
    
    if [ "$HTTP_CODE" = "422" ]; then
        print_success "Correctly rejected missing pokemon_id (422)"
    else
        print_error "Should have rejected with 422, got $HTTP_CODE"
    fi
}

test_get_favorites() {
    print_section "GET /favorites - List"
    print_test "Get user's favorites"
    
    ((TOTAL_TESTS++))
    
    RESPONSE=$(curl -s -X GET "$API_URL/favorites" \
        -H "Content-Type: application/json" \
        -H "$AUTH_HEADER")
    
    STATUS=$(echo $RESPONSE | grep -o '"success":true' || echo "false")
    
    if [ "$STATUS" = '"success":true' ]; then
        print_success "Retrieved favorites list (200)"
        echo "$RESPONSE" | jq '.'
    else
        print_error "Failed to retrieve favorites"
        echo "$RESPONSE" | jq '.'
    fi
}

test_delete_favorite() {
    print_section "DELETE /favorites/{pokemon_id}"
    print_test "Remove Pokemon 1 from favorites"
    
    ((TOTAL_TESTS++))
    
    RESPONSE=$(curl -s -X DELETE "$API_URL/favorites/1" \
        -H "Content-Type: application/json" \
        -H "$AUTH_HEADER")
    
    STATUS=$(echo $RESPONSE | grep -o '"success":true' || echo "false")
    
    if [ "$STATUS" = '"success":true' ]; then
        print_success "Pokemon removed from favorites (200)"
        echo "$RESPONSE" | jq '.'
    else
        print_error "Failed to remove Pokemon from favorites"
        echo "$RESPONSE" | jq '.'
    fi
}

test_delete_favorite_not_found() {
    print_section "DELETE /favorites - Not Found"
    print_test "Try deleting non-existent favorite"
    
    ((TOTAL_TESTS++))
    
    RESPONSE=$(curl -s -w "\n%{http_code}" -X DELETE "$API_URL/favorites/999" \
        -H "Content-Type: application/json" \
        -H "$AUTH_HEADER")
    
    HTTP_CODE=$(echo "$RESPONSE" | tail -n1)
    
    if [ "$HTTP_CODE" = "404" ]; then
        print_success "Correctly returned 404 for non-existent favorite"
    else
        print_error "Should have returned 404, got $HTTP_CODE"
    fi
}

test_unauthorized() {
    print_section "Auth Tests - Unauthorized"
    print_test "Try POST /favorites without token"
    
    ((TOTAL_TESTS++))
    
    RESPONSE=$(curl -s -w "\n%{http_code}" -X POST "$API_URL/favorites" \
        -H "Content-Type: application/json" \
        -d '{
            "pokemon_id": 1
        }')
    
    HTTP_CODE=$(echo "$RESPONSE" | tail -n1)
    
    if [ "$HTTP_CODE" = "401" ]; then
        print_success "Correctly rejected unauthorized request (401)"
    else
        print_error "Should have rejected with 401, got $HTTP_CODE"
    fi
}

test_pagination() {
    print_section "GET /favorites - Pagination"
    print_test "Get favorites with pagination"
    
    ((TOTAL_TESTS++))
    
    # Agregar múltiples favoritos
    for i in {2..5}; do
        curl -s -X POST "$API_URL/favorites" \
            -H "Content-Type: application/json" \
            -H "$AUTH_HEADER" \
            -d "{\"pokemon_id\": $i}" > /dev/null
    done
    
    RESPONSE=$(curl -s -X GET "$API_URL/favorites?page=1&per_page=2" \
        -H "Content-Type: application/json" \
        -H "$AUTH_HEADER")
    
    TOTAL=$(echo $RESPONSE | grep -o '"total":[0-9]*' | cut -d':' -f2)
    
    if [ ! -z "$TOTAL" ] && [ "$TOTAL" -gt 0 ]; then
        print_success "Retrieved paginated favorites (total: $TOTAL)"
        echo "$RESPONSE" | jq '.pagination'
    else
        print_error "Failed to retrieve paginated favorites"
    fi
}

###############################################################################
# Main
###############################################################################

main() {
    print_header
    
    print_warning "Make sure Docker containers are running:"
    print_warning "docker-compose up -d"
    
    read -p "Press Enter to continue..." -t 5 || true
    
    # Setup
    setup_auth
    
    # Tests
    test_post_favorite_success
    test_post_favorite_duplicate
    test_post_favorite_invalid_id
    test_post_favorite_missing_id
    test_get_favorites
    test_delete_favorite
    test_delete_favorite_not_found
    test_unauthorized
    test_pagination
    
    # Summary
    print_summary
}

main
