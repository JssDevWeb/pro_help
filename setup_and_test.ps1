# Script completo para configurar y probar ShelterConnect API
# Ejecutar con: powershell -ExecutionPolicy Bypass -File setup_and_test.ps1

Write-Host "=== Configurando y probando ShelterConnect ===" -ForegroundColor Green

# Paso 1: Verificar que Laravel esté configurado
Write-Host "`n1. Verificando configuración de Laravel..." -ForegroundColor Yellow
try {
    $envCheck = Get-Content ".env" -ErrorAction Stop
    Write-Host "✓ Archivo .env encontrado" -ForegroundColor Green
} catch {
    Write-Host "✗ Error: No se encontró el archivo .env" -ForegroundColor Red
    Write-Host "Ejecute: cp .env.example .env" -ForegroundColor Yellow
    exit 1
}

# Paso 2: Ejecutar migraciones y seeders
Write-Host "`n2. Ejecutando migraciones y seeders..." -ForegroundColor Yellow
try {
    Start-Process -FilePath "php" -ArgumentList "artisan", "migrate:fresh", "--seed" -Wait -NoNewWindow
    Write-Host "✓ Base de datos configurada con datos de prueba" -ForegroundColor Green
} catch {
    Write-Host "✗ Error ejecutando migraciones: $($_.Exception.Message)" -ForegroundColor Red
}

# Paso 3: Iniciar servidor en segundo plano
Write-Host "`n3. Iniciando servidor de desarrollo..." -ForegroundColor Yellow
$serverJob = Start-Job -ScriptBlock {
    Set-Location "c:\wamp64\www\laravel\ShelterConnect"
    php artisan serve --port=8000
}

# Esperar a que el servidor se inicie
Start-Sleep -Seconds 3
Write-Host "✓ Servidor iniciado en http://127.0.0.1:8000" -ForegroundColor Green

# Paso 4: Ejecutar pruebas de API
Write-Host "`n4. Ejecutando pruebas de API..." -ForegroundColor Yellow

$baseUrl = "http://127.0.0.1:8000/api"

# Test 1: Login
Write-Host "`n  1.1. Probando autenticacion..." -ForegroundColor Cyan
try {
    $loginBody = @{
        email = "admin@shelterconnect.org"
        password = "password"
    } | ConvertTo-Json

    $loginResponse = Invoke-RestMethod -Uri "$baseUrl/login" -Method Post -ContentType "application/json" -Body $loginBody
    
    if ($loginResponse.token) {
        Write-Host "    ✓ Login exitoso! Token obtenido." -ForegroundColor Green
        $token = $loginResponse.token
        $headers = @{
            "Authorization" = "Bearer $token"
            "Content-Type" = "application/json"
        }
        Write-Host "    Usuario: $($loginResponse.user.name) ($($loginResponse.user.role))" -ForegroundColor White
    } else {
        Write-Host "    ✗ Error en login" -ForegroundColor Red
        Stop-Job $serverJob
        exit 1
    }
} catch {
    Write-Host "    ✗ Error en login: $($_.Exception.Message)" -ForegroundColor Red
    Stop-Job $serverJob
    exit 1
}

