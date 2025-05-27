::
:: generate_docs_report.bat
:: Genera un informe sobre el estado de la documentación del proyecto
::
@echo off
setlocal EnableDelayedExpansion

:: Definir la ubicación de salida del informe
set REPORT_FILE=docs\informe_documentacion.md
set DOCS_DIR=docs

echo # Informe del Estado de la Documentación > %REPORT_FILE%
echo. >> %REPORT_FILE%
echo Generado: %DATE% %TIME% >> %REPORT_FILE%
echo. >> %REPORT_FILE%
echo ## Archivos de Documentación Encontrados >> %REPORT_FILE%
echo. >> %REPORT_FILE%
echo | Archivo | Última Modificación | Tamaño (bytes) | >> %REPORT_FILE%
echo | ------- | ------------------- | -------------- | >> %REPORT_FILE%

:: Contar archivos y establecer variables para estadísticas
set TOTAL_FILES=0
set TOTAL_SIZE=0

:: Iterar a través de todos los archivos md en el directorio de docs
for %%F in (%DOCS_DIR%\*.md) do (
    :: Obtener información del archivo
    set "FILE_NAME=%%~nxF"
    set "FILE_SIZE=%%~zF"
    set "FILE_DATE=%%~tF"
    
    :: Calcular estadísticas
    set /a TOTAL_FILES+=1
    set /a TOTAL_SIZE+="!FILE_SIZE!"
    
    :: Añadir fila a la tabla
    echo | !FILE_NAME! | !FILE_DATE! | !FILE_SIZE! | >> %REPORT_FILE%
)

:: Añadir estadísticas
echo. >> %REPORT_FILE%
echo ## Estadísticas >> %REPORT_FILE%
echo. >> %REPORT_FILE%
echo - **Total de archivos de documentación:** %TOTAL_FILES% >> %REPORT_FILE%
echo - **Tamaño total de documentación:** %TOTAL_SIZE% bytes >> %REPORT_FILE%
echo. >> %REPORT_FILE%

:: Verificar archivos clave
echo ## Estado de Documentos Clave >> %REPORT_FILE%
echo. >> %REPORT_FILE%

:: Lista de archivos importantes
set KEY_DOCS=README.md arquitectura.md estado_actual.md api.md guia_desarrollo.md guia_pruebas.md guia_pruebas_frontend.md
set MISSING_COUNT=0

:: Verificar cada documento clave
for %%D in (%KEY_DOCS%) do (
    if exist "%DOCS_DIR%\%%D" (
        echo - ✅ %%D >> %REPORT_FILE%
    ) else (
        echo - ❌ %%D ^(Falta^) >> %REPORT_FILE%
        set /a MISSING_COUNT+=1
    )
)

echo. >> %REPORT_FILE%

:: Mostrar recomendaciones
echo ## Recomendaciones >> %REPORT_FILE%
echo. >> %REPORT_FILE%

if %MISSING_COUNT% GTR 0 (
    echo - ⚠️ **Hay %MISSING_COUNT% documentos clave faltantes. Por favor, créelos.** >> %REPORT_FILE%
) else (
    echo - ✅ Todos los documentos clave están presentes. >> %REPORT_FILE%
)

:: Verificar si hay un índice
if exist "%DOCS_DIR%\README.md" (
    echo - ✅ El índice de documentación existe (README.md) >> %REPORT_FILE%
) else (
    echo - ⚠️ **Se recomienda crear un índice (README.md) para facilitar la navegación.** >> %REPORT_FILE%
)

echo. >> %REPORT_FILE%
echo --- >> %REPORT_FILE%
echo Informe generado automáticamente por generate_docs_report.bat >> %REPORT_FILE%

echo Informe generado en %REPORT_FILE%
echo.
echo Done!
