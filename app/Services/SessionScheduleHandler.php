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
        /** @var Instance $instance */
        $instance = Instance::find($instanceId);
        if (!$instance) return;

        $active = $instance->getActiveSchedule();

        if ($active?->type === ScheduleEnum::QUEUE->value) {
            if ($action === 'start') {
                $updated = $this->scheduleService->save(ScheduleEnum::IN_SESSION, $active->instance_id, $time, $active);
                ScheduleUpdated::dispatch($updated, 'updated');
                return;
            }

            if ($action === 'cancelled') {
                $cancelled = $this->scheduleService->cancel($active->id);
                ScheduleUpdated::dispatch($cancelled, 'cancelled');
                return;
            }
        }

        if ($active) {
            $cancelled = $this->scheduleService->cancel($active->id);
            ScheduleUpdated::dispatch($cancelled, 'cancelled');
        }

        if ($action === 'queue') {
            $schedule = $this->scheduleService->save(ScheduleEnum::QUEUE, $instanceId);
            ScheduleUpdated::dispatch($schedule, 'created');
        } elseif ($action === 'start') {
            $schedule = $this->scheduleService->save(ScheduleEnum::IN_SESSION, $instanceId, $time);
            ScheduleUpdated::dispatch($schedule, 'created');
        }
    }
}
