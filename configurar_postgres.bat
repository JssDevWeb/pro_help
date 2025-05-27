@echo off
REM Script para configurar PostgreSQL para ShelterConnect
REM Ejecutar con: configurar_postgres.bat

echo === Configurando PostgreSQL para ShelterConnect ===
echo.

echo 1. Verificando archivo .env...
IF NOT EXIST ".env" (
    echo [ERROR] No se encontró archivo .env
    echo Ejecute primero: copy .env.example .env
    pause
    exit /b 1
)

echo 2. Configurando .env para PostgreSQL...
powershell -Command "(Get-Content .env) -replace 'DB_CONNECTION=.*', 'DB_CONNECTION=pgsql' -replace 'DB_HOST=.*', 'DB_HOST=127.0.0.1' -replace 'DB_PORT=.*', 'DB_PORT=5432' -replace 'DB_DATABASE=.*', 'DB_DATABASE=shelterconnect' -replace 'DB_USERNAME=.*', 'DB_USERNAME=postgres' | Set-Content .env.temp"

echo 3. Por favor introduzca la contraseña de PostgreSQL:
set /p PGPASSWORD="Contraseña: "

powershell -Command "(Get-Content .env.temp) -replace 'DB_PASSWORD=.*', 'DB_PASSWORD=%PGPASSWORD%' | Set-Content .env"
del .env.temp

echo.
echo 4. Intentando conectarse a PostgreSQL...
echo (Si no tiene instalado psql en el PATH, necesitará crear la base de datos manualmente)

REM Intentar crear la base de datos si psql está disponible
psql -h localhost -U postgres -c "CREATE DATABASE shelterconnect;" 2>NUL
if %ERRORLEVEL% EQU 0 (
    echo Base de datos 'shelterconnect' creada correctamente
) else (
    echo [AVISO] No se pudo crear la base de datos automáticamente
    echo Por favor, cree la base de datos 'shelterconnect' manualmente
)

echo.
echo 5. Verificando extensión PostGIS (si está disponible)...
psql -h localhost -U postgres -d shelterconnect -c "CREATE EXTENSION IF NOT EXISTS postgis;" 2>NUL
if %ERRORLEVEL% EQU 0 (
    echo Extensión PostGIS habilitada correctamente
) else (
    echo [AVISO] No se pudo habilitar PostGIS automáticamente
    echo Es posible que necesite instalar la extensión PostGIS manualmente
)

echo.
echo === Configuración de PostgreSQL completada ===
echo Ahora puede ejecutar 'reinstalar_db.bat' para migrar y sembrar datos de prueba.
echo.
pause
