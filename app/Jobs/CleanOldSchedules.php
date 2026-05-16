<?php

namespace App\Jobs;

use App\Models\Schedule;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Phobiavr\PhoberLaravelCommon\Enums\ScheduleEnum;

class CleanOldSchedules implements ShouldQueue {
    use Queueable;

    public function handle(): void {
        Schedule::where('type', ScheduleEnum::CANCELED->value)
            ->orWhere('end', '<', now())
            ->delete();
    }
}
