<?php

namespace App\Services;

use App\Events\ScheduleUpdated;
use App\Models\Instance;
use Phobiavr\PhoberLaravelCommon\Contracts\SessionScheduleHandlerInterface;
use Phobiavr\PhoberLaravelCommon\Enums\ScheduleEnum;

class SessionScheduleHandler implements SessionScheduleHandlerInterface
{
    public function __construct(private readonly ScheduleService $scheduleService) {}

    public function handle(int $instanceId, string $action, ?int $time): void
    {
        $instance = Instance::find($instanceId);
        if (!$instance) return;

        $active = $instance->getActiveSchedule();
        if ($active) {
            $cancelled = $this->scheduleService->cancel($active->id);
            ScheduleUpdated::dispatch($cancelled, 'cancelled');
        }

        if ($action === 'queue') {
            $schedule = $this->scheduleService->create([
                'type'        => ScheduleEnum::QUEUE->value,
                'instance_id' => $instanceId,
                'start'       => now(),
                'end'         => null,
            ]);
            ScheduleUpdated::dispatch($schedule, 'created');
        } elseif ($action === 'start') {
            $schedule = $this->scheduleService->create([
                'type'        => ScheduleEnum::IN_SESSION->value,
                'instance_id' => $instanceId,
                'start'       => now(),
                'end'         => now()->addMinutes($time),
            ]);
            ScheduleUpdated::dispatch($schedule, 'created');
        }
    }
}
