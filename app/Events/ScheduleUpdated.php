<?php

namespace App\Events;

use App\Models\Schedule;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScheduleUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public int $scheduleId;
    public int $instanceId;
    public string $action;

    public function __construct(Schedule $schedule, string $action)
    {
        $this->scheduleId = (int) $schedule->id;
        $this->instanceId = (int) $schedule->instance_id;
        $this->action     = $action;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('instances');
    }

    public function broadcastAs(): string
    {
        return 'ScheduleUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'schedule_id' => $this->scheduleId,
            'instance_id' => $this->instanceId,
            'action'      => $this->action,
        ];
    }
}
