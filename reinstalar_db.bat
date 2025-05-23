@echo off
REM Reinstala la base de datos con datos de prueba
REM Ejecutar con: reinstalar_db.bat

echo === Reinstalando base de datos ShelterConnect ===
echo.

REM Verificar si hay archivos de migración duplicados primero
echo Verificando archivos de migración duplicados...
set DUPLICADOS=0

IF EXIST "database\migrations\2024_05_22_000002_create_organizations_table.php" set DUPLICADOS=1
IF EXIST "database\migrations\2024_05_22_000002_create_services_table.php" set DUPLICADOS=1
IF EXIST "database\migrations\2024_05_22_000003_create_beneficiaries_table.php" set DUPLICADOS=1
IF EXIST "database\migrations\2024_05_22_000004_create_interventions_table.php" set DUPLICADOS=1

IF %DUPLICADOS%==1 (
    echo [AVISO] Se detectaron archivos de migración duplicados.
    echo Ejecute primero 'corregir_migraciones.bat' para eliminar los duplicados.
    pause
    exit /b 1
)

echo 1. Ejecutando migraciones...
php artisan migrate:fresh --force
IF %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Las migraciones fallaron
    echo Si el error persiste, ejecute 'corregir_migraciones.bat' y vuelva a intentarlo.
    pause
    exit /b %ERRORLEVEL%
)

echo.
echo 2. Cargando datos de prueba...
php artisan db:seed --force
IF %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Los seeders fallaron
    pause
    exit /b %ERRORLEVEL%
)

echo.
echo === Base de datos reinstalada correctamente ===
echo La base de datos ha sido reinstalada con todos los datos de prueba.
echo.
pause
