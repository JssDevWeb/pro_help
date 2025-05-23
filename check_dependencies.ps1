# Script para verificar dependencias de ShelterConnect
# Ejecutar con: powershell -ExecutionPolicy Bypass -File check_dependencies.ps1

Write-Host "=== Verificando dependencias de ShelterConnect ===" -ForegroundColor Green

# Verificar PHP
Write-Host "`n1. Verificando PHP..." -ForegroundColor Yellow
try {
    $phpVersion = php -v
    Write-Host "✓ PHP instalado:" -ForegroundColor Green
    Write-Host ($phpVersion -split "`n")[0] -ForegroundColor White
} catch {
    Write-Host "✗ PHP no encontrado. Instale PHP 8.1 o superior." -ForegroundColor Red
    exit 1
}

# Verificar Composer
Write-Host "`n2. Verificando Composer..." -ForegroundColor Yellow
try {
    $composerVersion = composer --version
    Write-Host "✓ Composer instalado: $composerVersion" -ForegroundColor Green
} catch {
    Write-Host "✗ Composer no encontrado. Instale Composer." -ForegroundColor Red
    exit 1
}

# Verificar Node.js
Write-Host "`n3. Verificando Node.js..." -ForegroundColor Yellow
try {
    $nodeVersion = node --version
    Write-Host "✓ Node.js instalado: $nodeVersion" -ForegroundColor Green
} catch {
    Write-Host "✗ Node.js no encontrado. Instale Node.js." -ForegroundColor Red
}

# Verificar NPM
Write-Host "`n4. Verificando NPM..." -ForegroundColor Yellow
try {
    $npmVersion = npm --version
    Write-Host "✓ NPM instalado: $npmVersion" -ForegroundColor Green
} catch {
    Write-Host "✗ NPM no encontrado." -ForegroundColor Red
}

# Verificar extensiones PHP necesarias
Write-Host "`n5. Verificando extensiones PHP..." -ForegroundColor Yellow
$requiredExtensions = @('pdo', 'pdo_mysql', 'pdo_pgsql', 'mbstring', 'fileinfo', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath')
foreach ($ext in $requiredExtensions) {
    $output = php -m | Select-String $ext
    if ($output) {
        Write-Host "✓ $ext habilitado" -ForegroundColor Green
    } else {
        Write-Host "✗ $ext no encontrado" -ForegroundColor Red
    }
}

# Verificar dependencias de Laravel
Write-Host "`n6. Verificando dependencias de Laravel..." -ForegroundColor Yellow
if (Test-Path "vendor/autoload.php") {
    Write-Host "✓ Dependencias de Composer instaladas" -ForegroundColor Green
} else {
    Write-Host "✗ Dependencias de Composer no instaladas. Ejecute: composer install" -ForegroundColor Red
}

# Verificar configuración
Write-Host "`n7. Verificando configuración..." -ForegroundColor Yellow
if (Test-Path ".env") {
    Write-Host "✓ Archivo .env encontrado" -ForegroundColor Green
} else {
    Write-Host "✗ Archivo .env no encontrado. Ejecute: cp .env.example .env" -ForegroundColor Red
}

# Verificar permisos de storage y cache
Write-Host "`n8. Verificando permisos..." -ForegroundColor Yellow
if (Test-Path "storage/logs") {
    Write-Host "✓ Directorio storage accesible" -ForegroundColor Green
} else {
    Write-Host "✗ Problemas con directorio storage" -ForegroundColor Red
}

if (Test-Path "bootstrap/cache") {
    Write-Host "✓ Directorio cache accesible" -ForegroundColor Green
} else {
    Write-Host "✗ Problemas con directorio cache" -ForegroundColor Red
}

Write-Host "`n=== Verificación completada ===" -ForegroundColor Green
Write-Host "Si hay errores, revise la documentación de instalación de Laravel." -ForegroundColor Yellow
