<?php

namespace App\Services;

use App\Models\Instance;
use App\Models\Schedule;
use Phobiavr\PhoberLaravelCommon\Enums\ScheduleEnum;

class ScheduleService {
    public function create(array $data): Schedule {
        return Schedule::create($data);
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
