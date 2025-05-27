<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RealTimeStatsUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $organizationId;
    public $stats;
    public $statsType;

    /**
     * Create a new event instance.
     */
    public function __construct(int $organizationId, array $stats, string $statsType = 'general')
    {
        $this->organizationId = $organizationId;
        $this->stats = $stats;
        $this->statsType = $statsType;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('stats.' . $this->organizationId),
            new PrivateChannel('organization.' . $this->organizationId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'stats.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'organization_id' => $this->organizationId,
            'stats_type' => $this->statsType,
            'stats' => $this->stats,
            'timestamp' => now()->toISOString(),
        ];
    }
}