# Test 2: Obtener información del usuario
Write-Host "`n  1.2. Probando información del usuario..." -ForegroundColor Cyan
try {
    $userResponse = Invoke-RestMethod -Uri "$baseUrl/user" -Method Get -Headers $headers
    Write-Host "    ✓ Información del usuario obtenida:" -ForegroundColor Green
    Write-Host "      - ID: $($userResponse.id)" -ForegroundColor White
    Write-Host "      - Nombre: $($userResponse.name)" -ForegroundColor White
    Write-Host "      - Email: $($userResponse.email)" -ForegroundColor White
    Write-Host "      - Rol: $($userResponse.role)" -ForegroundColor White
} catch {
    Write-Host "    ✗ Error obteniendo información del usuario: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 3: Listar organizaciones
Write-Host "`n  1.3. Probando listado de organizaciones..." -ForegroundColor Cyan
try {
    $orgsResponse = Invoke-RestMethod -Uri "$baseUrl/organizations" -Method Get -Headers $headers
    Write-Host "    ✓ Organizaciones obtenidas: $($orgsResponse.meta.total) total" -ForegroundColor Green
    foreach ($org in $orgsResponse.data) {
        Write-Host "      - $($org.name) ($($org.email))" -ForegroundColor White
    }
} catch {
    Write-Host "    ✗ Error obteniendo organizaciones: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 4: Listar servicios
Write-Host "`n  1.4. Probando listado de servicios..." -ForegroundColor Cyan
try {
    $servicesResponse = Invoke-RestMethod -Uri "$baseUrl/services" -Method Get -Headers $headers
    Write-Host "    ✓ Servicios obtenidos: $($servicesResponse.meta.total) total" -ForegroundColor Green
    foreach ($service in $servicesResponse.data) {
        Write-Host "      - $($service.name) ($($service.type))" -ForegroundColor White
    }
} catch {
    Write-Host "    ✗ Error obteniendo servicios: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 5: Búsqueda geoespacial de servicios
Write-Host "`n  1.5. Probando búsqueda geoespacial..." -ForegroundColor Cyan
try {
    $nearbyUrl = "$baseUrl/services-nearby?lat=40.4168&lng=-3.7038&radius=10000"
    $nearbyResponse = Invoke-RestMethod -Uri $nearbyUrl -Method Get -Headers $headers
    Write-Host "    ✓ Servicios cercanos encontrados: $($nearbyResponse.data.Count)" -ForegroundColor Green
    foreach ($service in $nearbyResponse.data) {
        $distance = [math]::Round($service.distance)
        Write-Host "      - $($service.name) - ${distance}m" -ForegroundColor White
    }
} catch {
    Write-Host "    ✗ Error en búsqueda geoespacial: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 6: Listar beneficiarios
Write-Host "`n  1.6. Probando listado de beneficiarios..." -ForegroundColor Cyan
try {
    $beneficiariesResponse = Invoke-RestMethod -Uri "$baseUrl/beneficiaries" -Method Get -Headers $headers
    Write-Host "    ✓ Beneficiarios obtenidos: $($beneficiariesResponse.meta.total) total" -ForegroundColor Green
    foreach ($beneficiary in $beneficiariesResponse.data) {
        Write-Host "      - $($beneficiary.name) ($($beneficiary.identification))" -ForegroundColor White
    }
} catch {
    Write-Host "    ✗ Error obteniendo beneficiarios: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 7: Listar intervenciones
Write-Host "`n  1.7. Probando listado de intervenciones..." -ForegroundColor Cyan
try {
    $interventionsResponse = Invoke-RestMethod -Uri "$baseUrl/interventions" -Method Get -Headers $headers
    Write-Host "    ✓ Intervenciones obtenidas: $($interventionsResponse.meta.total) total" -ForegroundColor Green
    foreach ($intervention in $interventionsResponse.data) {
        Write-Host "      - $($intervention.beneficiary.name) -> $($intervention.service.name) ($($intervention.status))" -ForegroundColor White
    }
} catch {
    Write-Host "    ✗ Error obteniendo intervenciones: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 8: Logout
Write-Host "`n  1.8. Probando logout..." -ForegroundColor Cyan
try {
    $logoutResponse = Invoke-RestMethod -Uri "$baseUrl/logout" -Method Post -Headers $headers
    Write-Host "    ✓ Logout exitoso: $($logoutResponse.message)" -ForegroundColor Green
} catch {
    Write-Host "    ✗ Error en logout: $($_.Exception.Message)" -ForegroundColor Red
}

# Cleanup: Detener servidor
Write-Host "`n5. Limpieza..." -ForegroundColor Yellow
Stop-Job $serverJob
Remove-Job $serverJob
Write-Host "✓ Servidor detenido" -ForegroundColor Green

Write-Host "`n=== Pruebas completadas ===" -ForegroundColor Green
Write-Host "Para ejecutar el servidor manualmente: php artisan serve --port=8000" -ForegroundColor Yellow
