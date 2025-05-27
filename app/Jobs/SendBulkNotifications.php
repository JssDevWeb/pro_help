<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Notifications\OrganizationAlert;
use App\Notifications\ServiceStatusUpdated;
use App\Models\Service;
use App\Models\Organization;

class SendBulkNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $notificationType;
    protected $userIds;
    protected $data;

    public $tries = 3;
    public $maxExceptions = 3;
    public $backoff = [10, 30, 60]; // segundos

    /**
     * Create a new job instance.
     */
    public function __construct(string $notificationType, array $userIds, array $data)
    {
        $this->notificationType = $notificationType;
        $this->userIds = $userIds;
        $this->data = $data;
    }    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Verificar si hay límites de notificaciones configurados
        if ($this->exceedsNotificationLimits()) {
            Log::warning('Notification job exceeds configured limits', [
                'user_count' => count($this->userIds),
                'type' => $this->notificationType,
                'job_id' => $this->job->getJobId() ?? 'unknown'
            ]);
            return;
        }
        
        // Procesar en lotes para evitar sobrecarga
        $users = User::whereIn('id', $this->userIds)->get();
        
        $totalChunks = ceil($users->count() / 50);
        $processedCount = 0;
        
        foreach ($users->chunk(50) as $index => $userChunk) {
            Log::info("Processing notification chunk " . ($index + 1) . " of $totalChunks", [
                'chunk_size' => $userChunk->count()
            ]);
            
            $processedCount += $this->sendNotificationsToUsers($userChunk);
        }
        
        Log::info("Bulk notification job completed", [
            'total_users' => $users->count(),
            'notifications_sent' => $processedCount
        ]);
    }
    
    /**
     * Verificar si el job excede los límites configurados
     */
    protected function exceedsNotificationLimits(): bool
    {
        // Limitar el número máximo de receptores
        $maxRecipients = config('notifications.limits.max_recipients', 1000);
        if (count($this->userIds) > $maxRecipients) {
            return true;
        }
        
        // Verificar límite diario de notificaciones masivas
        $bulkDailyLimit = config('notifications.limits.bulk_daily', 5);
        $todayCount = DB::table('jobs')
            ->where('queue', 'notifications')
            ->whereDate('created_at', now()->toDateString())
            ->count();
            
        if ($todayCount >= $bulkDailyLimit) {
            return true;
        }
        
        return false;
    }

    /**
     * Send notifications to a chunk of users.
     */    /**
     * Enviar notificaciones a un grupo de usuarios
     * 
     * @param Collection $users
     * @return int Número de notificaciones enviadas correctamente
     */
    private function sendNotificationsToUsers(Collection $users): int
    {
        $successCount = 0;
        
        foreach ($users as $user) {
            try {
                // Verificar límites por usuario
                if ($this->userExceedsLimits($user)) {
                    \Log::info("Notification limit exceeded for user {$user->id}");
                    continue;
                }
                
                $notification = $this->createNotification();
                if ($notification) {
                    $user->notify($notification);
                    $successCount++;
                    
                    // Registrar métricas de éxito
                    app('events')->dispatch('notification.sent', [
                        'user_id' => $user->id,
                        'notification_type' => $this->notificationType,
                        'data' => $this->data
                    ]);
                }
            } catch (\Exception $e) {
                // Log error pero continúa con otros usuarios
                \Log::error("Error sending notification to user {$user->id}: " . $e->getMessage(), [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'notification_type' => $this->notificationType
                ]);
            }
        }
        
        return $successCount;
    }
    
    /**
     * Verificar si un usuario ha excedido sus límites de notificaciones
     * 
     * @param User $user
     * @return bool
     */
    private function userExceedsLimits(User $user): bool
    {
        // Obtener límite diario por usuario
        $dailyLimit = config('notifications.limits.per_user_daily', 50);
        
        // Contar notificaciones enviadas hoy
        $todayCount = DB::table('notifications')
            ->where('notifiable_id', $user->id)
            ->where('notifiable_type', get_class($user))
            ->whereDate('created_at', now()->toDateString())
            ->count();
            
        return $todayCount >= $dailyLimit;
    }

    /**
     * Create the appropriate notification based on type.
     */
    private function createNotification()
    {
        switch ($this->notificationType) {
            case 'service_status':
                $service = Service::find($this->data['service_id']);
                if ($service) {
                    return new ServiceStatusUpdated(
                        $service,
                        $this->data['status_type'],
                        $this->data['message'] ?? null
                    );
                }
                break;

            case 'organization_alert':
                $organization = Organization::find($this->data['organization_id']);
                if ($organization) {
                    return new OrganizationAlert(
                        $organization,
                        $this->data['alert_type'],
                        $this->data['title'],
                        $this->data['message'],
                        $this->data['extra_data'] ?? []
                    );
                }
                break;

            case 'system_maintenance':
                $organization = Organization::first(); // Para alertas del sistema
                if ($organization) {
                    return new OrganizationAlert(
                        $organization,
                        'system_maintenance',
                        $this->data['title'] ?? 'Mantenimiento del Sistema',
                        $this->data['message'],
                        [
                            'maintenance_start' => $this->data['start_time'] ?? null,
                            'maintenance_end' => $this->data['end_time'] ?? null,
                            'affected_services' => $this->data['affected_services'] ?? [],
                        ]
                    );
                }
                break;

            case 'emergency_alert':
                $organization = Organization::find($this->data['organization_id'] ?? Organization::first()->id);
                if ($organization) {
                    return new OrganizationAlert(
                        $organization,
                        'emergency',
                        $this->data['title'] ?? 'Alerta de Emergencia',
                        $this->data['message'],
                        [
                            'emergency_level' => $this->data['emergency_level'] ?? 'high',
                            'location' => $this->data['location'] ?? null,
                            'contact_info' => $this->data['contact_info'] ?? null,
                        ]
                    );
                }
                break;
        }

        return null;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('SendBulkNotifications job failed: ' . $exception->getMessage(), [
            'notification_type' => $this->notificationType,
            'user_count' => count($this->userIds),
            'data' => $this->data,
            'exception' => $exception
        ]);
    }
}
