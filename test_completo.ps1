# Script de pruebas completas para ShelterConnect API
# Ejecutar con: powershell -ExecutionPolicy Bypass -File test_completo.ps1

$baseUrl = "http://127.0.0.1:8000/api"

Write-Host "=== PRUEBAS COMPLETAS DE API SHELTERCONNECT ===" -ForegroundColor Green
Write-Host "Fecha: $(Get-Date)" -ForegroundColor Gray
Write-Host ""

# Variables globales
$headers = @{}
$testResults = @()

function Test-API {
    param($name, $scriptBlock)
    
    Write-Host "[$name]" -ForegroundColor Cyan -NoNewline
    try {
        $result = & $scriptBlock
        Write-Host " ✓ PASÓ" -ForegroundColor Green
        $script:testResults += @{Name = $name; Status = "PASÓ"; Details = $result}
        return $true
    } catch {
        Write-Host " ✗ FALLÓ" -ForegroundColor Red
        Write-Host "  Error: $($_.Exception.Message)" -ForegroundColor Red
        $script:testResults += @{Name = $name; Status = "FALLÓ"; Error = $_.Exception.Message}
        return $false
    }
}

# PRUEBA 1: AUTENTICACIÓN
Test-API "Autenticación de usuario admin" {
    $loginBody = @{
        email = "admin@shelterconnect.org"
        password = "password"
    } | ConvertTo-Json

    $response = Invoke-RestMethod -Uri "$baseUrl/login" -Method Post -ContentType "application/json" -Body $loginBody
    
    if (-not $response.token) {
        throw "No se recibió token de autenticación"
    }
    
    $script:headers = @{
        "Authorization" = "Bearer $($response.token)"
        "Content-Type" = "application/json"
        "Accept" = "application/json"
    }
    
    return "Token obtenido correctamente"
}

# PRUEBA 2: INFORMACIÓN DEL USUARIO
Test-API "Obtener información del usuario autenticado" {
    $userInfo = Invoke-RestMethod -Uri "$baseUrl/user" -Method Get -Headers $headers
    
    if (-not $userInfo.name) {
        throw "No se obtuvo información del usuario"
    }
    
    return "Usuario: $($userInfo.name) ($($userInfo.email))"
}

# PRUEBA 3: LISTAR ORGANIZACIONES
Test-API "Listar organizaciones" {
    $orgsResponse = Invoke-RestMethod -Uri "$baseUrl/organizations" -Method Get -Headers $headers
    
    if (-not $orgsResponse.data -or $orgsResponse.data.Count -eq 0) {
        throw "No se encontraron organizaciones"
    }
    
    return "$($orgsResponse.data.Count) organizaciones encontradas"
}

# PRUEBA 4: CREAR NUEVA ORGANIZACIÓN
Test-API "Crear nueva organización" {
    $newOrg = @{
        name = "Organización de Prueba"
        email = "test@prueba.org"
        phone = "+34600000000"
        address = "Calle Test 123, Madrid"
        description = "Organización creada para pruebas"
        latitude = 40.4168
        longitude = -3.7038
    } | ConvertTo-Json
    
    $response = Invoke-RestMethod -Uri "$baseUrl/organizations" -Method Post -Headers $headers -Body $newOrg
    
    if (-not $response.id) {
        throw "No se creó la organización correctamente"
    }
    
    $script:testOrgId = $response.id
    return "Organización creada con ID: $($response.id)"
}

# PRUEBA 5: OBTENER ORGANIZACIÓN ESPECÍFICA
Test-API "Obtener organización específica" {
    if (-not $script:testOrgId) {
        throw "No hay ID de organización de prueba"
    }
    
    $org = Invoke-RestMethod -Uri "$baseUrl/organizations/$script:testOrgId" -Method Get -Headers $headers
    
    if (-not $org.id -or $org.id -ne $script:testOrgId) {
        throw "No se obtuvo la organización correcta"
    }
    
    return "Organización obtenida: $($org.name)"
}

# PRUEBA 6: LISTAR SERVICIOS
Test-API "Listar servicios" {
    $servicesResponse = Invoke-RestMethod -Uri "$baseUrl/services" -Method Get -Headers $headers
    
    if (-not $servicesResponse.data) {
        throw "No se pudieron obtener servicios"
    }
    
    return "$($servicesResponse.data.Count) servicios encontrados"
}

# PRUEBA 7: LISTAR BENEFICIARIOS
Test-API "Listar beneficiarios" {
    $beneficiariesResponse = Invoke-RestMethod -Uri "$baseUrl/beneficiaries" -Method Get -Headers $headers
    
    if (-not $beneficiariesResponse.data) {
        throw "No se pudieron obtener beneficiarios"
    }
    
    return "$($beneficiariesResponse.data.Count) beneficiarios encontrados"
}

# PRUEBA 8: LISTAR INTERVENCIONES
Test-API "Listar intervenciones" {
    $interventionsResponse = Invoke-RestMethod -Uri "$baseUrl/interventions" -Method Get -Headers $headers
    
    if (-not $interventionsResponse.data) {
        throw "No se pudieron obtener intervenciones"
    }
    
    return "$($interventionsResponse.data.Count) intervenciones encontradas"
}

# PRUEBA 9: ACTUALIZAR ORGANIZACIÓN
Test-API "Actualizar organización" {
    if (-not $script:testOrgId) {
        throw "No hay ID de organización de prueba"
    }
    
    $updateData = @{
        name = "Organización de Prueba Actualizada"
        description = "Descripción actualizada para pruebas"
    } | ConvertTo-Json
    
    $response = Invoke-RestMethod -Uri "$baseUrl/organizations/$script:testOrgId" -Method Put -Headers $headers -Body $updateData
    
    if ($response.name -ne "Organización de Prueba Actualizada") {
        throw "La organización no se actualizó correctamente"
    }
    
    return "Organización actualizada correctamente"
}

# PRUEBA 10: ELIMINAR ORGANIZACIÓN
Test-API "Eliminar organización de prueba" {
    if (-not $script:testOrgId) {
        throw "No hay ID de organización de prueba"
    }
    
    $response = Invoke-RestMethod -Uri "$baseUrl/organizations/$script:testOrgId" -Method Delete -Headers $headers
    
    return "Organización eliminada correctamente"
}

# RESUMEN DE RESULTADOS
Write-Host "`n=== RESUMEN DE PRUEBAS ===" -ForegroundColor Yellow

$passed = ($testResults | Where-Object { $_.Status -eq "PASÓ" }).Count
$failed = ($testResults | Where-Object { $_.Status -eq "FALLÓ" }).Count
$total = $testResults.Count

Write-Host "Total de pruebas: $total" -ForegroundColor White
Write-Host "Pruebas exitosas: $passed" -ForegroundColor Green
Write-Host "Pruebas fallidas: $failed" -ForegroundColor Red

if ($failed -gt 0) {
    Write-Host "`nPruebas que fallaron:" -ForegroundColor Red
    $testResults | Where-Object { $_.Status -eq "FALLÓ" } | ForEach-Object {
        Write-Host "  - $($_.Name): $($_.Error)" -ForegroundColor Red
    }
}

Write-Host "`n=== PRUEBAS COMPLETADAS ===" -ForegroundColor Green
