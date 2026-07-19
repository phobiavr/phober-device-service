<?php

namespace App\Jobs;

use App\Events\Broadcast\ScheduleUpdatedPrivate;
use App\Events\Broadcast\ScheduleUpdatedPublic;
use App\Models\Schedule;
use Illuminate\Foundation\Queue\Queueable;
use Phobiavr\PhoberLaravelCommon\Enums\ScheduleEnum;

class NotifyUpcomingSchedules {
    use Queueable;

    public const WINDOW_MINUTES = 15;

    public function handle(): void {
        Schedule::where('start', '>', now())
            ->where('start', '<=', now()->addMinutes(self::WINDOW_MINUTES))
            ->where('type', '!=', ScheduleEnum::CANCELED->value)
            ->get()
            ->each(function ($schedule) {
                broadcast(new ScheduleUpdatedPrivate($schedule, 'upcoming'));
                broadcast(new ScheduleUpdatedPublic($schedule));
            });
    }
}
