@echo off
REM Script para solucionar problemas con las migraciones
REM Ejecutar con: reset_migrations.bat

echo === Solucionando Problemas de Migraciones ===
echo.

echo Este script hará lo siguiente:
echo  1. Eliminar migraciones duplicadas
echo  2. Eliminar archivos de migración vacíos
echo  3. Limpiar la base de datos
echo  4. Ejecutar migraciones limpias
echo  5. Sembrar datos de prueba
echo.
echo ADVERTENCIA: Se perderán todos los datos existentes
echo.
set /p CONFIRMAR="¿Desea continuar? (S/N): "
if /i "%CONFIRMAR%" neq "S" exit /b 0

echo.
echo 1. Eliminando archivos de migración problemáticos...

REM Lista de migraciones a eliminar
IF EXIST database\migrations\2024_05_22_000002_create_organizations_table.php (
    del database\migrations\2024_05_22_000002_create_organizations_table.php
    echo   - Eliminado: 2024_05_22_000002_create_organizations_table.php
)

IF EXIST database\migrations\2024_05_22_000002_create_services_table.php (
    del database\migrations\2024_05_22_000002_create_services_table.php
    echo   - Eliminado: 2024_05_22_000002_create_services_table.php
)

IF EXIST database\migrations\2024_05_22_000003_create_beneficiaries_table.php (
    del database\migrations\2024_05_22_000003_create_beneficiaries_table.php
    echo   - Eliminado: 2024_05_22_000003_create_beneficiaries_table.php
)

IF EXIST database\migrations\2024_05_22_000004_create_interventions_table.php (
    del database\migrations\2024_05_22_000004_create_interventions_table.php
    echo   - Eliminado: 2024_05_22_000004_create_interventions_table.php
)

IF EXIST database\migrations\2025_05_22_175218_create_personal_access_tokens_table.php (
    del database\migrations\2025_05_22_175218_create_personal_access_tokens_table.php
    echo   - Eliminado: 2025_05_22_175218_create_personal_access_tokens_table.php
)

IF EXIST database\migrations\2025_05_22_000001_test_postgis.php (
    del database\migrations\2025_05_22_000001_test_postgis.php
    echo   - Eliminado: 2025_05_22_000001_test_postgis.php
)

echo.
echo 2. Verificando contenido de archivos de migración...

echo   - Verificando create_services_table.php
for %%I in (database\migrations\2024_05_22_000003_create_services_table.php) do if %%~zI==0 (
    echo     Archivo vacío detectado, recreando...
    copy /y NUL database\migrations\2024_05_22_000003_create_services_table.php >NUL
    echo ^<?php > database\migrations\2024_05_22_000003_create_services_table.php
    echo. >> database\migrations\2024_05_22_000003_create_services_table.php
    echo use Illuminate\Database\Migrations\Migration; >> database\migrations\2024_05_22_000003_create_services_table.php
    echo use Illuminate\Database\Schema\Blueprint; >> database\migrations\2024_05_22_000003_create_services_table.php
    echo use Illuminate\Support\Facades\Schema; >> database\migrations\2024_05_22_000003_create_services_table.php
    echo use Illuminate\Support\Facades\DB; >> database\migrations\2024_05_22_000003_create_services_table.php
    echo. >> database\migrations\2024_05_22_000003_create_services_table.php
    echo return new class extends Migration >> database\migrations\2024_05_22_000003_create_services_table.php
    echo { >> database\migrations\2024_05_22_000003_create_services_table.php
    echo     public function up(): void >> database\migrations\2024_05_22_000003_create_services_table.php
    echo     { >> database\migrations\2024_05_22_000003_create_services_table.php
    echo         Schema::create('services', function ^(Blueprint $table^) { >> database\migrations\2024_05_22_000003_create_services_table.php
    echo             $table-^>id^(^); >> database\migrations\2024_05_22_000003_create_services_table.php
    echo             $table-^>foreignId^('organization_id'^)-^>constrained^(^)-^>onDelete^('cascade'^); >> database\migrations\2024_05_22_000003_create_services_table.php
    echo             $table-^>string^('name'^); >> database\migrations\2024_05_22_000003_create_services_table.php
    echo             $table-^>string^('type'^); >> database\migrations\2024_05_22_000003_create_services_table.php
    echo             $table-^>text^('description'^)-^>nullable^(^); >> database\migrations\2024_05_22_000003_create_services_table.php
    echo             $table-^>string^('address'^); >> database\migrations\2024_05_22_000003_create_services_table.php
    echo             $table-^>decimal^('latitude', 10, 7^); >> database\migrations\2024_05_22_000003_create_services_table.php
    echo             $table-^>decimal^('longitude', 10, 7^); >> database\migrations\2024_05_22_000003_create_services_table.php
    echo             $table-^>integer^('capacity'^)-^>nullable^(^); >> database\migrations\2024_05_22_000003_create_services_table.php
    echo             $table-^>json^('availability'^)-^>nullable^(^); >> database\migrations\2024_05_22_000003_create_services_table.php
    echo             $table-^>json^('requirements'^)-^>nullable^(^); >> database\migrations\2024_05_22_000003_create_services_table.php
    echo             $table-^>boolean^('is_active'^)-^>default^(true^); >> database\migrations\2024_05_22_000003_create_services_table.php
    echo             $table-^>timestamps^(^); >> database\migrations\2024_05_22_000003_create_services_table.php
    echo             $table-^>softDeletes^(^); >> database\migrations\2024_05_22_000003_create_services_table.php
    echo         }^); >> database\migrations\2024_05_22_000003_create_services_table.php
    echo. >> database\migrations\2024_05_22_000003_create_services_table.php
    echo         // Agregar columna de tipo geometry para PostGIS >> database\migrations\2024_05_22_000003_create_services_table.php
    echo         DB::statement^('ALTER TABLE services ADD COLUMN location geometry^(Point, 4326^)'^); >> database\migrations\2024_05_22_000003_create_services_table.php
    echo. >> database\migrations\2024_05_22_000003_create_services_table.php
    echo         // Crear un índice espacial >> database\migrations\2024_05_22_000003_create_services_table.php
    echo         DB::statement^('CREATE INDEX services_location_idx ON services USING GIST ^(location^)'^); >> database\migrations\2024_05_22_000003_create_services_table.php
    echo     } >> database\migrations\2024_05_22_000003_create_services_table.php
    echo. >> database\migrations\2024_05_22_000003_create_services_table.php
    echo     public function down^(^): void >> database\migrations\2024_05_22_000003_create_services_table.php
    echo     { >> database\migrations\2024_05_22_000003_create_services_table.php
    echo         Schema::dropIfExists^('services'^); >> database\migrations\2024_05_22_000003_create_services_table.php
    echo     } >> database\migrations\2024_05_22_000003_create_services_table.php
    echo }; >> database\migrations\2024_05_22_000003_create_services_table.php
)

