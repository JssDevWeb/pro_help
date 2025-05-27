# Prueba rápida de la API ShelterConnect
# Ejecutar con: powershell -ExecutionPolicy Bypass -File quick_api_test.ps1

$baseUrl = "http://127.0.0.1:8000/api"

Write-Host "=== Prueba rápida de API ShelterConnect ===" -ForegroundColor Green
Write-Host "Asegúrese de que el servidor esté ejecutándose: php artisan serve --port=8000" -ForegroundColor Yellow

# Test de conectividad básica
Write-Host "`nProbando conectividad..." -ForegroundColor Cyan
try {
    $healthCheck = Invoke-WebRequest -Uri "http://127.0.0.1:8000" -Method Get -TimeoutSec 5
    if ($healthCheck.StatusCode -eq 200) {
        Write-Host "✓ Servidor Laravel respondiendo en puerto 8000" -ForegroundColor Green
    }
} catch {
    Write-Host "✗ No se puede conectar al servidor Laravel" -ForegroundColor Red
    Write-Host "Ejecute primero: php artisan serve --port=8000" -ForegroundColor Yellow
    exit 1
}

# Test de salud del sistema (endpoint público)
Write-Host "`nProbando salud del sistema..." -ForegroundColor Cyan
try {
    $healthResponse = Invoke-RestMethod -Uri "$baseUrl/health" -Method Get
    Write-Host "✓ Sistema saludable" -ForegroundColor Green
    Write-Host "  Estado: $($healthResponse.data.status)" -ForegroundColor White
    Write-Host "  Base de datos: $($healthResponse.data.database)" -ForegroundColor White
    Write-Host "  Cache: $($healthResponse.data.cache)" -ForegroundColor White
} catch {
    Write-Host "✗ Error en verificación de salud: $($_.Exception.Message)" -ForegroundColor Red
}

# Test de login
Write-Host "`nProbando autenticación..." -ForegroundColor Cyan
try {
    $loginBody = @{
        email = "admin@shelterconnect.org"
        password = "password"
    } | ConvertTo-Json

    $loginResponse = Invoke-RestMethod -Uri "$baseUrl/login" -Method Post -ContentType "application/json" -Body $loginBody
    
    if ($loginResponse.token) {
        Write-Host "✓ Autenticación exitosa" -ForegroundColor Green
        Write-Host "  Usuario: $($loginResponse.user.name)" -ForegroundColor White
        Write-Host "  Rol: $($loginResponse.user.role)" -ForegroundColor White
        
        $headers = @{
            "Authorization" = "Bearer $($loginResponse.token)"
            "Content-Type" = "application/json"
        }
        
        # Test de endpoint protegido
        Write-Host "`nProbando endpoint protegido..." -ForegroundColor Cyan
        try {
            $userResponse = Invoke-RestMethod -Uri "$baseUrl/user" -Method Get -Headers $headers
            Write-Host "✓ Endpoint /api/user funcional" -ForegroundColor Green
        } catch {
            Write-Host "✗ Error en endpoint protegido: $($_.Exception.Message)" -ForegroundColor Red
        }
        
        # Test de geolocalización
        Write-Host "`nProbando geolocalización..." -ForegroundColor Cyan
        try {
            $nearbyUrl = "$baseUrl/services-nearby?lat=40.4168&lng=-3.7038&radius=10000"
            $nearbyResponse = Invoke-RestMethod -Uri $nearbyUrl -Method Get -Headers $headers
            Write-Host "✓ Búsqueda geoespacial funcional" -ForegroundColor Green
            Write-Host "  Servicios encontrados: $($nearbyResponse.data.Count)" -ForegroundColor White
        } catch {
            Write-Host "✗ Error en geolocalización: $($_.Exception.Message)" -ForegroundColor Red
        }
        
        # Test de estadísticas
        Write-Host "`nProbando estadísticas..." -ForegroundColor Cyan
        try {
            $statsResponse = Invoke-RestMethod -Uri "$baseUrl/stats" -Method Get -Headers $headers
            Write-Host "✓ Estadísticas obtenidas" -ForegroundColor Green
            Write-Host "  Organizaciones: $($statsResponse.data.organizations)" -ForegroundColor White
            Write-Host "  Servicios: $($statsResponse.data.services)" -ForegroundColor White
            Write-Host "  Beneficiarios: $($statsResponse.data.beneficiaries)" -ForegroundColor White
            Write-Host "  Intervenciones: $($statsResponse.data.interventions)" -ForegroundColor White
        } catch {
            Write-Host "✗ Error en estadísticas: $($_.Exception.Message)" -ForegroundColor Red
        }
        
    } else {
        Write-Host "✗ Login falló" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Error en autenticación: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "Verifique que la base de datos esté configurada y tenga datos de prueba" -ForegroundColor Yellow
}

Write-Host "`n=== Prueba rápida completada ===" -ForegroundColor Green
