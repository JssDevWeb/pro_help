<?php

namespace App\Services;

use App\Models\Service;
use App\Models\Organization;
use App\Models\User;
use App\Jobs\SendBulkNotifications;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    /**
     * Templates predefinidos para notificaciones
     */
    const TEMPLATES = [
        'service_created' => [
            'title' => 'Nuevo Servicio Disponible',
            'message' => 'Se ha creado un nuevo servicio: {service_name} en {organization_name}',
            'type' => 'service_status',
            'priority' => 'medium'
        ],
        'service_capacity_full' => [
            'title' => 'Capacidad Completa',
            'message' => 'El servicio {service_name} ha alcanzado su capacidad máxima',
            'type' => 'service_status',
            'priority' => 'high'
        ],
        'service_capacity_available' => [
            'title' => 'Capacidad Disponible',
            'message' => 'El servicio {service_name} tiene capacidad disponible',
            'type' => 'service_status',
            'priority' => 'medium'
        ],
        'emergency_alert' => [
            'title' => '🚨 Alerta de Emergencia',
            'message' => 'Se ha activado una alerta de emergencia. Ubicación: {location}',
            'type' => 'organization_alert',
            'priority' => 'critical'
        ],
        'system_maintenance' => [
            'title' => '🔧 Mantenimiento Programado',
            'message' => 'El sistema estará en mantenimiento desde {start_time} hasta {end_time}',
            'type' => 'organization_alert',
            'priority' => 'medium'
        ],
        'new_beneficiary_registered' => [
            'title' => 'Nuevo Beneficiario',
            'message' => 'Se ha registrado un nuevo beneficiario en {service_name}',
            'type' => 'service_status',
            'priority' => 'low'
        ],
        'daily_report_ready' => [
            'title' => '📊 Reporte Diario Listo',
            'message' => 'El reporte diario de {date} está disponible para descarga',
            'type' => 'organization_alert',
            'priority' => 'low'
        ]
    ];

    /**
     * Enviar notificación usando template
     */    /**
     * Enviar notificación usando template
     * 
     * @param string $templateKey Clave del template
     * @param array $recipients Receptores (IDs o modelos User)
     * @param array $variables Variables para reemplazar en el template
     * @param bool $async Usar envío asíncrono (cola)
     * @param string $queueName Nombre de la cola (default, notifications, high, low)
     * @return bool
     */
    public function sendFromTemplate(
        string $templateKey, 
        array $recipients, 
        array $variables = [],
        bool $async = true,
        string $queueName = 'notifications'
    ): bool {
        if (!isset(self::TEMPLATES[$templateKey])) {
            throw new \InvalidArgumentException("Template '{$templateKey}' no existe");
        }

        $template = self::TEMPLATES[$templateKey];
        
        // Reemplazar variables en el mensaje
        $message = $this->replaceVariables($template['message'], $variables);
        $title = $this->replaceVariables($template['title'], $variables);

        // Preparar datos para la notificación
        $notificationData = [
            'title' => $title,
            'message' => $message,
            'template_key' => $templateKey,
            'priority' => $template['priority'],
            'variables' => $variables,
            'sent_at' => now()->toISOString(),
            'tracking_id' => \Illuminate\Support\Str::uuid()->toString()
        ];

        // Agregar datos específicos según el tipo
        $notificationData = array_merge($notificationData, $this->getSpecificData($templateKey, $variables));

        // Registrar la intención de envío
        \Log::info("Preparando envío de notificación", [
            'template' => $templateKey,
            'recipient_count' => count($recipients),
            'tracking_id' => $notificationData['tracking_id'],
            'async' => $async
        ]);

        if ($async && config('queue.default') !== 'sync') {
            // Envío asíncrono usando jobs
            $job = new SendBulkNotifications(
                $template['type'],
                $this->extractUserIds($recipients),
                $notificationData
            );
            
            // Configurar la cola según la prioridad del mensaje
            if ($template['priority'] === 'critical' || $template['priority'] === 'high') {
                $job->onQueue('high');
            } else {
                $job->onQueue($queueName);
            }
            
            dispatch($job);
        } else {
            // Envío síncrono
            $this->sendSynchronous($template['type'], $recipients, $notificationData);
        }

        return true;
    }

    /**
     * Enviar notificación de emergencia a todos los usuarios activos
     */
    public function sendEmergencyAlert(
        string $message,
        string $location = null,
        array $contactInfo = []
    ): bool {
        $allUsers = User::where('is_active', true)->pluck('id')->toArray();

        return $this->sendFromTemplate('emergency_alert', $allUsers, [
            'message' => $message,
            'location' => $location ?? 'No especificada',
            'contact_info' => $contactInfo
        ]);
    }

    /**
     * Notificar cambio de capacidad de servicio
     */
    public function notifyServiceCapacityChange(Service $service, bool $isFull): bool
    {
        $template = $isFull ? 'service_capacity_full' : 'service_capacity_available';
        
        // Obtener usuarios de la organización y coordinadores del área
        $recipients = $this->getServiceRelatedUsers($service);

        return $this->sendFromTemplate($template, $recipients, [
            'service_name' => $service->name,
            'organization_name' => $service->organization->name,
            'current_capacity' => $service->current_capacity ?? 0,
            'max_capacity' => $service->capacity ?? 'Ilimitada'
        ]);
    }

    /**
     * Notificar nuevo beneficiario registrado
     */
    public function notifyNewBeneficiary(Service $service, array $beneficiaryData = []): bool
    {
        $recipients = $this->getServiceRelatedUsers($service);

        return $this->sendFromTemplate('new_beneficiary_registered', $recipients, [
            'service_name' => $service->name,
            'organization_name' => $service->organization->name,
            'beneficiary_count' => $beneficiaryData['total_count'] ?? null
        ]);
    }

    /**
     * Programar notificación de mantenimiento
     */
    public function scheduleMaintenanceNotification(
        \DateTime $startTime,
        \DateTime $endTime,
        array $affectedServices = []
    ): bool {
        $allUsers = User::where('is_active', true)->pluck('id')->toArray();

        return $this->sendFromTemplate('system_maintenance', $allUsers, [
            'start_time' => $startTime->format('d/m/Y H:i'),
            'end_time' => $endTime->format('d/m/Y H:i'),
            'affected_services' => $affectedServices
        ]);
    }

    /**
     * Reemplazar variables en texto
     */
    private function replaceVariables(string $text, array $variables): string
    {
        foreach ($variables as $key => $value) {
            if (is_string($value) || is_numeric($value)) {
                $text = str_replace("{{$key}}", $value, $text);
            }
        }
        return $text;
    }

    /**
     * Obtener datos específicos según el template
     */
    private function getSpecificData(string $templateKey, array $variables): array
    {
        switch ($templateKey) {
            case 'service_created':
            case 'service_capacity_full':
            case 'service_capacity_available':
            case 'new_beneficiary_registered':
                return [
                    'service_id' => $variables['service_id'] ?? null,
                    'status_type' => $this->getStatusTypeFromTemplate($templateKey)
                ];

            case 'emergency_alert':
            case 'system_maintenance':
            case 'daily_report_ready':
                return [
                    'organization_id' => $variables['organization_id'] ?? Organization::first()->id,
                    'alert_type' => $this->getAlertTypeFromTemplate($templateKey),
                    'extra_data' => $variables
                ];

            default:
                return [];
        }
    }

    /**
     * Obtener tipo de estado según template
     */
    private function getStatusTypeFromTemplate(string $templateKey): string
    {
        $mapping = [
            'service_created' => 'new',
            'service_capacity_full' => 'capacity_full',
            'service_capacity_available' => 'capacity_available',
            'new_beneficiary_registered' => 'new_beneficiary'
        ];

        return $mapping[$templateKey] ?? 'updated';
    }

    /**
     * Obtener tipo de alerta según template
     */
    private function getAlertTypeFromTemplate(string $templateKey): string
    {
        $mapping = [
            'emergency_alert' => 'emergency',
            'system_maintenance' => 'maintenance',
            'daily_report_ready' => 'info'
        ];

        return $mapping[$templateKey] ?? 'info';
    }

    /**
     * Obtener usuarios relacionados con un servicio
     */
    private function getServiceRelatedUsers(Service $service): array
    {
        return User::where(function ($query) use ($service) {
            $query->where('organization_id', $service->organization_id)
                  ->orWhere('role', 'admin')
                  ->orWhere('role', 'super_admin');
        })
        ->where('is_active', true)
        ->pluck('id')
        ->toArray();
    }

    /**
     * Extraer IDs de usuarios de diferentes tipos de recipients
     */
    private function extractUserIds($recipients): array
    {
        if (is_array($recipients)) {
            // Si ya es un array de IDs
            if (is_numeric($recipients[0] ?? null)) {
                return $recipients;
            }
            
            // Si es un array de modelos User
            if ($recipients[0] instanceof User) {
                return collect($recipients)->pluck('id')->toArray();
            }
        }

        return [];
    }

    /**
     * Envío síncrono de notificaciones
     */
    private function sendSynchronous(string $type, array $recipients, array $data): void
    {
        $job = new SendBulkNotifications($type, $this->extractUserIds($recipients), $data);
        $job->handle();
    }

    /**
     * Obtener estadísticas de notificaciones
     */
    public function getNotificationStats(int $organizationId = null): array
    {
        $query = DB::table('notifications');
        
        if ($organizationId) {
            $query->whereIn('notifiable_id', 
                User::where('organization_id', $organizationId)->pluck('id')
            );
        }

        $totalSent = $query->count();
        $totalRead = $query->whereNotNull('read_at')->count();
        $totalUnread = $totalSent - $totalRead;

        $byType = $query->select('type', DB::raw('count(*) as count'))
                       ->groupBy('type')
                       ->get()
                       ->pluck('count', 'type')
                       ->toArray();

        return [
            'total_sent' => $totalSent,
            'total_read' => $totalRead,
            'total_unread' => $totalUnread,
            'read_rate' => $totalSent > 0 ? round(($totalRead / $totalSent) * 100, 2) : 0,
            'by_type' => $byType,
            'last_24h' => $query->where('created_at', '>=', now()->subDay())->count()
        ];
    }
}
