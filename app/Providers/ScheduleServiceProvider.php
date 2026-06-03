<?php

namespace App\Providers;

use App\Jobs\CleanOldSchedules;
use App\Jobs\NotifyUpcomingSchedules;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\ServiceProvider;

class ScheduleServiceProvider extends ServiceProvider {
    public function boot(): void {
        Schedule::job(CleanOldSchedules::class)->everyMinute();
        Schedule::job(NotifyUpcomingSchedules::class)->everyMinute();
    }
}
