<?php

namespace App\Services;

use App\Models\Instance;
use App\Models\Schedule;
use Phobiavr\PhoberLaravelCommon\Enums\ScheduleEnum;

class ScheduleService {
    public function save(ScheduleEnum $type, int $instanceId, ?int $minutes = null, ?Schedule $schedule = null, ?int $sessionId = null, ?string $startedAt = null): Schedule {
        $schedule ??= new Schedule(['instance_id' => $instanceId]);

        $start = $startedAt ? \Carbon\Carbon::parse($startedAt) : now();

        $schedule->type = $type;
        $schedule->start = $start;
        $schedule->end = $minutes ? $start->copy()->addMinutes($minutes) : null;
        if ($sessionId) {
            $schedule->session_id = $sessionId;
        }
        $schedule->save();

        return $schedule;
    }

    public function activeForInstanceById(int $id): ?Schedule {
        $instance = Instance::findOrFail($id);

        return $instance->getActiveSchedule();
    }

    public function activeForInstanceByMac(string $macAddress): ?Schedule {
        $instance = Instance::where('mac_address', $macAddress)->firstOrFail();

        return $instance->getActiveSchedule();
    }

    public function cancel(int $id): Schedule {
        $schedule = Schedule::where('type', '<>', ScheduleEnum::CANCELED->value)->findOrFail($id);

        $schedule->type = ScheduleEnum::CANCELED;
        $schedule->save();

        return $schedule;
    }
}
