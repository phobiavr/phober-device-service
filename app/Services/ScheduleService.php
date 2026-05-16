<?php

namespace App\Services;

use App\Models\Instance;
use App\Models\Schedule;
use Phobiavr\PhoberLaravelCommon\Enums\ScheduleEnum;

class ScheduleService {
    public function save(ScheduleEnum $type, int $instanceId, ?int $minutes = null, ?Schedule $schedule = null): Schedule {
        $schedule ??= new Schedule(['instance_id' => $instanceId]);

        $schedule->type  = $type;
        $schedule->start = now();
        $schedule->end   = $minutes ? now()->addMinutes($minutes) : null;
        $schedule->save();

        return $schedule;
    }

    public function activeForInstance(string $idOrMacAddress): ?Schedule {
        $instance = Instance::findByIdOrMacAddressOrFail($idOrMacAddress);

        return $instance->getActiveSchedule();
    }

    public function cancel(int $id): Schedule {
        $schedule = Schedule::where('type', '<>', ScheduleEnum::CANCELED->value)->findOrFail($id);

        $schedule->type = ScheduleEnum::CANCELED;
        $schedule->save();

        return $schedule;
    }
}
