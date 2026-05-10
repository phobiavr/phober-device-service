<?php

namespace App\Listeners;

use App\Events\ScheduleChanged;
use App\Events\ScheduleUpdated;

class BroadcastScheduleChanged
{
    public function handle(ScheduleUpdated $event): void
    {
        ScheduleChanged::dispatch();
    }
}
