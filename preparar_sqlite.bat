@echo off
REM Script para preparar una base de datos SQLite limpia para desarrollo
REM Ejecutar con: preparar_sqlite.bat

echo === Preparando base de datos SQLite limpia ===
echo.

echo 1. Creando directorio de base de datos si no existe...
IF NOT EXIST "database" mkdir database
IF NOT EXIST "database\database.sqlite" (
    echo 2. Creando archivo de base de datos SQLite vacío...
    echo. > database\database.sqlite
    echo Base de datos SQLite creada correctamente.
) ELSE (
    echo 2. Vaciando base de datos SQLite existente...
    copy /y NUL database\database.sqlite > NUL
    echo Base de datos SQLite reiniciada correctamente.
)

echo.
echo 3. Verificando configuración de .env...

IF NOT EXIST ".env" (
    echo [AVISO] No se encontró archivo .env. Creando uno desde .env.example...
    copy .env.example .env
    
    echo 4. Configurando .env para SQLite...
    powershell -Command "(Get-Content .env) -replace 'DB_CONNECTION=.*', 'DB_CONNECTION=sqlite' -replace 'DB_DATABASE=.*', 'DB_DATABASE=database/database.sqlite' | Set-Content .env"
    
    echo 5. Generando clave de aplicación...
    php artisan key:generate
) ELSE (
    echo 4. Actualizando configuración de base de datos en .env existente...
    powershell -Command "(Get-Content .env) -replace 'DB_CONNECTION=.*', 'DB_CONNECTION=sqlite' -replace 'DB_DATABASE=.*', 'DB_DATABASE=database/database.sqlite' | Set-Content .env"
)

echo.
echo === Base de datos SQLite preparada ===
echo Ahora puede ejecutar 'reinstalar_db.bat' para migrar y sembrar datos de prueba.
echo.
pause
