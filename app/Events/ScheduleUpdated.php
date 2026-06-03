<?php

namespace App\Events;

use App\Models\Schedule;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScheduleUpdated {
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly Schedule $schedule, public readonly string $action) {
    }
}
