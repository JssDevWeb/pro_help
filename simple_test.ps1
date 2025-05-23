# Script simplificado para pruebas de API de ShelterConnect
# Ejecutar con: powershell -ExecutionPolicy Bypass -File simple_test.ps1

$baseUrl = "http://127.0.0.1:8000/api"

Write-Host "=== Prueba Simplificada de API ShelterConnect ===" -ForegroundColor Green
Write-Host "Asegúrate de que el servidor esté ejecutándose en puerto 8000" -ForegroundColor Yellow
Write-Host "Para iniciar el servidor: php artisan serve" -ForegroundColor Yellow
Write-Host ""

# Test 1: Login
Write-Host "1. Probando autenticación..." -ForegroundColor Cyan
$loginBody = @{
    email = "admin@shelterconnect.org"
    password = "password"
} | ConvertTo-Json

try {
    $loginResponse = Invoke-RestMethod -Uri "$baseUrl/login" -Method Post -ContentType "application/json" -Body $loginBody
    
    Write-Host "  [OK] Login exitoso" -ForegroundColor Green
    Write-Host "  Usuario: $($loginResponse.user.name)" -ForegroundColor White
    
    $headers = @{
        "Authorization" = "Bearer $($loginResponse.token)"
        "Content-Type" = "application/json"
    }
} catch {
    Write-Host "  [ERROR] Login falló: $($_.Exception.Message)" -ForegroundColor Red
    exit
}

# Test 2: Obtener info usuario
Write-Host "`n2. Probando info de usuario..." -ForegroundColor Cyan
try {
    $userInfo = Invoke-RestMethod -Uri "$baseUrl/user" -Method Get -Headers $headers
    Write-Host "  [OK] Información obtenida" -ForegroundColor Green
    Write-Host "  Nombre: $($userInfo.name)" -ForegroundColor White
    Write-Host "  Email: $($userInfo.email)" -ForegroundColor White
    Write-Host "  Rol: $($userInfo.role)" -ForegroundColor White
} catch {
    Write-Host "  [ERROR] No se pudo obtener info del usuario: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 3: Listar organizaciones
Write-Host "`n3. Probando listado de organizaciones..." -ForegroundColor Cyan
try {
    $orgsResponse = Invoke-RestMethod -Uri "$baseUrl/organizations" -Method Get -Headers $headers
    Write-Host "  [OK] $($orgsResponse.meta.total) organizaciones obtenidas" -ForegroundColor Green
} catch {
    Write-Host "  [ERROR] Error obteniendo organizaciones: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 4: Geolocalización
Write-Host "`n4. Probando funcionalidad geoespacial..." -ForegroundColor Cyan
try {
    $nearbyUrl = "$baseUrl/services-nearby?lat=40.4168&lng=-3.7038&radius=10000"
    $nearbyResponse = Invoke-RestMethod -Uri $nearbyUrl -Method Get -Headers $headers
    Write-Host "  [OK] Búsqueda geoespacial completada" -ForegroundColor Green
    Write-Host "  Servicios cercanos encontrados: $($nearbyResponse.data.Count)" -ForegroundColor White
} catch {
    Write-Host "  [ERROR] Error en búsqueda geoespacial: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 5: Health check
Write-Host "`n5. Probando health check..." -ForegroundColor Cyan
try {
    $healthResponse = Invoke-RestMethod -Uri "$baseUrl/health" -Method Get
    Write-Host "  [OK] Sistema saludable: $($healthResponse.data.status)" -ForegroundColor Green
} catch {
    Write-Host "  [ERROR] Error en health check: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 6: Logout
Write-Host "`n6. Probando logout..." -ForegroundColor Cyan
try {
    $logoutResponse = Invoke-RestMethod -Uri "$baseUrl/logout" -Method Post -Headers $headers
    Write-Host "  [OK] Logout exitoso: $($logoutResponse.message)" -ForegroundColor Green
} catch {
    Write-Host "  [ERROR] Error en logout: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "`n=== Pruebas Completadas ===" -ForegroundColor Green
