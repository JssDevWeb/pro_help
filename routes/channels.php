<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Canal privado para usuarios individuales
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal para organizaciones
Broadcast::channel('organization.{organizationId}', function ($user, $organizationId) {
    return $user->organization_id === (int) $organizationId;
});

// Canal para servicios específicos
Broadcast::channel('service.{serviceId}', function ($user, $serviceId) {
    // Verificar si el usuario pertenece a la organización que ofrece el servicio
    $service = \App\Models\Service::find($serviceId);
    return $service && $user->organization_id === $service->organization_id;
});

// Canal para alertas del sistema (solo administradores)
Broadcast::channel('system-alerts', function ($user) {
    return $user->role === 'admin' || $user->role === 'super_admin';
});

// Canal para estadísticas en tiempo real
Broadcast::channel('stats.{organizationId}', function ($user, $organizationId) {
    return $user->organization_id === (int) $organizationId && 
           in_array($user->role, ['admin', 'super_admin', 'coordinator']);
});

// Canal público para actualizaciones generales del sistema
Broadcast::channel('public-updates', function () {
    return true; // Disponible para todos los usuarios autenticados
});
