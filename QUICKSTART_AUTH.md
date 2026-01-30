# ⚡ GUÍA RÁPIDA: Autenticación JWT en 5 Minutos

**Cómo empezar a usar la API de autenticación**

---

## 🚀 Setup (2 minutos)

### 1. Configurar JWT_SECRET

```bash
# En el contenedor
docker-compose exec backend openssl rand -hex 32

# Copiar resultado a .env
JWT_SECRET=<valor_generado>

# Reiniciar backend
docker-compose restart backend
```

### 2. Ejecutar Migraciones

```bash
docker-compose exec backend php artisan migrate
```

### 3. Verificar que todo funciona

```bash
curl http://localhost:8000/health
# Debe retornar: { "status": "healthy" }
```

---

## 📝 Registro (1 minuto)

### Crear nueva cuenta

```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Juan Pérez",
    "email": "juan@example.com",
    "password": "MyPassword123!",
    "password_confirmation": "MyPassword123!"
  }'
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Usuario registrado exitosamente",
  "data": {
    "id": 1,
    "name": "Juan Pérez",
    "email": "juan@example.com",
    "created_at": "2026-01-30T10:30:00Z"
  },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

**Guardar el token para próximas requests:**
```bash
TOKEN="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

---

## 🔐 Login (1 minuto)

### Iniciar sesión

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "juan@example.com",
    "password": "MyPassword123!"
  }'
```

**Guardar token:**
```bash
TOKEN="<token_recibido>"
```

---

## 👤 Obtener Usuario (30 seg)

### Ver datos del usuario autenticado

```bash
curl -X GET http://localhost:8000/api/v1/auth/me \
  -H "Authorization: Bearer $TOKEN"
```

---

## 🔄 Renovar Token (30 seg)

### Antes de que expire (1 hora)

```bash
curl -X POST http://localhost:8000/api/v1/auth/refresh \
  -H "Authorization: Bearer $TOKEN"
```

**Nuevo token:**
```bash
TOKEN="<nuevo_token_recibido>"
```

---

## 🚪 Logout (30 seg)

### Cerrar sesión

```bash
curl -X POST http://localhost:8000/api/v1/auth/logout \
  -H "Authorization: Bearer $TOKEN"
```

---

## ✨ Tips Importantes

### 1. Requisitos de Contraseña

✅ Válida: `MyPassword123!`
- Mínimo 8 caracteres
- Máximo 255 caracteres
- 1 mayúscula
- 1 minúscula
- 1 número
- Caracteres especiales opcionales: `@$!%*?&._-`

❌ Inválida: `password123`
- Falta mayúscula

### 2. Email Debe Ser Único

```bash
# Esto falla si email ya existe
curl -X POST ... -d '{"email": "juan@example.com", ...}'
# Error 422: Este email ya está registrado.
```

### 3. Rate Limiting

```bash
# Máximo 5 intentos de login / 15 minutos por IP
# Si se excede, esperar 15 minutos o cambiar IP

# Máximo 3 registros / 60 minutos por IP
```

### 4. Token Storage (Frontend)

```javascript
// Guardar en localStorage
localStorage.setItem('auth_token', token);

// Enviar en requests
fetch('/api/v1/pokemon', {
  headers: {
    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
  }
});
```

### 5. Token Expiración

```
TTL: 60 minutos
Refresh TTL: 2 semanas

Si expira → 401 Unauthorized
Renovar con POST /auth/refresh antes que expire
```

---

## 🐛 Troubleshooting Rápido

### Error: "Token no proporcionado"

```bash
# Asegurar que header Authorization está siendo enviado
curl ... -H "Authorization: Bearer $TOKEN"

# Verificar formato: "Bearer <token>" (con espacio)
```

### Error: "Demasiados intentos"

```bash
# Esperar 15 minutos (login) o 60 minutos (registro)
# O cambiar IP/proxy
```

### Error: "Token expirado"

```bash
# Renovar token
curl -X POST http://localhost:8000/api/v1/auth/refresh \
  -H "Authorization: Bearer $TOKEN"

# O re-autenticar con login
```

### Error: "Email ya registrado"

```bash
# Usar otro email para registro
# O hacer login si ya existe
```

---

## 📊 Tabla de Referencia Rápida

| Operación | Método | Endpoint | Auth | Status |
|-----------|--------|----------|------|--------|
| Registrar | POST | `/auth/register` | ❌ | 201 |
| Login | POST | `/auth/login` | ❌ | 200 |
| Yo | GET | `/auth/me` | ✅ | 200 |
| Refresh | POST | `/auth/refresh` | ✅ | 200 |
| Logout | POST | `/auth/logout` | ✅ | 200 |

---

## 🧪 Script de Test Automático

```bash
# Ejecutar todos los tests
bash test-auth.sh

# Verificará:
✅ Registro
✅ Login
✅ Me
✅ Refresh
✅ Logout
✅ Protección sin token
✅ Validaciones
✅ Health check
```

---

## 📚 Documentación Completa

Para más detalles, revisar:

- **BACKEND_AUTH.md** - Guía completa de autenticación (800+ líneas)
- **FASE_3.1_COMPLETADA.md** - Resumen de implementación
- **FRONTEND_AUTH_INTEGRATION.md** - Guía para frontend (Next.js)
- **FASE_3.1_VISUAL.txt** - Resumen visual

---

## ✅ Checklist de Setup

- [ ] JWT_SECRET configurado en .env
- [ ] Migraciones ejecutadas
- [ ] Backend reiniciado
- [ ] Health check pasando
- [ ] Registro de usuario ejecutado
- [ ] Login funcionando
- [ ] Token siendo usado en requests
- [ ] Refresh token funcionando

---

## 🎯 Próximo Paso

Una vez que la autenticación esté trabajando, continúa con:

**Fase 3.2: Pokemon API**
- Consumir PokeAPI
- Implementar endpoints de pokemon
- Agregar caching
- Documentar

---

**Tiempo total:** ⏱️ 5 minutos
**Dificultad:** 🟢 Fácil
**Status:** ✅ Listo para usar
