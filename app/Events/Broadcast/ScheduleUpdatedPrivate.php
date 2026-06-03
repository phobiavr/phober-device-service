<?php

namespace App\Events\Broadcast;

use App\Models\Instance;
use App\Models\Schedule;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScheduleUpdatedPrivate implements ShouldBroadcast {
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public readonly Schedule $schedule, public readonly string $action) {
    }

    public function broadcastOn(): array {
        return [new PrivateChannel('instances')];
    }

    public function broadcastAs(): string {
        return 'ScheduleUpdated';
    }

    public function broadcastWith(): array {
        $type = 'N/A';
        $countdown = 0;

        /** @var Instance $instance */
        $instance = Instance::find($this->schedule->instanceId);
        //TODO:: what?!
        $schedule = $instance?->getActiveSchedule();

        if ($schedule) {
            $type = $schedule->type;
            $countdown = $schedule->end ? now()->diffInSeconds($schedule->end) : -1;
        }

        $upcomingType = null;
        $startsIn = null;

        $upcoming = $instance?->getUpcomingSchedule();
        if ($upcoming) {
            $upcomingType = $upcoming->type;
            $startsIn = (int) now()->diffInSeconds($upcoming->start);
        }

        return [
            'schedule_id' => $this->schedule->id,
            'instance_id' => $this->schedule->instance_id,
            'action' => $this->action,
            'type' => $type,
            'countdown' => (int) $countdown,
            'upcoming_type' => $upcomingType,
            'starts_in' => $startsIn,
        ];
    }
}
