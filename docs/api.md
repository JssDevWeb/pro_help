# API Documentation

## Autenticación

### POST /api/login
**Descripción:** Autentica un usuario y devuelve un token de acceso.  
**Request:**
```json
{
  "email": "user@example.com",
  "password": "secret"
}
```
**Response (200):**
```json
{
  "token": "abc123...",
  "user": {
    "id": 1,
    "name": "Demo User",
    "email": "user@example.com",
    "role": "social_worker"
  }
}
```

### POST /api/logout
**Descripción:** Cierra la sesión del usuario y revoca el token.  
**Headers requeridos:** Authorization: Bearer {token}  
**Response (200):**
```json
{
  "message": "Successfully logged out"
}
```

### GET /api/user
**Descripción:** Obtiene información del usuario autenticado.  
**Headers requeridos:** Authorization: Bearer {token}  
**Response (200):**
```json
{
  "id": 1,
  "name": "Demo User",
  "email": "user@example.com",
  "role": "social_worker",
  "organization_id": 1
}
```

## Organizaciones

### GET /api/organizations
**Descripción:** Obtiene lista de organizaciones.  
**Headers requeridos:** Authorization: Bearer {token}  
**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Cruz Roja Madrid",
      "description": "Organización humanitaria en Madrid",
      "email": "contacto@cruzrojamadrid.es",
      "phone": "+34911234567",
      "location": {
        "lat": 40.4168,
        "lng": -3.7038
      },
      "address": "Calle del Prado, 21, Madrid"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 10
  }
}
```

### GET /api/organizations/{id}
**Descripción:** Obtiene detalles de una organización específica.  
**Headers requeridos:** Authorization: Bearer {token}  
**Response (200):**
```json
{
  "id": 1,
  "name": "Cruz Roja Madrid",
  "description": "Organización humanitaria en Madrid",
  "email": "contacto@cruzrojamadrid.es",
  "phone": "+34911234567",
  "location": {
    "lat": 40.4168,
    "lng": -3.7038
  },
  "address": "Calle del Prado, 21, Madrid",
  "services": [
    {
      "id": 1,
      "name": "Comedor Social Madrid Centro",
      "type": "food"
    }
  ]
}
```

## Servicios

### GET /api/services
**Descripción:** Obtiene lista de servicios disponibles.  
**Headers requeridos:** Authorization: Bearer {token}  
**Parámetros Query:**
- `lat`: Latitud para búsqueda por proximidad
- `lng`: Longitud para búsqueda por proximidad
- `radius`: Radio de búsqueda en metros (opcional, default: 5000)
- `type`: Filtro por tipo de servicio (opcional)

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Comedor Social Centro",
      "organization": "Cáritas",
      "type": "food",
      "location": {
        "lat": 40.4168,
        "lng": -3.7038
      },
      "address": "Calle Example 123",
      "schedule": "Lu-Vi 9:00-18:00",
      "distance": 1520 // distancia en metros desde las coordenadas proporcionadas
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 50
  }
}
```

### GET /api/services/{id}
**Descripción:** Obtiene detalles de un servicio específico.  
**Headers requeridos:** Authorization: Bearer {token}  
**Response (200):**
```json
{
  "id": 1,
  "name": "Comedor Social Centro",
  "organization": {
    "id": 1,
    "name": "Cáritas"
  },
  "type": "food",
  "description": "Servicio de comidas diarias",
  "location": {
    "lat": 40.4168,
    "lng": -3.7038
  },
  "address": "Calle Example 123",
  "schedule": {
    "monday": "9:00-17:00",
    "tuesday": "9:00-17:00",
    "wednesday": "9:00-17:00",
    "thursday": "9:00-17:00",
    "friday": "9:00-17:00"
  },
  "capacity": 100,
  "is_active": true
}
```

## Beneficiarios

