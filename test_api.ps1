# Script de prueba para ShelterConnect API
# Ejecutar con: powershell -ExecutionPolicy Bypass -File test_api.ps1

$baseUrl = "http://127.0.0.1:8000/api"

Write-Host "=== Probando API de ShelterConnect ===" -ForegroundColor Green

# Test 1: Login
Write-Host "`n1. Probando autenticacion..." -ForegroundColor Yellow
try {
    $loginBody = @{
        email = "admin@shelterconnect.org"
        password = "password"
    } | ConvertTo-Json

    $loginResponse = Invoke-RestMethod -Uri "$baseUrl/login" -Method Post -ContentType "application/json" -Body $loginBody
    
    if ($loginResponse.token) {
        Write-Host "Login exitoso! Token obtenido." -ForegroundColor Green
        $token = $loginResponse.token
        $headers = @{
            "Authorization" = "Bearer $token"
            "Content-Type" = "application/json"
        }
        Write-Host "Usuario: $($loginResponse.user.name) ($($loginResponse.user.role))" -ForegroundColor Cyan
    } else {
        Write-Host "Error en login" -ForegroundColor Red
        exit 1
    }
} catch {
    Write-Host "Error en login: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# Test 2: Obtener información del usuario
Write-Host "`n2. Probando información del usuario..." -ForegroundColor Yellow
try {
    $userResponse = Invoke-RestMethod -Uri "$baseUrl/user" -Method Get -Headers $headers
    Write-Host "✓ Información del usuario obtenida:" -ForegroundColor Green
    Write-Host "  - ID: $($userResponse.id)" -ForegroundColor Cyan
    Write-Host "  - Nombre: $($userResponse.name)" -ForegroundColor Cyan
    Write-Host "  - Email: $($userResponse.email)" -ForegroundColor Cyan
    Write-Host "  - Rol: $($userResponse.role)" -ForegroundColor Cyan
} catch {
    Write-Host "✗ Error obteniendo información del usuario: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 3: Listar organizaciones
Write-Host "`n3. Probando listado de organizaciones..." -ForegroundColor Yellow
try {
    $orgsResponse = Invoke-RestMethod -Uri "$baseUrl/organizations" -Method Get -Headers $headers
    Write-Host "✓ Organizaciones obtenidas: $($orgsResponse.meta.total) total" -ForegroundColor Green
    foreach ($org in $orgsResponse.data) {
        Write-Host "  - $($org.name) ($($org.email))" -ForegroundColor Cyan
    }
} catch {
    Write-Host "✗ Error obteniendo organizaciones: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 4: Listar servicios
Write-Host "`n4. Probando listado de servicios..." -ForegroundColor Yellow
try {
    $servicesResponse = Invoke-RestMethod -Uri "$baseUrl/services" -Method Get -Headers $headers
    Write-Host "✓ Servicios obtenidos: $($servicesResponse.meta.total) total" -ForegroundColor Green
    foreach ($service in $servicesResponse.data) {
        Write-Host "  - $($service.name) ($($service.type))" -ForegroundColor Cyan
    }
} catch {
    Write-Host "✗ Error obteniendo servicios: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 5: Búsqueda geoespacial de servicios
Write-Host "`n5. Probando búsqueda geoespacial..." -ForegroundColor Yellow
try {
    $nearbyUrl = "$baseUrl/services-nearby?lat=40.4168&lng=-3.7038&radius=10000"
    $nearbyResponse = Invoke-RestMethod -Uri $nearbyUrl -Method Get -Headers $headers
    Write-Host "✓ Servicios cercanos encontrados: $($nearbyResponse.data.Count)" -ForegroundColor Green
    foreach ($service in $nearbyResponse.data) {
        $distance = [math]::Round($service.distance)
        Write-Host "  - $($service.name) - ${distance}m" -ForegroundColor Cyan
    }
} catch {
    Write-Host "✗ Error en búsqueda geoespacial: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 6: Listar beneficiarios
Write-Host "`n6. Probando listado de beneficiarios..." -ForegroundColor Yellow
try {
    $beneficiariesResponse = Invoke-RestMethod -Uri "$baseUrl/beneficiaries" -Method Get -Headers $headers
    Write-Host "✓ Beneficiarios obtenidos: $($beneficiariesResponse.meta.total) total" -ForegroundColor Green
    foreach ($beneficiary in $beneficiariesResponse.data) {
        Write-Host "  - $($beneficiary.name) ($($beneficiary.identification))" -ForegroundColor Cyan
    }
} catch {
    Write-Host "✗ Error obteniendo beneficiarios: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 7: Listar intervenciones
Write-Host "`n7. Probando listado de intervenciones..." -ForegroundColor Yellow
try {
    $interventionsResponse = Invoke-RestMethod -Uri "$baseUrl/interventions" -Method Get -Headers $headers
    Write-Host "✓ Intervenciones obtenidas: $($interventionsResponse.meta.total) total" -ForegroundColor Green
    foreach ($intervention in $interventionsResponse.data) {
        Write-Host "  - $($intervention.beneficiary.name) -> $($intervention.service.name) ($($intervention.status))" -ForegroundColor Cyan
    }
} catch {
    Write-Host "✗ Error obteniendo intervenciones: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 8: Logout
Write-Host "`n8. Probando logout..." -ForegroundColor Yellow
try {
    $logoutResponse = Invoke-RestMethod -Uri "$baseUrl/logout" -Method Post -Headers $headers
    Write-Host "✓ Logout exitoso: $($logoutResponse.message)" -ForegroundColor Green
} catch {
    Write-Host "✗ Error en logout: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "`n=== Pruebas completadas ===" -ForegroundColor Green
