<?php

require __DIR__ . '/vendor/autoload.php';

// Cargar configuración de Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST NOTIFICACIONES ===\n\n";

echo "Creando usuario de prueba...\n";
$user = App\Models\User::firstOrCreate([
    'email' => 'test@shelterconnect.com'
], [
    'name' => 'Usuario Prueba',
    'password' => bcrypt('password123'),
    'email_verified_at' => now()
]);
echo "Usuario ID: {$user->id}\n";
echo "Usuario: {$user->name}\n";
echo "Email: {$user->email}\n\n";

// Obtener o crear servicio de prueba
$organization = App\Models\Organization::first();
if (!$organization) {
    echo "Creando organización de prueba...\n";
    $organization = new App\Models\Organization([
        'name' => 'Organización de Prueba',
        'type' => 'NGO',
        'address' => 'Calle de Prueba 123',
        'phone' => '123456789',
        'email' => 'org@test.com',
        'description' => 'Organización de prueba'
    ]);
    $organization->save();
}

// Buscar primero si ya existe el servicio
$service = App\Models\Service::where('name', 'Comedor Comunitario Test')->first();

// Si no existe, crearlo con todos los campos requeridos
if (!$service) {
    echo "Creando servicio de prueba...\n";
    $service = new App\Models\Service([
        'name' => 'Comedor Comunitario Test',
        'organization_id' => $organization->id,
        'type' => 'food',
        'description' => 'Servicio de comedor de prueba',
        'address' => 'Calle Prueba 123',  // Aseguramos que address no sea null
        'latitude' => 40.4165,
        'longitude' => -3.7025,
        'is_active' => true
    ]);
    $service->save();
} else {
    echo "Servicio existente encontrado.\n";
    // Asegurémonos de que el servicio existente tenga dirección
    if (empty($service->address)) {
        $service->address = 'Calle Prueba 123';
        $service->save();
        echo "Actualizada dirección del servicio existente.\n";
    }
}
echo "Servicio ID: {$service->id}\n";
echo "Servicio: {$service->name}\n\n";

// Enviar notificación
echo "Enviando notificación de prueba...\n";
try {
    // Asegurarse de que service es un objeto de tipo Service
    if ($service instanceof App\Models\Service) {
        $user->notify(new App\Notifications\ServiceStatusUpdated(
            $service, 
            'updated', 
            'El servicio está ahora disponible para nuevos beneficiarios'
        ));
        echo "Notificación enviada correctamente\n\n";
    } else {
        echo "ERROR: No se pudo obtener un objeto Service válido\n\n";
    }
} catch (Exception $e) {
    echo "Error al enviar notificación: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
}

// Verificar notificaciones
echo "Verificando notificaciones...\n";
$count = $user->notifications()->count();
echo "Notificaciones del usuario: {$count}\n\n";

if ($count > 0) {
    $notification = $user->notifications()->latest()->first();
    echo "Última notificación:\n";
    echo "- ID: {$notification->id}\n";
    echo "- Tipo: {$notification->type}\n";
    echo "- Datos: " . json_encode($notification->data, JSON_PRETTY_PRINT) . "\n";
    echo "- Creada: {$notification->created_at}\n";
    echo "- Leída: " . ($notification->read_at ? $notification->read_at : 'No leída') . "\n";
}

echo "\n=== TEST COMPLETADO ===\n";
