<?php

namespace App\Events;

use App\Models\Schedule;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScheduleUpdated
{
    use Dispatchable;
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
}
