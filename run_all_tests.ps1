# Script maestro para ejecutar todas las pruebas de ShelterConnect
# Ejecutar con: powershell -ExecutionPolicy Bypass -File run_all_tests.ps1

param(
    [switch]$SkipSetup,
    [switch]$OnlyQuick,
    [int]$Port = 8000
)

Write-Host "=== ShelterConnect - Ejecutor de Pruebas Maestro ===" -ForegroundColor Green
Write-Host "Puerto del servidor: $Port" -ForegroundColor Yellow

if (-not $SkipSetup) {
    Write-Host "`n1. Verificando dependencias..." -ForegroundColor Cyan
    if (Test-Path "check_dependencies.ps1") {
        & .\check_dependencies.ps1
        if ($LASTEXITCODE -ne 0) {
            Write-Host "✗ Faltan dependencias. Corrija los errores antes de continuar." -ForegroundColor Red
            exit 1
        }
    }

    Write-Host "`n2. Configurando base de datos..." -ForegroundColor Cyan
    try {
        Start-Process -FilePath "php" -ArgumentList "artisan", "migrate:fresh", "--seed", "--force" -Wait -NoNewWindow
        Write-Host "✓ Base de datos configurada" -ForegroundColor Green
    } catch {
        Write-Host "✗ Error configurando base de datos: $($_.Exception.Message)" -ForegroundColor Red
        exit 1
    }
}

Write-Host "`n3. Iniciando servidor de desarrollo..." -ForegroundColor Cyan
$serverJob = Start-Job -ScriptBlock {
    param($port)
    Set-Location $using:PWD
    php artisan serve --port=$port
} -ArgumentList $Port

Start-Sleep -Seconds 3

# Verificar que el servidor esté funcionando
try {
    $healthCheck = Invoke-WebRequest -Uri "http://127.0.0.1:$Port" -Method Get -TimeoutSec 10
    Write-Host "✓ Servidor iniciado correctamente en puerto $Port" -ForegroundColor Green
} catch {
    Write-Host "✗ Error iniciando servidor" -ForegroundColor Red
    Stop-Job $serverJob
    Remove-Job $serverJob
    exit 1
}

if ($OnlyQuick) {
    Write-Host "`n4. Ejecutando pruebas rápidas..." -ForegroundColor Cyan
    if (Test-Path "quick_api_test.ps1") {
        # Modificar el puerto en el script temporalmente
        $originalContent = Get-Content "quick_api_test.ps1" -Raw
        $modifiedContent = $originalContent -replace 'http://127.0.0.1:8000', "http://127.0.0.1:$Port"
        Set-Content "quick_api_test_temp.ps1" -Value $modifiedContent
        
        & .\quick_api_test_temp.ps1
        Remove-Item "quick_api_test_temp.ps1" -ErrorAction SilentlyContinue
    }
} else {
    Write-Host "`n4. Ejecutando suite completa de pruebas..." -ForegroundColor Cyan
    if (Test-Path "test_api_fixed.ps1") {
        # Modificar el puerto en el script temporalmente
        $originalContent = Get-Content "test_api_fixed.ps1" -Raw
        $modifiedContent = $originalContent -replace 'http://127.0.0.1:8000', "http://127.0.0.1:$Port"
        Set-Content "test_api_fixed_temp.ps1" -Value $modifiedContent
        
        & .\test_api_fixed_temp.ps1
        Remove-Item "test_api_fixed_temp.ps1" -ErrorAction SilentlyContinue
    }
}

Write-Host "`n5. Limpieza..." -ForegroundColor Cyan
Stop-Job $serverJob
Remove-Job $serverJob
Write-Host "✓ Servidor detenido" -ForegroundColor Green

Write-Host "`n=== Pruebas completadas ===" -ForegroundColor Green
Write-Host "Para ejecutar solo pruebas rápidas: .\run_all_tests.ps1 -OnlyQuick" -ForegroundColor Yellow
Write-Host "Para omitir setup: .\run_all_tests.ps1 -SkipSetup" -ForegroundColor Yellow
Write-Host "Para cambiar puerto: .\run_all_tests.ps1 -Port 9000" -ForegroundColor Yellow
