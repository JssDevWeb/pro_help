@echo off
echo === TEST NOTIFICACIONES ===

cd /d "c:\wamp64\www\laravel\pro_help-master"

echo.
echo Ejecutando prueba de notificaciones...
php -f test_notification.php

echo.
echo Presione una tecla para continuar...
pause > nul
