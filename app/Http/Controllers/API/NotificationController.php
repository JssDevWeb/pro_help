<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Notifications\ServiceStatusUpdated;
use App\Notifications\OrganizationAlert;
use App\Models\Service;
use App\Models\Organization;
use App\Models\User;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $query = $user->notifications();
        
        // Filtros opcionales
        if ($request->has('unread_only') && $request->boolean('unread_only')) {
            $query->unread();
        }
        
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        // Paginación
        $perPage = min($request->get('per_page', 20), 100);
        $notifications = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => $notifications,
            'unread_count' => $user->unreadNotifications()->count()
        ]);
    }

    /**
     * Get unread notifications count.
     */
    public function unreadCount(): JsonResponse
    {
        $user = Auth::user();
        $count = $user->unreadNotifications()->count();
        
        return response()->json([
            'success' => true,
            'unread_count' => $count
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $user = Auth::user();
        $notification = $user->notifications()->find($id);
        
        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notificación no encontrada'
            ], 404);
        }
        
        if ($notification->read_at === null) {
            $notification->markAsRead();
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Notificación marcada como leída',
            'unread_count' => $user->unreadNotifications()->count()
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();
        
        return response()->json([
            'success' => true,
            'message' => 'Todas las notificaciones marcadas como leídas',
            'unread_count' => 0
        ]);
    }

    /**
     * Delete a notification.
     */
    public function destroy(string $id): JsonResponse
    {
        $user = Auth::user();
        $notification = $user->notifications()->find($id);
        
        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notificación no encontrada'
            ], 404);
        }
        
        $notification->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Notificación eliminada',
            'unread_count' => $user->unreadNotifications()->count()
        ]);
    }

    /**
     * Send a test notification (only for development).
     */
    public function sendTest(Request $request): JsonResponse
    {
        if (!app()->environment('local', 'testing')) {
            return response()->json([
                'success' => false,
                'message' => 'Esta función solo está disponible en desarrollo'
            ], 403);
        }

        $request->validate([
            'type' => 'required|in:service_status,organization_alert',
            'target_user_id' => 'required|exists:users,id'
        ]);

        $user = User::find($request->target_user_id);
        
        if ($request->type === 'service_status') {
            $service = Service::first();
            if ($service) {
                $user->notify(new ServiceStatusUpdated($service, 'updated', 'Esta es una notificación de prueba'));
            }
        } elseif ($request->type === 'organization_alert') {
            $organization = Organization::first();
            if ($organization) {
                $user->notify(new OrganizationAlert(
                    $organization,
                    'info',
                    'Notificación de Prueba',
                    'Esta es una alerta de prueba para verificar el sistema de notificaciones',
                    ['test' => true, 'timestamp' => now()]
                ));
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Notificación de prueba enviada correctamente'
        ]);
    }

    /**
     * Send service status notification.
     */
    public function sendServiceNotification(Request $request): JsonResponse
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'status_type' => 'required|string',
            'message' => 'nullable|string',
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $service = Service::with('organization')->find($request->service_id);
        
        // Si no se especifican usuarios, notificar a usuarios de la organización
        if (empty($request->user_ids)) {
            $users = User::where('organization_id', $service->organization_id)->get();
        } else {
            $users = User::whereIn('id', $request->user_ids)->get();
        }

        foreach ($users as $user) {
            $user->notify(new ServiceStatusUpdated(
                $service,
                $request->status_type,
                $request->message
            ));
        }

        return response()->json([
            'success' => true,
            'message' => "Notificación enviada a {$users->count()} usuarios",
            'users_notified' => $users->count()
        ]);
    }

    /**
     * Send organization alert.
     */
    public function sendOrganizationAlert(Request $request): JsonResponse
    {
        $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'alert_type' => 'required|string',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'data' => 'nullable|array',
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $organization = Organization::find($request->organization_id);
        
        // Si no se especifican usuarios, notificar a usuarios de la organización
        if (empty($request->user_ids)) {
            $users = User::where('organization_id', $organization->id)->get();
        } else {
            $users = User::whereIn('id', $request->user_ids)->get();
        }

        foreach ($users as $user) {
            $user->notify(new OrganizationAlert(
                $organization,
                $request->alert_type,
                $request->title,
                $request->message,
                $request->data ?? []
            ));
        }

        return response()->json([
            'success' => true,
            'message' => "Alerta enviada a {$users->count()} usuarios",
            'users_notified' => $users->count()
        ]);
    }

    /**
     * Get notification statistics.
     */
    public function stats(): JsonResponse
    {
        $user = Auth::user();
        
        $stats = [
            'total_notifications' => $user->notifications()->count(),
            'unread_notifications' => $user->unreadNotifications()->count(),
            'read_notifications' => $user->readNotifications()->count(),
            'notifications_by_type' => $user->notifications()
                ->select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->get()
                ->pluck('count', 'type'),
            'recent_notifications' => $user->notifications()
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'type' => $notification->type,
                        'created_at' => $notification->created_at,
                        'read_at' => $notification->read_at,
                        'data' => $notification->data
                    ];
                })
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