### GET /api/beneficiaries
**Descripción:** Obtiene lista de beneficiarios.  
**Headers requeridos:** Authorization: Bearer {token}  
**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Juan Pérez",
      "identification": "A1234567B",
      "birth_date": "1985-06-15",
      "gender": "male",
      "needs": ["food", "shelter"]
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 25
  }
}
```

### GET /api/beneficiaries/{id}
**Descripción:** Obtiene detalles de un beneficiario específico.  
**Headers requeridos:** Authorization: Bearer {token}  
**Response (200):**
```json
{
  "id": 1,
  "name": "Juan Pérez",
  "identification": "A1234567B",
  "birth_date": "1985-06-15",
  "gender": "male",
  "phone": "+34600123456",
  "email": "juan.perez@mail.com",
  "address": "Calle Mayor 15, Madrid",
  "location": {
    "lat": 40.4165,
    "lng": -3.7026
  },
  "needs": ["food", "shelter"],
  "status": "active",
  "interventions": [
    {
      "id": 1,
      "service_id": 1,
      "service_name": "Comedor Social Madrid Centro",
      "start_date": "2024-05-22",
      "status": "active"
    }
  ]
}
```

## Intervenciones

### GET /api/interventions
**Descripción:** Obtiene lista de intervenciones.  
**Headers requeridos:** Authorization: Bearer {token}  
**Parámetros Query:**
- `beneficiary_id`: Filtro por beneficiario (opcional)
- `service_id`: Filtro por servicio (opcional)
- `status`: Filtro por estado (opcional)

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "beneficiary": {
        "id": 1,
        "name": "Juan Pérez"
      },
      "service": {
        "id": 1,
        "name": "Comedor Social Madrid Centro"
      },
      "start_date": "2024-05-22",
      "end_date": "2024-06-22",
      "status": "active",
      "type": "food_assistance"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 15
  }
}
```

## Funcionalidades Especiales
- `GET /api/services-nearby` - Búsqueda geoespacial de servicios
- `GET /api/health` - Estado de salud del sistema
- `GET /api/stats` - Estadísticas generales
- `GET /api/stats/services` - Estadísticas por servicio

## Endpoints Geoespaciales Avanzados

### GET /api/geospatial-stats
**Descripción:** Retorna datos estadísticos de distribución geoespacial para visualización en mapas de calor.  
**Headers requeridos:** Authorization: Bearer {token}  
**Parámetros Query:**
- `type`: Tipo de estadística (density, coverage)
- `bounds`: Límites del área a analizar (opcional)

**Response (200):**
```json
{
  "points": [
    {
      "lat": 40.4168,
      "lng": -3.7038,
      "weight": 8.5  // peso de este punto para mapa de calor
    },
    // más puntos...
  ],
  "metadata": {
    "total_services": 150,
    "coverage_percentage": 65,
    "highest_concentration": "Centro",
    "lowest_concentration": "Periferia Sur"
  }
}
```

### GET /api/services-nearby
**Descripción:** Busca servicios cercanos a una ubicación específica.  
**Headers requeridos:** Authorization: Bearer {token}  
**Parámetros Query:**
- `lat`: Latitud (obligatorio)
- `lng`: Longitud (obligatorio)
- `radius`: Radio en metros (opcional, default: 5000)
- `type`: Filtro por tipo de servicio (opcional)

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Comedor Social Centro",
      "type": "food",
      "location": {
        "lat": 40.4168,
        "lng": -3.7038
      },
      "distance": 1520,  // distancia en metros
      "organization": {
        "id": 1,
        "name": "Cáritas"
      }
    }
  ],
  "metadata": {
    "count": 5,
    "closest": 320, // metros
    "farthest": 4800 // metros
  }
}
```

## Estado actual de la implementación

✅ **Sanctum configurado**
- Migración de personal_access_tokens ejecutada
- Middleware de autenticación configurado en rutas API

✅ **AuthController implementado**
- Métodos login, logout y user funcionando
- Validación de credenciales y manejo de errores

✅ **ResourceControllers implementados**
- OrganizationController: CRUD completo
- ServiceController: CRUD completo con funcionalidades geoespaciales
- BeneficiaryController: CRUD completo
- InterventionController: CRUD completo

✅ **Rutas API configuradas**
- Rutas agrupadas bajo middleware de autenticación
- Implementación RESTful para todos los recursos
- Endpoints geoespaciales funcionando
