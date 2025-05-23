<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\OrganizationController;
use App\Http\Controllers\API\ServiceController;
use App\Http\Controllers\API\BeneficiaryController;
use App\Http\Controllers\API\InterventionController;
use App\Http\Controllers\API\StatsController;
use App\Http\Controllers\API\GeospatialStatsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rutas de autenticación públicas
Route::post('/login', [AuthController::class, 'login']);

// Rutas públicas de verificación
Route::get('/health', [StatsController::class, 'health']);

// Rutas protegidas por autenticación
Route::middleware('auth:sanctum')->group(function () {
    // Autenticación
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Organizaciones
    Route::apiResource('organizations', OrganizationController::class);

    // Servicios
    Route::apiResource('services', ServiceController::class);
    Route::get('/services-nearby', [ServiceController::class, 'nearby']);

    // Beneficiarios
    Route::apiResource('beneficiaries', BeneficiaryController::class);    // Intervenciones
    Route::apiResource('interventions', InterventionController::class);    // Estadísticas y métricas
    Route::get('/stats', [StatsController::class, 'index']);
    Route::get('/stats/services', [StatsController::class, 'serviceStats']);
    
    // Estadísticas geoespaciales
    Route::get('/geospatial-stats', [GeospatialStatsController::class, 'getGeospatialStats']);
    Route::get('/service-types', [GeospatialStatsController::class, 'getServiceTypes']);
    Route::get('/service-density', [GeospatialStatsController::class, 'getServiceDensity']);
    Route::get('/service-type-stats/{serviceType}', [GeospatialStatsController::class, 'getServiceTypeStats']);
});
