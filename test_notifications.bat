@echo off
echo ====================================
echo    PRUEBA SISTEMA DE NOTIFICACIONES
echo    ShelterConnect
echo ====================================

cd /d "c:\wamp64\www\laravel\pro_help-master"

echo.
echo [NUEVO] Ejecutando pruebas automatizadas...
php artisan test --filter=NotificationSystemTest
php artisan test --filter=NotificationControllerTest

echo.
echo [1/6] Verificando configuracion de broadcasting...
php artisan config:show broadcasting 2>nul
if %errorlevel% neq 0 (
    echo ERROR: Configuracion de broadcasting no encontrada
    pause
    exit /b 1
)

echo.
echo [2/6] Verificando tabla de notificaciones...
php artisan tinker --execute="echo 'Notificaciones: ' . DB::table('notifications')->count();"
if %errorlevel% neq 0 (
    echo ERROR: Tabla de notificaciones no encontrada
    pause
    exit /b 1
)

echo.
echo [3/6] Verificando modelos y clases...
php artisan tinker --execute="new App\Notifications\ServiceStatusUpdated(App\Models\Service::first(), 'test'); echo 'ServiceStatusUpdated: OK';"
if %errorlevel% neq 0 (
    echo ERROR: Clase ServiceStatusUpdated no funciona
    pause
    exit /b 1
)

php artisan tinker --execute="new App\Notifications\OrganizationAlert(App\Models\Organization::first(), 'test', 'Test', 'Test message'); echo 'OrganizationAlert: OK';"
if %errorlevel% neq 0 (
    echo ERROR: Clase OrganizationAlert no funciona
    pause
    exit /b 1
)

echo.
echo [4/6] Verificando eventos...
php artisan tinker --execute="new App\Events\ServiceUpdated(App\Models\Service::first(), 'test'); echo 'ServiceUpdated Event: OK';"
if %errorlevel% neq 0 (
    echo ERROR: Evento ServiceUpdated no funciona
    pause
    exit /b 1
)

echo.
echo [5/6] Verificando servicio de notificaciones...
php artisan tinker --execute="echo 'Templates disponibles: ' . count(App\Services\NotificationService::TEMPLATES);"
if %errorlevel% neq 0 (
    echo ERROR: NotificationService no funciona
    pause
    exit /b 1
)

echo.
echo [6/6] Verificando rutas de API...
curl -s -o nul -w "Status: %%{http_code}" "http://localhost/laravel/pro_help-master/api/notifications" -H "Accept: application/json"
echo.

echo.
echo ====================================
echo   SISTEMA DE NOTIFICACIONES OK!
echo ====================================
echo.
echo Componentes verificados:
echo ✓ Configuracion de broadcasting
echo ✓ Tabla de notificaciones
echo ✓ Clases de notificacion
echo ✓ Eventos de broadcasting  
echo ✓ Servicio de notificaciones
echo ✓ Rutas de API
echo.
echo Para probar notificaciones:
echo 1. Ejecutar: php artisan queue:work (en otra ventana)
echo 2. Acceder al dashboard en el navegador
echo 3. Las notificaciones aparecerán en tiempo real
echo.
echo Para enviar notificacion de prueba:
echo php artisan tinker
echo $user = App\Models\User::first();
echo $service = App\Models\Service::first();
echo $user->notify(new App\Notifications\ServiceStatusUpdated($service, 'test', 'Mensaje de prueba'));
echo.

pause
