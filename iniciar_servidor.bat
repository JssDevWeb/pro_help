@echo off
REM Inicia el servidor Laravel en modo de desarrollo
REM Ejecutar con: iniciar_servidor.bat [puerto]

IF "%1"=="" (
    SET PUERTO=8000
) ELSE (
    SET PUERTO=%1
)

echo === Iniciando servidor Laravel en puerto %PUERTO% ===
echo Presiona Ctrl+C para detener el servidor
echo.

php artisan serve --port=%PUERTO%