echo   - Verificando create_beneficiaries_table.php
for %%I in (database\migrations\2024_05_22_000004_create_beneficiaries_table.php) do if %%~zI==0 (
    echo     Archivo vacío detectado, recreando...
    copy /y NUL database\migrations\2024_05_22_000004_create_beneficiaries_table.php >NUL
    echo ^<?php > database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo. >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo use Illuminate\Database\Migrations\Migration; >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo use Illuminate\Database\Schema\Blueprint; >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo use Illuminate\Support\Facades\Schema; >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo. >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo return new class extends Migration >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo { >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo     public function up^(^): void >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo     { >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo         Schema::create^('beneficiaries', function ^(Blueprint $table^) { >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo             $table-^>id^(^); >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo             $table-^>string^('name'^); >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo             $table-^>string^('identification'^)-^>nullable^(^)-^>unique^(^); >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo             $table-^>date^('birthdate'^)-^>nullable^(^); >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo             $table-^>string^('gender'^)-^>nullable^(^); >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo             $table-^>string^('nationality'^)-^>nullable^(^); >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo             $table-^>json^('spoken_languages'^)-^>nullable^(^); >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo             $table-^>text^('health_status'^)-^>nullable^(^); >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo             $table-^>json^('needs'^)-^>nullable^(^); >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo             $table-^>string^('phone'^)-^>nullable^(^); >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo             $table-^>string^('email'^)-^>nullable^(^); >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo             $table-^>string^('contact_preference'^)-^>nullable^(^); >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo             $table-^>string^('vulnerability_status'^)-^>nullable^(^); >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo             $table-^>text^('notes'^)-^>nullable^(^); >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo             $table-^>boolean^('is_active'^)-^>default^(true^); >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo             $table-^>timestamps^(^); >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo             $table-^>softDeletes^(^); >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo         }^); >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo     } >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo. >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo     public function down^(^): void >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo     { >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo         Schema::dropIfExists^('beneficiaries'^); >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo     } >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
    echo }; >> database\migrations\2024_05_22_000004_create_beneficiaries_table.php
)

