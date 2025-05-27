<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckNotificationPermissions
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permission = null)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado'
            ], 401);
        }

        // Verificar permisos específicos para notificaciones
        switch ($permission) {
            case 'send-notifications':
                if (!in_array($user->role, ['admin', 'super_admin', 'coordinator'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No tienes permisos para enviar notificaciones'
                    ], 403);
                }
                break;
                
            case 'manage-all-notifications':
                if (!in_array($user->role, ['admin', 'super_admin'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No tienes permisos para gestionar todas las notificaciones'
                    ], 403);
                }
                break;
        }

        return $next($request);
    }
}
