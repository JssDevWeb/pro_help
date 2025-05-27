<?php

// Script simple para probar el sistema de notificaciones
require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Cargar configuración de Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== PRUEBA DEL SISTEMA DE NOTIFICACIONES ===\n\n";

try {
    // 1. Verificar conexión a la base de datos
    echo "1. Verificando conexión a la base de datos...\n";
    $connection = DB::connection()->getPdo();
    echo "   ✓ Conexión exitosa\n\n";

    // 2. Verificar tabla de notificaciones
    echo "2. Verificando tabla de notificaciones...\n";
    $exists = DB::getSchemaBuilder()->hasTable('notifications');
    if ($exists) {
        echo "   ✓ Tabla 'notifications' existe\n";
        $count = DB::table('notifications')->count();
        echo "   ✓ Registros en tabla: $count\n\n";
    } else {
        echo "   ✗ Tabla 'notifications' NO existe\n\n";
    }

    // 3. Verificar clases de notificación
    echo "3. Verificando clases de notificación...\n";
    
    $notificationClasses = [
        'App\\Notifications\\ServiceStatusUpdated',
        'App\\Notifications\\OrganizationAlert'
    ];
    
    foreach ($notificationClasses as $class) {
        if (class_exists($class)) {
            echo "   ✓ Clase existe: $class\n";
        } else {
            echo "   ✗ Clase NO existe: $class\n";
        }
    }
    echo "\n";

    // 4. Verificar eventos
    echo "4. Verificando eventos...\n";
    
    $eventClasses = [
        'App\\Events\\ServiceUpdated',
        'App\\Events\\RealTimeStatsUpdated'
    ];
    
    foreach ($eventClasses as $class) {
        if (class_exists($class)) {
            echo "   ✓ Evento existe: $class\n";
        } else {
            echo "   ✗ Evento NO existe: $class\n";
        }
    }
    echo "\n";

    // 5. Verificar controlador
    echo "5. Verificando controlador...\n";
    if (class_exists('App\\Http\\Controllers\\API\\NotificationController')) {
        echo "   ✓ NotificationController existe\n";
        
        $controller = new App\Http\Controllers\API\NotificationController();
        $methods = get_class_methods($controller);
        $expectedMethods = ['index', 'markAsRead', 'destroy', 'sendServiceNotification'];
        
        foreach ($expectedMethods as $method) {
            if (in_array($method, $methods)) {
                echo "   ✓ Método existe: $method\n";
            } else {
                echo "   ✗ Método NO existe: $method\n";
            }
        }
    } else {
        echo "   ✗ NotificationController NO existe\n";
    }
    echo "\n";

    // 6. Verificar servicio
    echo "6. Verificando servicio...\n";
    if (class_exists('App\\Services\\NotificationService')) {
        echo "   ✓ NotificationService existe\n";
    } else {
        echo "   ✗ NotificationService NO existe\n";
    }
    echo "\n";

    echo "=== PRUEBA COMPLETADA ===\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
