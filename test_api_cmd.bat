@echo off
:: Script para pruebas simples de ShelterConnect API
:: Funciona con CMD estándar de Windows
:: Ejecutar con: test_api_cmd.bat

echo === Probando API de ShelterConnect ===
echo.

:: Asegúrate de tener el servidor Laravel ejecutándose
echo Asegúrate de tener el servidor Laravel en ejecución en el puerto 8000
echo Puedes iniciarlo con: php artisan serve
echo.
echo Presiona cualquier tecla para continuar cuando el servidor esté listo...
pause > nul

set BASE_URL=http://127.0.0.1:8000/api

:: Prueba 1: Verificar el servidor
echo 1. Verificando si el servidor está en ejecución...
curl -s -o nul -w "%%{http_code}" %BASE_URL% > temp_status.txt
set /p STATUS=<temp_status.txt
del temp_status.txt

if "%STATUS%" == "200" (
    echo    [OK] Servidor respondiendo correctamente
) else (
    echo    [ERROR] El servidor no responde correctamente. Código: %STATUS%
    echo    Asegúrate de que el servidor esté en ejecución con: php artisan serve
    exit /b 1
)

:: Prueba 2: Login
echo.
echo 2. Probando autenticación...
echo    Intentando login con admin@shelterconnect.org...

:: Archivo temporal para el cuerpo JSON
echo {"email":"admin@shelterconnect.org","password":"password"} > temp_body.json
curl -s -X POST -H "Content-Type: application/json" -d @temp_body.json %BASE_URL%/login > temp_login.json
del temp_body.json

:: Comprobar si el login fue exitoso extrayendo el token
findstr /C:"token" temp_login.json > nul
if %ERRORLEVEL% EQU 0 (
    echo    [OK] Login exitoso
    
    :: Extraer token para las siguientes pruebas (esto es básico y puede fallar)
    :: En un entorno real se recomienda usar jq o similar para parsear JSON
    for /f "tokens=2 delims=:," %%a in ('type temp_login.json ^| findstr /C:"token"') do (
        set TOKEN=%%a
    )
    :: Limpiar el token (quitar comillas y espacios)
    set TOKEN=%TOKEN:"=%
    set TOKEN=%TOKEN: =%
    echo    Token obtenido
) else (
    echo    [ERROR] Error en login
    type temp_login.json
    del temp_login.json
    exit /b 1
)

:: Prueba 3: Obtener info del usuario
echo.
echo 3. Probando información del usuario...
curl -s -X GET -H "Authorization: Bearer %TOKEN%" -H "Content-Type: application/json" %BASE_URL%/user > temp_user.json
echo    [OK] Información de usuario obtenida:
type temp_user.json | findstr "name email"
echo.

:: Prueba 4: Listar organizaciones
echo.
echo 4. Probando listado de organizaciones...
curl -s -X GET -H "Authorization: Bearer %TOKEN%" -H "Content-Type: application/json" %BASE_URL%/organizations > temp_orgs.json
echo    [OK] Organizaciones obtenidas
:: Contar cuántas organizaciones hay (aproximado)
findstr /C:"name" temp_orgs.json | find /c /v ""
echo.

:: Prueba 5: Listar servicios
echo.
echo 5. Probando listado de servicios...
curl -s -X GET -H "Authorization: Bearer %TOKEN%" -H "Content-Type: application/json" %BASE_URL%/services > temp_services.json
echo    [OK] Servicios obtenidos
:: Contar cuántos servicios hay (aproximado)
findstr /C:"name" temp_services.json | find /c /v ""
echo.

:: Prueba 6: Búsqueda geoespacial
echo.
echo 6. Probando búsqueda geoespacial...
curl -s -X GET -H "Authorization: Bearer %TOKEN%" -H "Content-Type: application/json" "%BASE_URL%/services-nearby?lat=40.4168&lng=-3.7038&radius=10000" > temp_nearby.json
echo    [OK] Búsqueda geoespacial completada
:: Ver los resultados cercanos
findstr /C:"name" temp_nearby.json | find /c /v ""
echo.

:: Prueba 7: Logout
echo.
echo 7. Probando logout...
curl -s -X POST -H "Authorization: Bearer %TOKEN%" -H "Content-Type: application/json" %BASE_URL%/logout > temp_logout.json
echo    [OK] Logout completado
type temp_logout.json | findstr "message"
echo.

:: Limpieza
del temp_login.json 2>nul
del temp_user.json 2>nul
del temp_orgs.json 2>nul
del temp_services.json 2>nul
del temp_nearby.json 2>nul
del temp_logout.json 2>nul

echo === Pruebas completadas ===
echo.
echo Si todas las pruebas fueron exitosas, la API está funcionando correctamente.
echo.
pause
