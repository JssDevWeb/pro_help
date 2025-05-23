# ShelterConnect - Guía Rápida de Pruebas

## Ejecutando las pruebas de la API

Este documento provee instrucciones paso a paso para probar la API de ShelterConnect.

### 1. Preparación del entorno

**Reinstalar la base de datos:**
```
reinstalar_db.bat
```

**Iniciar el servidor Laravel:**
```
iniciar_servidor.bat
```
(Por defecto usa el puerto 8000. Para cambiar: `iniciar_servidor.bat 8001`)

### 2. Ejecutar pruebas

Escoge UNA de estas opciones de prueba:

**Opción 1: Script CMD (más compatible):**
```
test_api_cmd.bat
```

**Opción 2: Script PowerShell (mejor formato):**
```
powershell -ExecutionPolicy Bypass -File simple_test.ps1
```

### 3. Interpretación de resultados

- ✅ **[OK]** - La prueba fue exitosa
- ❌ **[ERROR]** - La prueba falló

### 4. Solución de problemas comunes

**Error: "No se puede conectar al servidor"**
- Asegúrate de que el servidor esté en ejecución (`iniciar_servidor.bat`)
- Confirma que el puerto no está bloqueado (prueba con otro puerto)

**Error: "Login falló"**
- Verifica que la base de datos esté correctamente instalada (`reinstalar_db.bat`)
- Comprueba que las credenciales sean correctas (admin@shelterconnect.org / password)

**Error: "No such file or directory" en scripts PowerShell**
- Asegúrate de estar ejecutando los scripts desde el directorio raíz del proyecto
- Utiliza PowerShell reciente (5.1+)

### 5. Documentación detallada

Para instrucciones más detalladas, consulta los siguientes recursos:

- [Guía completa de pruebas](docs/guia_pruebas.md)
- [Documentación de la API](docs/api.md)
- [Estado actual del proyecto](docs/estado_actual.md)

### 6. Para ejecutar pruebas individuales con cURL

**Login (obtener token):**
```
curl -X POST http://127.0.0.1:8000/api/login -H "Content-Type: application/json" -d "{\"email\":\"admin@shelterconnect.org\",\"password\":\"password\"}"
```

**Consultar usuario actual:**
```
curl -X GET http://127.0.0.1:8000/api/user -H "Authorization: Bearer TU_TOKEN" -H "Content-Type: application/json"
```

**Búsqueda geoespacial:**
```
curl -X GET "http://127.0.0.1:8000/api/services-nearby?lat=40.4168&lng=-3.7038&radius=10000" -H "Authorization: Bearer TU_TOKEN" -H "Content-Type: application/json"
```
