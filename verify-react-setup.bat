::
:: verify-react-setup.bat
:: Script para verificar la configuración de React y TypeScript en ShelterConnect
::
@echo off
echo ===================================================================
echo Verificando configuracion de React y TypeScript en ShelterConnect
echo ===================================================================
echo.

echo [1/5] Verificando Node.js y npm...
node --version > nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Node.js no esta instalado o no se encuentra en el PATH
    echo Por favor, instale Node.js desde https://nodejs.org/
    goto :error
) else (
    for /f "tokens=* USEBACKQ" %%a IN (`node --version`) do set NODE_VERSION=%%a
    echo [OK] Node.js %NODE_VERSION% instalado correctamente
)

echo.
echo [2/5] Verificando existencia de package.json...
if not exist "package.json" (
    echo [ERROR] No se encuentra package.json en el directorio actual
    goto :error
) else (
    echo [OK] package.json encontrado
)

echo.
echo [3/5] Verificando configuracion de TypeScript...
if not exist "tsconfig.json" (
    echo [ERROR] No se encuentra tsconfig.json en el directorio actual
    goto :error
) else (
    echo [OK] tsconfig.json encontrado
)

echo.
echo [4/5] Verificando script de correccion de tipos React...
if not exist "scripts\fix-react-types.js" (
    echo [ERROR] No se encuentra scripts\fix-react-types.js
    goto :error
) else (
    echo [OK] Script de correccion de tipos React encontrado
)

echo.
echo [5/5] Verificando archivos de tipos personalizados...
if not exist "resources\js\types\react.d.ts" (
    echo [ADVERTENCIA] No se encuentra resources\js\types\react.d.ts
    echo Ejecute: node scripts\fix-react-types.js
    goto :warning
) else (
    echo [OK] Archivos de tipos personalizados encontrados
)

echo.
echo ===================================================================
echo [COMPLETADO] La configuracion de React y TypeScript parece correcta
echo ===================================================================
echo.
echo Recomendacion: Si encuentra errores de tipos de React, ejecute:
echo    node scripts\fix-react-types.js
echo.
goto :end

:warning
echo.
echo ===================================================================
echo [ADVERTENCIA] Se encontraron advertencias en la configuracion
echo ===================================================================
echo.
echo Por favor, revise los mensajes anteriores y tome las acciones necesarias
goto :end

:error
echo.
echo ===================================================================
echo [ERROR] La verificacion ha fallado
echo ===================================================================
echo.
echo Por favor, corrija los errores antes de continuar
echo Consulte docs\react_typescript_solucion.md para mas informacion
goto :end

:end
