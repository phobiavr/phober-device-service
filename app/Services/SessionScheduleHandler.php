<?php

namespace App\Services;

use App\Events\ScheduleUpdated;
use App\Models\Instance;
use App\Models\Schedule;
use Illuminate\Support\Facades\DB;
use Phobiavr\PhoberLaravelCommon\Contracts\SessionScheduleHandlerInterface;
use Phobiavr\PhoberLaravelCommon\Enums\ScheduleEnum;
use Phobiavr\PhoberLaravelCommon\Enums\SessionScheduleActionEnum;
use Phobiavr\PhoberLaravelCommon\Exceptions\InstanceNotFoundException;
use Phobiavr\PhoberLaravelCommon\Exceptions\ScheduleConflictException;
use Phobiavr\PhoberLaravelCommon\Jobs\CancelSession;

readonly class SessionScheduleHandler implements SessionScheduleHandlerInterface
{
    public function __construct(private ScheduleService $scheduleService) {}

    public function handle(int $instanceId, SessionScheduleActionEnum $action, ?int $time, ?int $sessionId, ?string $startedAt = null): void
    {
        DB::transaction(function () use ($instanceId, $action, $time, $sessionId, $startedAt) {
            $instance = Instance::query()->whereKey($instanceId)->lockForUpdate()->first();
            if (!$instance) {
                if ($sessionId !== null) {
                    CancelSession::dispatch($sessionId)->onQueue('staff');
                }

                throw new InstanceNotFoundException("Instance {$instanceId} does not exist.");
            }

            if ($action === SessionScheduleActionEnum::QUEUE) {
                /** @var Schedule|null $active */
                $active = Schedule::query()
                    ->where('instance_id', $instanceId)
                    ->lockForUpdate()
                    ->get()
                    ->first(fn(Schedule $schedule) => $schedule->isActive());

                if ($active) {
                    if ($sessionId !== null && $active->session_id === $sessionId) {
                        // Redelivery of our own successful QUEUE job — the schedule
                        // it created is already sitting there. Idempotent no-op.
                        return;
                    }

                    if ($sessionId !== null) {
                        CancelSession::dispatch($sessionId)->onQueue('staff');
                    }

                    throw new ScheduleConflictException('Instance already has an active schedule.');
                }

                $schedule = $this->scheduleService->save(ScheduleEnum::QUEUE, $instanceId, sessionId: $sessionId, startedAt: $startedAt);
                ScheduleUpdated::dispatch($schedule, 'created');

                return;
            }

            $type = match ($action) {
                SessionScheduleActionEnum::START => ScheduleEnum::IN_SESSION,
                SessionScheduleActionEnum::CANCEL, SessionScheduleActionEnum::FINISH => ScheduleEnum::CANCELED,
            };

            $searchTypes = match ($action) {
                SessionScheduleActionEnum::START => [ScheduleEnum::QUEUE->value, ScheduleEnum::IN_SESSION->value],
                SessionScheduleActionEnum::CANCEL, SessionScheduleActionEnum::FINISH => [ScheduleEnum::QUEUE->value, ScheduleEnum::IN_SESSION->value, ScheduleEnum::CANCELED->value],
            };

            /** @var Schedule|null $queued */
            $queued = Schedule::query()
                ->where('instance_id', $instanceId)
                ->whereIn('type', $searchTypes)
                ->lockForUpdate()
                ->first();

            if ($queued?->type === $type->value) {
                if ($sessionId !== null && $queued->session_id === $sessionId) {
                    // Redelivery of our own successful job — already in the
                    // state we were asked to reach. Idempotent no-op.
                    return;
                }

                if ($sessionId !== null) {
                    CancelSession::dispatch($sessionId)->onQueue('staff');
                }

                throw new ScheduleConflictException('Schedule is already in the requested state.');
            }

            $schedule = $this->scheduleService->save($type, $instanceId, $time, $queued, sessionId: $sessionId, startedAt: $startedAt);
            ScheduleUpdated::dispatch($schedule, $queued ? 'updated' : 'created');
        });
    }
}
