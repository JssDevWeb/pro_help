<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;
use App\Models\Service;

class ServiceStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $service;
    protected $statusType;
    protected $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(Service $service, string $statusType, string $message = null)
    {
        $this->service = $service;
        $this->statusType = $statusType;
        $this->message = $message ?? $this->getDefaultMessage();
    }    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        $channels = [];
        
        // Verificar preferencias para cada canal
        if ($notifiable->shouldReceiveNotification(get_class($this), 'database')) {
            $channels[] = 'database';
        }
        
        if ($notifiable->shouldReceiveNotification(get_class($this), 'broadcast')) {
            $channels[] = 'broadcast';
        }
        
        if ($notifiable->shouldReceiveNotification(get_class($this), 'mail')) {
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
                    ->subject('Estado del Servicio Actualizado - ShelterConnect')
                    ->line($this->message)
                    ->line('Servicio: ' . $this->service->name)
                    ->line('Estado: ' . $this->statusType)
                    ->action('Ver Servicio', url('/services/' . $this->service->id))
                    ->line('Gracias por usar ShelterConnect!');
    }    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable)
    {
        return [
            'service_id' => $this->service->id,
            'service_name' => $this->service->name,
            'status_type' => $this->statusType,
            'message' => $this->message,
            'organization_id' => $this->service->organization_id,
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
            'service_id' => $this->service->id,
            'service_name' => $this->service->name,
            'status_type' => $this->statusType,
            'message' => $this->message,
            'organization_id' => $this->service->organization_id,
            'created_at' => now()->toISOString(),
            'read_at' => null,
            'priority' => $this->getPriorityFromStatusType(),
            'icon' => $this->getIconFromStatusType(),
        ]);
    }
    
    /**
     * Determinar prioridad basada en el tipo de estado
     *
     * @return string
     */
    private function getPriorityFromStatusType(): string
    {
        $priorities = [
            'emergency' => 'high',
            'capacity_full' => 'medium',
            'updated' => 'low',
            'new' => 'medium',
            'capacity_available' => 'low',
            'new_beneficiary' => 'low',
            'suspended' => 'medium',
            'inactive' => 'medium',
        ];
        
        return $priorities[$this->statusType] ?? 'medium';
    }
    
    /**
     * Obtener icono correspondiente al tipo de estado
     *
     * @return string
     */
    private function getIconFromStatusType(): string
    {
        $icons = [
            'active' => '✅',
            'inactive' => '❌',
            'suspended' => '⚠️',
            'updated' => '📝',
            'new' => '🆕',
            'capacity_full' => '⛔',
            'capacity_available' => '✳️',
            'new_beneficiary' => '👥',
            'emergency' => '🚨',
            'deadline' => '⏰',
            'reminder' => '🔔',
            'info' => 'ℹ️',
        ];
        
        return $icons[$this->statusType] ?? '📢';
    }/**
     * Get the channels the event should broadcast on.
     */    public function broadcastOn()
    {
        return [
            new \Illuminate\Broadcasting\PrivateChannel('organization.' . $this->service->organization_id),
            new \Illuminate\Broadcasting\PrivateChannel('service.' . $this->service->id)
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs()
    {
        return 'service.status.updated';
    }

    /**
     * Get default message based on status type.
     */
    private function getDefaultMessage(): string
    {
        return match($this->statusType) {
            'active' => 'El servicio está ahora activo y disponible.',
            'inactive' => 'El servicio ha sido desactivado temporalmente.',
            'suspended' => 'El servicio ha sido suspendido.',
            'updated' => 'La información del servicio ha sido actualizada.',
            'new_beneficiary' => 'Un nuevo beneficiario ha sido registrado en el servicio.',
            'capacity_full' => 'El servicio ha alcanzado su capacidad máxima.',
            'capacity_available' => 'El servicio tiene capacidad disponible.',
            default => 'El estado del servicio ha cambiado.',
        };
    }
}
