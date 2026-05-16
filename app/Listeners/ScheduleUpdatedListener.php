<?php

namespace App\Listeners;

use App\Events\ScheduleUpdated;
use App\Events\ScheduleUpdatedPrivate;
use App\Events\ScheduleUpdatedPublic;

class ScheduleUpdatedListener
{
    public function handle(ScheduleUpdated $event): void
    {
        broadcast(new ScheduleUpdatedPublic($event->instanceId));
        broadcast(new ScheduleUpdatedPrivate($event->scheduleId, $event->instanceId, $event->action));
    }
}
