<?php

namespace App\Listeners;

use App\Events\Broadcast\ScheduleUpdatedOverlay;
use App\Events\Broadcast\ScheduleUpdatedPrivate;
use App\Events\Broadcast\ScheduleUpdatedPublic;
use App\Events\ScheduleUpdated;

class ScheduleUpdatedListener
{
    public function handle(ScheduleUpdated $event): void
    {
        broadcast(new ScheduleUpdatedPublic($event->schedule));
        broadcast(new ScheduleUpdatedPrivate($event->schedule, $event->action));
        broadcast(new ScheduleUpdatedOverlay($event->schedule));
    }
}
