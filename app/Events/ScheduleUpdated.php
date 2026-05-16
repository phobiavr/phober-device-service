<?php

namespace App\Events;

use App\Models\Instance;
use App\Models\Schedule;
use Illuminate\Broadcasting\Channel;
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

    /**
     * @return array<int, Channel|PrivateChannel>
     */
    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('instances')];

        $mac = Instance::query()->whereKey($this->instanceId)->value('mac_address');

        if ($mac) {
            $slug = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $mac));
            $channels[] = new Channel('schedule.' . $slug);
        }

        $channels[] = new Channel('instances');

        return $channels;
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
