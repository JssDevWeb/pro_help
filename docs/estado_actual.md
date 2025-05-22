# Estado Actual del Proyecto ShelterConnect

## Resumen

ShelterConnect es una plataforma para conectar servicios sociales con personas en situación de vulnerabilidad utilizando características geoespaciales para mejorar el acceso a recursos. Utiliza Laravel 12 con integración de PostGIS para funcionalidades geoespaciales.

## Componentes Implementados

### Base de Datos
- [x] Configuración de PostgreSQL con extensiones PostGIS
- [x] Migraciones para todas las tablas principales:
  - Organizations (con columna de ubicación geoespacial)
  - Services (con columna de ubicación geoespacial)
  - Beneficiaries (con columna de última ubicación conocida)
  - Interventions (registro de servicios prestados)
  - Users (cuentas de acceso al sistema)

### Modelos
- [x] Implementación de modelos con relaciones:
  - `Organization`: con métodos para manejo de ubicaciones geoespaciales
  - `Service`: con métodos para manejo de ubicaciones y scope `nearby` para búsquedas por proximidad
  - `Beneficiary`: con métodos para manejo de ubicación geoespacial
  - `Intervention`: con relaciones a servicios y beneficiarios
  - `User`: con relaciones y manejo de roles

### Seeders de Datos
- [x] OrganizationSeeder: datos de ejemplo para organizaciones
- [x] ServiceSeeder: servicios con horarios y ubicaciones
- [x] BeneficiarySeeder: beneficiarios con necesidades específicas
- [x] InterventionSeeder: registro de intervenciones
- [x] UserSeeder: usuarios de prueba con diferentes roles

## Próximos Pasos

### 1. Implementación de Autenticación con Sanctum

#### Configuración de Sanctum
```php
// Paso 1: Verificar que la migración esté creada (ya está)

// Paso 2: Configurar el middleware de autenticación en app/Http/Kernel.php
protected $middlewareGroups = [
    'api' => [
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],
];
```

#### Crear AuthController
```php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciales inválidas'
            ], 401);
        }

        // Crear token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
```

#### Configurar Rutas de API
```php
use App\Http\Controllers\API\AuthController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
});
```

### 2. Implementación de Controladores de Recursos

Para cada recurso principal (Organization, Service, Beneficiary, Intervention) debemos:
1. Crear un controlador con métodos CRUD
2. Implementar consultas geoespaciales donde corresponda
3. Configurar rutas API protegidas con autenticación

### 3. Pruebas y Validación

1. Probar autenticación con Postman/Insomnia
2. Validar consultas geoespaciales
3. Verificar relaciones entre modelos en las respuestas API

## Uso de PostGIS en el Proyecto

Para todas las consultas geoespaciales utilizamos funciones nativas de PostGIS:

- `ST_SetSRID(ST_MakePoint(lng, lat), 4326)`: Para crear puntos geográficos
- `ST_DistanceSphere(point1, point2)`: Para calcular distancias
- `ST_DWithin(point1::geography, point2::geography, distance)`: Para filtrar por proximidad

Ejemplo de consulta para encontrar servicios cercanos:
```php
Service::selectRaw("*, ST_DistanceSphere(location, ST_MakePoint(?, ?)::geography) as distance", [$lng, $lat])
    ->whereRaw("ST_DWithin(location::geography, ST_MakePoint(?, ?)::geography, ?)", [$lng, $lat, $radius])
    ->orderBy('distance')
    ->get();
```
