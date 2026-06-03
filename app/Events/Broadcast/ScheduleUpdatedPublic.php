<?php

namespace App\Events\Broadcast;

use App\Models\Instance;
use App\Models\Schedule;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScheduleUpdatedPublic implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public readonly Schedule $schedule) {}

    public function broadcastOn(): array
    {
        $channels = [new Channel('instances')];

        $mac = Instance::query()->whereKey($this->schedule->instance_id)->value('mac_address');
        if ($mac) {
            $slug = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $mac));
            $channels[] = new Channel('schedule.' . $slug);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'ScheduleUpdated';
    }

    public function broadcastWith(): array
    {
        return [];
    }
}
