<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Service;

class ServiceUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $service;
    public $updateType;
    public $changes;

    /**
     * Create a new event instance.
     */
    public function __construct(Service $service, string $updateType, array $changes = [])
    {
        $this->service = $service;
        $this->updateType = $updateType;
        $this->changes = $changes;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('organization.' . $this->service->organization_id),
            new PrivateChannel('service.' . $this->service->id),
            new Channel('public-updates'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'service.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'service_id' => $this->service->id,
            'service_name' => $this->service->name,
            'organization_id' => $this->service->organization_id,
            'update_type' => $this->updateType,
            'changes' => $this->changes,
            'location' => [
                'latitude' => $this->service->latitude,
                'longitude' => $this->service->longitude,
            ],
            'timestamp' => now()->toISOString(),
        ];
    }
}
