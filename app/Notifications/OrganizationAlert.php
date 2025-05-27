<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;
use App\Models\Organization;

class OrganizationAlert extends Notification implements ShouldQueue
{
    use Queueable;

    protected $organization;
    protected $alertType;
    protected $title;
    protected $message;
    protected $data;

    /**
     * Create a new notification instance.
     */
    public function __construct(Organization $organization, string $alertType, string $title, string $message, array $data = [])
    {
        $this->organization = $organization;
        $this->alertType = $alertType;
        $this->title = $title;
        $this->message = $message;
        $this->data = $data;
    }    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        $channels = [];
        
        // Verificar preferencias para cada canal según el usuario
        if ($notifiable->shouldReceiveNotification(get_class($this), 'database')) {
            $channels[] = 'database';
        }
        
        if ($notifiable->shouldReceiveNotification(get_class($this), 'broadcast')) {
            $channels[] = 'broadcast';
        }
        
        // Determinar si debe enviarse por correo:
        // 1. El usuario permite correos para alertas de organización
        // 2. La alerta es crítica o de emergencia
        if (
            $notifiable->shouldReceiveNotification(get_class($this), 'mail') &&
            (in_array($this->alertType, ['critical', 'emergency', 'system_error']) || 
             $notifiable->getNotificationPreferences()->email_digest)
        ) {
            $channels[] = 'mail';
        }
        
        // Siempre garantizar al menos un canal (database como fallback)
        if (empty($channels)) {
            $channels[] = 'database';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('🚨 Alerta ShelterConnect: ' . $this->title)
                    ->line($this->message)
                    ->line('Organización: ' . $this->organization->name)
                    ->line('Tipo de alerta: ' . ucfirst($this->alertType))
                    ->when(!empty($this->data), function ($mail) {
                        return $mail->line('Detalles adicionales: ' . json_encode($this->data, JSON_PRETTY_PRINT));
                    })
                    ->action('Ver Dashboard', url('/dashboard'))
                    ->line('Por favor, revise su panel de control para más detalles.');
    }    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable)
    {
        return [
            'organization_id' => $this->organization->id,
            'organization_name' => $this->organization->name,
            'alert_type' => $this->alertType,
            'title' => $this->title,
            'message' => $this->message,
            'data' => $this->data,
            'priority' => $this->getPriority(),
            'created_at' => now()->toISOString(),
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     */    /**
     * Get the broadcastable representation of the notification.
     *
     * @param mixed $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        // Solo enviar por broadcasting si está habilitado en configuración
        if (!config('notifications.broadcast.enabled', true)) {
            return new BroadcastMessage([]);
        }
        
        return new BroadcastMessage([
            'notification_id' => $this->id ?? \Illuminate\Support\Str::uuid()->toString(),
            'organization_id' => $this->organization->id,
            'organization_name' => $this->organization->name,
            'alert_type' => $this->alertType,
            'title' => $this->title,
            'message' => $this->message,
            'data' => $this->data,
            'priority' => $this->getPriority(),
            'icon' => $this->getIcon(),
            'color' => $this->getColor(),
            'created_at' => now()->toISOString(),
            'read_at' => null,
            // Incluir datos para facilitar la renderización en el frontend
            'meta' => [
                'should_notify' => in_array($this->alertType, ['emergency', 'critical']),
                'action_url' => $this->getActionUrl(),
                'source' => 'system',
                'expiration' => $this->getExpirationTime(),
            ]
        ]);
    }

    /**
     * Obtener URL de acción según el tipo de alerta
     */
    protected function getActionUrl(): ?string
    {
        $urls = [
            'emergency' => '/dashboard/alerts/emergency',
            'critical' => '/dashboard/alerts',
            'system_error' => '/dashboard/system',
            'capacity_alert' => '/services/' . ($this->data['service_id'] ?? ''),
            'deadline' => '/calendar',
            'reminder' => '/dashboard/tasks',
        ];
        
        return $urls[$this->alertType] ?? '/dashboard';
    }
    
    /**
     * Obtener tiempo de expiración para la notificación
     */
    protected function getExpirationTime(): ?string
    {
        // Las alertas de emergencia expiran después de 24 horas
        if (in_array($this->alertType, ['emergency', 'critical'])) {
            return now()->addDay()->toISOString();
        }
        
        // Alertas informativas expiran después de 7 días
        if (in_array($this->alertType, ['info', 'reminder', 'update'])) {
            return now()->addWeek()->toISOString();
        }
        
        // Por defecto, 3 días
        return now()->addDays(3)->toISOString();
    }/**
     * Get the channels the event should broadcast on.
     */    public function broadcastOn()
    {
        return [
            new \Illuminate\Broadcasting\PrivateChannel('organization.' . $this->organization->id)
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs()
    {
        return 'organization.alert';
    }

    /**
     * Get priority level based on alert type.
     */
    private function getPriority(): string
    {
        return match($this->alertType) {
            'emergency', 'critical', 'system_error' => 'high',
            'warning', 'capacity_alert', 'deadline' => 'medium',
            'info', 'reminder', 'update' => 'low',
            default => 'medium',
        };
    }

    /**
     * Get icon based on alert type.
     */
    private function getIcon(): string
    {
        return match($this->alertType) {
            'emergency' => '🚨',
            'critical' => '❌',
            'warning' => '⚠️',
            'info' => 'ℹ️',
            'success' => '✅',
            'reminder' => '🔔',
            'system_error' => '💻',
            'capacity_alert' => '📊',
            'deadline' => '⏰',
            default => '📢',
        };
    }

    /**
     * Get color scheme based on alert type.
     */
    private function getColor(): string
    {
        return match($this->alertType) {
            'emergency', 'critical', 'system_error' => 'red',
            'warning', 'capacity_alert', 'deadline' => 'yellow',
            'success' => 'green',
            'info', 'reminder', 'update' => 'blue',
            default => 'gray',
        };
    }
}
