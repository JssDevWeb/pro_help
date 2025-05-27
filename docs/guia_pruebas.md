# Guía de Prueba de API ShelterConnect

## Introducción

Esta guía muestra cómo ejecutar correctamente las pruebas de la API de ShelterConnect en diferentes terminales.

## Requisitos Previos

1. **Servidor Laravel en ejecución**
   - Asegúrate de que el servidor Laravel esté activo en el puerto 8000
   - Para iniciar el servidor: `php artisan serve`

2. **Base de datos preparada**
   - La base de datos debe tener las migraciones y semillas aplicadas
   - Para preparar la base de datos: `php artisan migrate:fresh --seed`

## Opciones de Prueba

Aquí tienes **tres** opciones diferentes para probar la API. Escoge la que mejor funcione en tu entorno:

### Opción 1: Script CMD Básico (Para todos los entornos Windows)

Este es el método más simple y debería funcionar en cualquier entorno Windows.

```cmd
test_api_cmd.bat
```

Este script:
- Funciona en el Símbolo del sistema (CMD) estándar de Windows
- Usa curl (disponible en Windows 10+)
- No requiere ajustes de políticas de ejecución
- Es básico pero efectivo para probar la funcionalidad

### Opción 2: PowerShell Simple (Recomendado)

Script PowerShell simplificado para evitar problemas de sintaxis:

```powershell
powershell -ExecutionPolicy Bypass -File simple_test.ps1
```

Este script:
- Es más limpio y fácil de entender
- Proporciona salida con colores más claros
- Evita problemas comunes de sintaxis PowerShell

### Opción 3: Pruebas Manuales con Postman o cURL

Si prefieres ejecutar pruebas manualmente:

#### Usando cURL

```bash
# 1. Login para obtener token
curl -X POST http://127.0.0.1:8000/api/login -H "Content-Type: application/json" -d '{"email":"admin@shelterconnect.org","password":"password"}'

# 2. Usar el token en las siguientes peticiones
curl -X GET http://127.0.0.1:8000/api/user -H "Authorization: Bearer TU_TOKEN"
```

#### Usando Postman

1. Crea una nueva colección "ShelterConnect API"
2. Configura una variable de colección `baseUrl` con valor `http://127.0.0.1:8000/api`
3. Configura una variable `token` que se actualice después del login
4. Crea peticiones para cada endpoint

## Solución de Problemas

### Problemas con PowerShell

Si PowerShell muestra errores de sintaxis:

1. **Política de ejecución**: Asegúrate de usar `-ExecutionPolicy Bypass`
2. **Versión de PowerShell**: Estos scripts funcionan mejor con PowerShell 5.1 o superior
3. **Archivos de texto**: Si hay problemas, verifica que el archivo tenga codificación UTF-8

### Problemas con Curl en CMD

1. **Versión de Windows**: Windows 10 o superior tiene curl integrado
2. **Curl no encontrado**: Asegúrate de tener curl instalado o usa una herramienta alternativa

### Problemas con el Servidor Laravel

1. **Puerto ocupado**: Si el puerto 8000 está ocupado, especifica otro: `php artisan serve --port=8001`
2. **Base de datos**: Comprueba que la base de datos está correctamente configurada

## Endpoints Principales para Pruebas Manuales

- **Login**: POST `/api/login` con credenciales
- **Usuario actual**: GET `/api/user` con token
- **Organizaciones**: GET `/api/organizations` con token
- **Servicios**: GET `/api/services` con token
- **Servicios cercanos**: GET `/api/services-nearby?lat=40.4168&lng=-3.7038&radius=10000` con token
- **Estado del sistema**: GET `/api/health` (público)
- **Logout**: POST `/api/logout` con token

## Nota Final

Recuerda que estos scripts de prueba están diseñados para un entorno de desarrollo. Puedes modificarlos según las necesidades de tu entorno específico.

Si encuentras problemas específicos, revisa los logs de Laravel para obtener más información:
```bash
cat storage/logs/laravel.log
```
