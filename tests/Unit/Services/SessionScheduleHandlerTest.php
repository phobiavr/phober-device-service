<?php

namespace Tests\Unit\Services;

use App\Events\ScheduleUpdated;
use App\Models\Instance;
use App\Models\Schedule;
use App\Services\SessionScheduleHandler;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Phobiavr\PhoberLaravelCommon\Enums\ScheduleEnum;
use Phobiavr\PhoberLaravelCommon\Enums\SessionScheduleActionEnum;
use Phobiavr\PhoberLaravelCommon\Exceptions\ScheduleConflictException;
use Phobiavr\PhoberLaravelCommon\Jobs\CancelSession;
use Phobiavr\PhoberLaravelCommon\Testing\ClearsExistingRows;
use Tests\TestCase;

class SessionScheduleHandlerTest extends TestCase
{
    use ClearsExistingRows;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearExistingRows(Instance::class);
    }

    private function assertConflict(int $instanceId, SessionScheduleActionEnum $action, ?int $time, ?int $sessionId): void
    {
        try {
            app(SessionScheduleHandler::class)->handle($instanceId, $action, $time, $sessionId, null);
            $this->fail('Expected ScheduleConflictException was not thrown.');
        } catch (ScheduleConflictException) {
            // expected
        }
    }

    // --- QUEUE ---

    public function test_queues_a_new_schedule_when_the_instance_has_no_active_schedule(): void
    {
        Event::fake();
        $instance = Instance::factory()->create();

        app(SessionScheduleHandler::class)->handle($instance->id, SessionScheduleActionEnum::QUEUE, null, 10, null);

        $schedule = Schedule::where('instance_id', $instance->id)->sole();
        $this->assertSame(ScheduleEnum::QUEUE->value, $schedule->type);
        $this->assertSame(10, $schedule->session_id);
        Event::assertDispatched(ScheduleUpdated::class, fn ($e) => $e->action === 'created');
    }

    public function test_throws_a_conflict_and_dispatches_a_session_rollback_when_queueing_against_an_active_schedule(): void
    {
        Event::fake();
        Queue::fake();
        $instance = Instance::factory()->create();
        $active = Schedule::factory()->for($instance)->create(); // IN_SESSION by default, active

        $this->assertConflict($instance->id, SessionScheduleActionEnum::QUEUE, null, 55);

        $this->assertSame(1, Schedule::where('instance_id', $instance->id)->count());
        $this->assertSame(ScheduleEnum::IN_SESSION->value, $active->fresh()->type);
        Event::assertNotDispatched(ScheduleUpdated::class);
        Queue::assertPushedOn('staff', CancelSession::class, fn ($job) => $job->sessionId === 55);
    }

    public function test_throws_a_conflict_without_a_rollback_dispatch_when_queueing_and_no_session_id_is_given(): void
    {
        Event::fake();
        Queue::fake();
        $instance = Instance::factory()->create();
        Schedule::factory()->for($instance)->create();

        $this->assertConflict($instance->id, SessionScheduleActionEnum::QUEUE, null, null);

        Queue::assertNotPushed(CancelSession::class);
    }

    public function test_throws_a_conflict_and_rolls_back_its_own_session_when_a_queue_job_is_redelivered(): void
    {
        // Known trade-off: a redelivered QUEUE job for a session that's
        // already queued hits the exact same "instance already has an
        // active schedule" path as a genuine foreign conflict — there's no
        // special-casing for "this active schedule is actually mine", so it
        // still rolls back its own (valid) session via CancelSession.
        Event::fake();
        Queue::fake();
        $instance = Instance::factory()->create();
        Schedule::factory()->for($instance)->queue()->create(['session_id' => 10]);

        $this->assertConflict($instance->id, SessionScheduleActionEnum::QUEUE, null, 10);

        Queue::assertPushedOn('staff', CancelSession::class, fn ($job) => $job->sessionId === 10);
    }

    // --- START ---

    public function test_promotes_an_existing_queue_schedule_to_in_session_when_starting(): void
    {
        Event::fake();
        $instance = Instance::factory()->create();
        $queued = Schedule::factory()->for($instance)->queue()->create(['session_id' => 5]);

        app(SessionScheduleHandler::class)->handle($instance->id, SessionScheduleActionEnum::START, 30, 5, null);

        $this->assertSame(1, Schedule::where('instance_id', $instance->id)->count());
        $fresh = $queued->fresh();
        $this->assertSame(ScheduleEnum::IN_SESSION->value, $fresh->type);
        $this->assertSame(30.0, $fresh->start->diffInMinutes($fresh->end));
        Event::assertDispatched(ScheduleUpdated::class, fn ($e) => $e->action === 'updated' && $e->schedule->is($queued));
    }

    public function test_creates_an_in_session_schedule_when_starting_with_no_existing_schedule(): void
    {
        Event::fake();
        $instance = Instance::factory()->create();

        app(SessionScheduleHandler::class)->handle($instance->id, SessionScheduleActionEnum::START, 15, 11, null);

        $schedule = Schedule::where('instance_id', $instance->id)->sole();
        $this->assertSame(ScheduleEnum::IN_SESSION->value, $schedule->type);
        Event::assertDispatched(ScheduleUpdated::class, fn ($e) => $e->action === 'created');
    }

    public function test_throws_a_conflict_when_starting_a_session_that_is_already_in_session(): void
    {
        // Redelivery of the same START job — the schedule is already where
        // it should be, so this fails loud instead of creating a duplicate
        // IN_SESSION row.
        Event::fake();
        $instance = Instance::factory()->create();
        $inSession = Schedule::factory()->for($instance)->create(); // IN_SESSION by default

        $this->assertConflict($instance->id, SessionScheduleActionEnum::START, 30, null);

        $this->assertSame(1, Schedule::where('instance_id', $instance->id)->count());
        $this->assertSame(ScheduleEnum::IN_SESSION->value, $inSession->fresh()->type);
        Event::assertNotDispatched(ScheduleUpdated::class);
    }

    // --- CANCEL / FINISH ---

    public function test_cancels_an_existing_queue_schedule(): void
    {
        Event::fake();
        $instance = Instance::factory()->create();
        $queued = Schedule::factory()->for($instance)->queue()->create();

        app(SessionScheduleHandler::class)->handle($instance->id, SessionScheduleActionEnum::CANCEL, null, null, null);

        $this->assertSame(1, Schedule::where('instance_id', $instance->id)->count());
        $this->assertSame(ScheduleEnum::CANCELED->value, $queued->fresh()->type);
        Event::assertDispatched(ScheduleUpdated::class, fn ($e) => $e->action === 'updated');
    }

    public function test_cancels_an_existing_in_session_schedule_when_finished(): void
    {
        Event::fake();
        $instance = Instance::factory()->create();
        $inSession = Schedule::factory()->for($instance)->create();

        app(SessionScheduleHandler::class)->handle($instance->id, SessionScheduleActionEnum::FINISH, null, null, null);

        $this->assertSame(1, Schedule::where('instance_id', $instance->id)->count());
        $this->assertSame(ScheduleEnum::CANCELED->value, $inSession->fresh()->type);
        Event::assertDispatched(ScheduleUpdated::class, fn ($e) => $e->action === 'updated');
    }

    public function test_creates_a_canceled_schedule_when_canceling_with_no_existing_schedule(): void
    {
        Event::fake();
        $instance = Instance::factory()->create();

        app(SessionScheduleHandler::class)->handle($instance->id, SessionScheduleActionEnum::CANCEL, null, null, null);

        $schedule = Schedule::where('instance_id', $instance->id)->sole();
        $this->assertSame(ScheduleEnum::CANCELED->value, $schedule->type);
        Event::assertDispatched(ScheduleUpdated::class, fn ($e) => $e->action === 'created');
    }

    public function test_throws_a_conflict_when_canceling_a_schedule_that_is_already_canceled(): void
    {
        // Redelivery of the same CANCEL job.
        Event::fake();
        $instance = Instance::factory()->create();
        Schedule::factory()->for($instance)->canceled()->create();

        $this->assertConflict($instance->id, SessionScheduleActionEnum::CANCEL, null, null);

        $this->assertSame(1, Schedule::where('instance_id', $instance->id)->count());
        Event::assertNotDispatched(ScheduleUpdated::class);
    }

    public function test_throws_a_conflict_when_finishing_a_schedule_that_is_already_canceled(): void
    {
        // Redelivery of the same FINISH job.
        Event::fake();
        $instance = Instance::factory()->create();
        Schedule::factory()->for($instance)->canceled()->create();

        $this->assertConflict($instance->id, SessionScheduleActionEnum::FINISH, null, null);

        $this->assertSame(1, Schedule::where('instance_id', $instance->id)->count());
        Event::assertNotDispatched(ScheduleUpdated::class);
    }

    public function test_does_nothing_when_the_instance_does_not_exist(): void
    {
        Event::fake();

        app(SessionScheduleHandler::class)->handle(999999, SessionScheduleActionEnum::QUEUE, null, 1, null);

        $this->assertSame(0, Schedule::where('instance_id', 999999)->count());
        Event::assertNotDispatched(ScheduleUpdated::class);
    }
}
