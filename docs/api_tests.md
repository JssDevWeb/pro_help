# Test de API ShelterConnect

## Prueba de autenticación

### Login
```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@shelterconnect.org",
    "password": "password"
  }'
```

### Obtener información del usuario (requiere token)
```bash
curl -X GET http://127.0.0.1:8000/api/user \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Prueba de servicios geoespaciales

### Buscar servicios cercanos a Madrid Centro
```bash
curl -X GET "http://127.0.0.1:8000/api/services-nearby?lat=40.4168&lng=-3.7038&radius=10000" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Buscar servicios de comida cercanos
```bash
curl -X GET "http://127.0.0.1:8000/api/services-nearby?lat=40.4168&lng=-3.7038&radius=5000&type=food" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Prueba de organizaciones

### Listar organizaciones
```bash
curl -X GET http://127.0.0.1:8000/api/organizations \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Obtener detalles de una organización
```bash
curl -X GET http://127.0.0.1:8000/api/organizations/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Prueba de beneficiarios

### Listar beneficiarios
```bash
curl -X GET http://127.0.0.1:8000/api/beneficiaries \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Obtener detalles de un beneficiario
```bash
curl -X GET http://127.0.0.1:8000/api/beneficiaries/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Prueba de intervenciones

### Listar intervenciones
```bash
curl -X GET http://127.0.0.1:8000/api/interventions \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Crear nueva intervención
```bash
curl -X POST http://127.0.0.1:8000/api/interventions \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "beneficiary_id": 1,
    "service_id": 1,
    "start_date": "2024-05-23",
    "end_date": "2024-06-23",
    "type": "food_assistance",
    "notes": "Asistencia alimentaria semanal"
  }'
```

## Credenciales de prueba disponibles:

- **Admin**: admin@shelterconnect.org / password
- **Manager**: manager@cruzrojamadrid.es / password  
- **Provider**: provider@caritasbarcelona.org / password
