<?php

namespace App\Events;

use App\Models\Instance;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScheduleUpdatedPrivate implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly int $scheduleId,
        public readonly int $instanceId,
        public readonly string $action,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('instances')];
    }

    public function broadcastAs(): string
    {
        return 'ScheduleUpdated';
    }

    public function broadcastWith(): array
    {
        $type      = 'N/A';
        $countdown = 0;

        $instance = Instance::find($this->instanceId);
        $schedule = $instance?->getActiveSchedule();

        if ($schedule) {
            $type      = $schedule->type;
            $countdown = $schedule->end ? now()->diffInSeconds($schedule->end) : -1;
        }

        return [
            'schedule_id' => $this->scheduleId,
            'instance_id' => $this->instanceId,
            'action'      => $this->action,
            'type'        => $type,
            'countdown'   => (int) $countdown,
        ];
    }
}
