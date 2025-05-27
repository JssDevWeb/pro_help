@echo off
REM Script para corregir duplicados en migraciones
REM Ejecutar con: corregir_migraciones.bat

echo === Corrigiendo migraciones duplicadas ===
echo.

REM Eliminar archivos de migración duplicados y vacíos
echo 1. Eliminando archivos duplicados...

IF EXIST database\migrations\2024_05_22_000002_create_organizations_table.php (
    del database\migrations\2024_05_22_000002_create_organizations_table.php
    echo    - Eliminado: 2024_05_22_000002_create_organizations_table.php
)

IF EXIST database\migrations\2024_05_22_000002_create_services_table.php (
    del database\migrations\2024_05_22_000002_create_services_table.php
    echo    - Eliminado: 2024_05_22_000002_create_services_table.php
)

IF EXIST database\migrations\2024_05_22_000003_create_beneficiaries_table.php (
    del database\migrations\2024_05_22_000003_create_beneficiaries_table.php
    echo    - Eliminado: 2024_05_22_000003_create_beneficiaries_table.php
)

IF EXIST database\migrations\2024_05_22_000004_create_interventions_table.php (
    del database\migrations\2024_05_22_000004_create_interventions_table.php
    echo    - Eliminado: 2024_05_22_000004_create_interventions_table.php
)

echo.
echo 2. Validando migraciones restantes...

REM Listar migraciones restantes para verificar
dir database\migrations\*.php /b

echo.
echo === Migración corregida ===
echo Ahora puede ejecutar 'reinstalar_db.bat' nuevamente para completar la instalación.
echo.
pause
