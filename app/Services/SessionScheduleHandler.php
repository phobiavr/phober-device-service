<?php

namespace App\Services;

use App\Events\ScheduleUpdated;
use App\Models\Instance;
use App\Models\Schedule;
use Phobiavr\PhoberLaravelCommon\Contracts\SessionScheduleHandlerInterface;
use Phobiavr\PhoberLaravelCommon\Enums\ScheduleEnum;
use Phobiavr\PhoberLaravelCommon\Enums\SessionScheduleActionEnum;

class SessionScheduleHandler implements SessionScheduleHandlerInterface
{
    public function __construct(private readonly ScheduleService $scheduleService) {}

    public function handle(int $instanceId, SessionScheduleActionEnum $action, ?int $time, ?int $sessionId, ?string $startedAt = null): void
    {
        /** @var Instance $instance */
        $instance = Instance::find($instanceId);
        if (!$instance) return;

        /** @var Schedule $active */
        $active = $instance->getActiveSchedule();

        if ($active?->type === ScheduleEnum::QUEUE->value) {
            if ($action === SessionScheduleActionEnum::START) {
                $updated = $this->scheduleService->save(ScheduleEnum::IN_SESSION, $active->instance_id, $time, $active, startedAt: $startedAt);
                ScheduleUpdated::dispatch($updated, 'updated');
                return;
            }

            if ($action === SessionScheduleActionEnum::CANCEL) {
                $cancelled = $this->scheduleService->cancel($active->id);
                ScheduleUpdated::dispatch($cancelled, 'cancelled');
                return;
            }
        }

        if ($active) {
            $cancelled = $this->scheduleService->cancel($active->id);
            ScheduleUpdated::dispatch($cancelled, 'cancelled');
        }

        if ($action === SessionScheduleActionEnum::QUEUE) {
            $schedule = $this->scheduleService->save(ScheduleEnum::QUEUE, $instanceId, sessionId: $sessionId, startedAt: $startedAt);
            ScheduleUpdated::dispatch($schedule, 'created');
        } elseif ($action === SessionScheduleActionEnum::START) {
            $schedule = $this->scheduleService->save(ScheduleEnum::IN_SESSION, $instanceId, $time, sessionId: $sessionId, startedAt: $startedAt);
            ScheduleUpdated::dispatch($schedule, 'created');
        }
    }
}