echo   - Verificando create_interventions_table.php
for %%I in (database\migrations\2024_05_22_000005_create_interventions_table.php) do if %%~zI==0 (
    echo     Archivo vacío detectado, recreando...
    copy /y NUL database\migrations\2024_05_22_000005_create_interventions_table.php >NUL
    echo ^<?php > database\migrations\2024_05_22_000005_create_interventions_table.php
    echo. >> database\migrations\2024_05_22_000005_create_interventions_table.php
    echo use Illuminate\Database\Migrations\Migration; >> database\migrations\2024_05_22_000005_create_interventions_table.php
    echo use Illuminate\Database\Schema\Blueprint; >> database\migrations\2024_05_22_000005_create_interventions_table.php
    echo use Illuminate\Support\Facades\Schema; >> database\migrations\2024_05_22_000005_create_interventions_table.php
    echo. >> database\migrations\2024_05_22_000005_create_interventions_table.php
    echo return new class extends Migration >> database\migrations\2024_05_22_000005_create_interventions_table.php
    echo { >> database\migrations\2024_05_22_000005_create_interventions_table.php
    echo     public function up^(^): void >> database\migrations\2024_05_22_000005_create_interventions_table.php
    echo     { >> database\migrations\2024_05_22_000005_create_interventions_table.php
    echo         Schema::create^('interventions', function ^(Blueprint $table^) { >> database\migrations\2024_05_22_000005_create_interventions_table.php
    echo             $table-^>id^(^); >> database\migrations\2024_05_22_000005_create_interventions_table.php
    echo             $table-^>foreignId^('beneficiary_id'^)-^>constrained^(^)-^>onDelete^('cascade'^); >> database\migrations\2024_05_22_000005_create_interventions_table.php
    echo             $table-^>foreignId^('service_id'^)-^>constrained^(^)-^>onDelete^('cascade'^); >> database\migrations\2024_05_22_000005_create_interventions_table.php
    echo             $table-^>foreignId^('user_id'^)-^>constrained^(^)-^>onDelete^('cascade'^); >> database\migrations\2024_05_22_000005_create_interventions_table.php
    echo             $table-^>enum^('status', ['scheduled', 'in_progress', 'completed', 'cancelled']^)-^>default^('scheduled'^); >> database\migrations\2024_05_22_000005_create_interventions_table.php
    echo             $table-^>dateTime^('scheduled_date'^)-^>nullable^(^); >> database\migrations\2024_05_22_000005_create_interventions_table.php
    echo             $table-^>dateTime^('started_date'^)-^>nullable^(^); >> database\migrations\2024_05_22_000005_create_interventions_table.php
    echo             $table-^>dateTime^('completed_date'^)-^>nullable^(^); >> database\migrations\2024_05_22_000005_create_interventions_table.php
    echo             $table-^>text^('notes'^)-^>nullable^(^); >> database\migrations\2024_05_22_000005_create_interventions_table.php
    echo             $table-^>text^('outcome'^)-^>nullable^(^); >> database\migrations\2024_05_22_000005_create_interventions_table.php
    echo             $table-^>text^('follow_up'^)-^>nullable^(^); >> database\migrations\2024_05_22_000005_create_interventions_table.php
    echo             $table-^>dateTime^('follow_up_date'^)-^>nullable^(^); >> database\migrations\2024_05_22_000005_create_interventions_table.php
    echo             $table-^>timestamps^(^); >> database\migrations\2024_05_22_000005_create_interventions_table.php
    echo             $table-^>softDeletes^(^); >> database\migrations\2024_05_22_000005_create_interventions_table.php
    echo         }^); >> database\migrations\2024_05_22_000005_create_interventions_table.php
    echo     } >> database\migrations\2024_05_22_000005_create_interventions_table.php
    echo. >> database\migrations\2024_05_22_000005_create_interventions_table.php
    echo     public function down^(^): void >> database\migrations\2024_05_22_000005_create_interventions_table.php
    echo     { >> database\migrations\2024_05_22_000005_create_interventions_table.php
    echo         Schema::dropIfExists^('interventions'^); >> database\migrations\2024_05_22_000005_create_interventions_table.php
    echo     } >> database\migrations\2024_05_22_000005_create_interventions_table.php
    echo }; >> database\migrations\2024_05_22_000005_create_interventions_table.php
)

echo.
echo 3. Limpiando cache de migración...
php artisan cache:clear
php artisan config:clear

echo.
echo 4. Ejecutando migraciones...
php artisan migrate:fresh --force
IF %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Las migraciones fallaron
    pause
    exit /b %ERRORLEVEL%
)

echo.
echo 5. Cargando datos de prueba...
php artisan db:seed --force
IF %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Los seeders fallaron
    pause
    exit /b %ERRORLEVEL%
)

echo.
echo === Proceso completado ===
echo Base de datos reinstalada correctamente
echo.
pause
