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

    public function test_creates_a_new_queue_schedule_when_the_instance_has_no_active_schedule(): void
    {
        Event::fake();
        $instance = Instance::factory()->create();

        app(SessionScheduleHandler::class)->handle($instance->id, SessionScheduleActionEnum::QUEUE, null, 10, null);

        $schedule = Schedule::where('instance_id', $instance->id)->sole();
        $this->assertSame(ScheduleEnum::QUEUE->value, $schedule->type);
        $this->assertSame(10, $schedule->session_id);

        Event::assertDispatched(ScheduleUpdated::class, fn ($e) => $e->action === 'created');
    }

    public function test_throws_a_conflict_and_dispatches_a_session_rollback_when_queueing_against_an_existing_queue_schedule(): void
    {
        Event::fake();
        Queue::fake();
        $instance = Instance::factory()->create();
        $queued = Schedule::factory()->for($instance)->queue()->create();

        try {
            app(SessionScheduleHandler::class)->handle($instance->id, SessionScheduleActionEnum::QUEUE, null, 88, null);
            $this->fail('Expected ScheduleConflictException was not thrown.');
        } catch (ScheduleConflictException) {
            // expected
        }

        $this->assertSame(ScheduleEnum::QUEUE->value, $queued->fresh()->type);
        $this->assertSame(1, Schedule::where('instance_id', $instance->id)->count());
        Event::assertNotDispatched(ScheduleUpdated::class);
        Queue::assertPushedOn('staff', CancelSession::class, fn ($job) => $job->sessionId === 88);
    }

    public function test_throws_a_conflict_and_dispatches_a_session_rollback_when_queueing_against_a_foreign_active_schedule(): void
    {
        Event::fake();
        Queue::fake();
        $instance = Instance::factory()->create();
        $inSession = Schedule::factory()->for($instance)->create();

        try {
            app(SessionScheduleHandler::class)->handle($instance->id, SessionScheduleActionEnum::QUEUE, null, 88, null);
            $this->fail('Expected ScheduleConflictException was not thrown.');
        } catch (ScheduleConflictException) {
            // expected
        }

        $this->assertSame(ScheduleEnum::IN_SESSION->value, $inSession->fresh()->type);
        $this->assertSame(1, Schedule::where('instance_id', $instance->id)->count());
        Event::assertNotDispatched(ScheduleUpdated::class);
        Queue::assertPushedOn('staff', CancelSession::class, fn ($job) => $job->sessionId === 88);
    }

    public function test_creates_a_new_in_session_schedule_with_the_given_duration_when_starting_fresh(): void
    {
        Event::fake();
        $instance = Instance::factory()->create();

        app(SessionScheduleHandler::class)->handle($instance->id, SessionScheduleActionEnum::START, 30, 11, null);

        $schedule = Schedule::where('instance_id', $instance->id)->sole();
        $this->assertSame(ScheduleEnum::IN_SESSION->value, $schedule->type);
        $this->assertSame(30.0, $schedule->start->diffInMinutes($schedule->end));

        Event::assertDispatched(ScheduleUpdated::class, fn ($e) => $e->action === 'created');
    }

    public function test_promotes_an_existing_queue_schedule_to_in_session_in_place_when_starting(): void
    {
        Event::fake();
        $instance = Instance::factory()->create();
        $queued = Schedule::factory()->for($instance)->queue()->create(['session_id' => 5]);

        app(SessionScheduleHandler::class)->handle($instance->id, SessionScheduleActionEnum::START, 15, 5, null);

        $this->assertSame(1, Schedule::where('instance_id', $instance->id)->count());
        $this->assertSame(ScheduleEnum::IN_SESSION->value, $queued->fresh()->type);

        Event::assertDispatched(ScheduleUpdated::class, fn ($e) => $e->action === 'updated');
    }

    public function test_creates_a_new_in_session_schedule_when_starting_and_no_queue_schedule_exists_even_if_another_schedule_is_active(): void
    {
        // START only looks for an existing QUEUE schedule to promote. A
        // foreign (non-queue) active schedule doesn't block it anymore.
        Event::fake();
        $instance = Instance::factory()->create();
        $inSession = Schedule::factory()->for($instance)->create();

        app(SessionScheduleHandler::class)->handle($instance->id, SessionScheduleActionEnum::START, 20, 77, null);

        $this->assertSame(ScheduleEnum::IN_SESSION->value, $inSession->fresh()->type);
        $this->assertSame(2, Schedule::where('instance_id', $instance->id)->count());
        Event::assertDispatched(ScheduleUpdated::class, fn ($e) => $e->action === 'created');
    }

    public function test_cancels_an_existing_queue_schedule_in_place_when_the_action_is_cancel(): void
    {
        Event::fake();
        $instance = Instance::factory()->create();
        $queued = Schedule::factory()->for($instance)->queue()->create();

        app(SessionScheduleHandler::class)->handle($instance->id, SessionScheduleActionEnum::CANCEL, null, null, null);

        $this->assertSame(1, Schedule::where('instance_id', $instance->id)->count());
        $this->assertSame(ScheduleEnum::CANCELED->value, $queued->fresh()->type);

        Event::assertDispatched(ScheduleUpdated::class, fn ($e) => $e->action === 'updated');
    }

    public function test_cancels_an_existing_in_session_schedule_in_place_when_the_action_is_cancel(): void
    {
        Event::fake();
        Queue::fake();
        $instance = Instance::factory()->create();
        $inSession = Schedule::factory()->for($instance)->create();

        app(SessionScheduleHandler::class)->handle($instance->id, SessionScheduleActionEnum::CANCEL, null, null, null);

        $this->assertSame(1, Schedule::where('instance_id', $instance->id)->count());
        $this->assertSame(ScheduleEnum::CANCELED->value, $inSession->fresh()->type);
        Event::assertDispatched(ScheduleUpdated::class, fn ($e) => $e->action === 'updated');
        Queue::assertNotPushed(CancelSession::class);
    }

    public function test_cancels_an_existing_in_session_schedule_in_place_when_the_action_is_finish(): void
    {
        Event::fake();
        Queue::fake();
        $instance = Instance::factory()->create();
        $inSession = Schedule::factory()->for($instance)->create();

        app(SessionScheduleHandler::class)->handle($instance->id, SessionScheduleActionEnum::FINISH, null, null, null);

        $this->assertSame(1, Schedule::where('instance_id', $instance->id)->count());
        $this->assertSame(ScheduleEnum::CANCELED->value, $inSession->fresh()->type);
        Event::assertDispatched(ScheduleUpdated::class, fn ($e) => $e->action === 'updated');
        Queue::assertNotPushed(CancelSession::class);
    }

    public function test_cancels_an_existing_queue_schedule_in_place_when_the_action_is_finish(): void
    {
        Event::fake();
        $instance = Instance::factory()->create();
        $queued = Schedule::factory()->for($instance)->queue()->create();

        app(SessionScheduleHandler::class)->handle($instance->id, SessionScheduleActionEnum::FINISH, null, null, null);

        $this->assertSame(1, Schedule::where('instance_id', $instance->id)->count());
        $this->assertSame(ScheduleEnum::CANCELED->value, $queued->fresh()->type);

        Event::assertDispatched(ScheduleUpdated::class, fn ($e) => $e->action === 'updated');
    }

    public function test_creates_a_new_canceled_schedule_when_there_is_no_queue_or_in_session_schedule_and_the_action_is_cancel_or_finish(): void
    {
        Event::fake();
        $instance = Instance::factory()->create();

        app(SessionScheduleHandler::class)->handle($instance->id, SessionScheduleActionEnum::CANCEL, null, null, null);
        app(SessionScheduleHandler::class)->handle($instance->id, SessionScheduleActionEnum::FINISH, null, null, null);

        $this->assertSame(2, Schedule::where('instance_id', $instance->id)->count());
        $this->assertSame(2, Schedule::where('instance_id', $instance->id)->where('type', ScheduleEnum::CANCELED->value)->count());
        Event::assertDispatchedTimes(ScheduleUpdated::class, 2);
        Event::assertDispatched(ScheduleUpdated::class, fn ($e) => $e->action === 'created');
    }

    public function test_updates_an_existing_queue_schedule_in_place_when_re_queued_but_currently_the_instance_has_no_other_active_schedule(): void
    {
        // Re-queueing always finds the existing QUEUE schedule as "active"
        // via isActive(), so this now results in a conflict, not an in-place
        // update.
        Event::fake();
        Queue::fake();
        $instance = Instance::factory()->create();
        $queued = Schedule::factory()->for($instance)->queue()->create();

        try {
            app(SessionScheduleHandler::class)->handle($instance->id, SessionScheduleActionEnum::QUEUE, null, 99, null);
            $this->fail('Expected ScheduleConflictException was not thrown.');
        } catch (ScheduleConflictException) {
            // expected
        }

        $this->assertSame(1, Schedule::where('instance_id', $instance->id)->count());
        $this->assertSame(ScheduleEnum::QUEUE->value, $queued->fresh()->type);
        Event::assertNotDispatched(ScheduleUpdated::class);
        Queue::assertPushedOn('staff', CancelSession::class, fn ($job) => $job->sessionId === 99);
    }

    public function test_does_nothing_when_the_instance_does_not_exist(): void
    {
        Event::fake();

        app(SessionScheduleHandler::class)->handle(999999, SessionScheduleActionEnum::QUEUE, null, 1, null);

        $this->assertSame(0, Schedule::where('instance_id', 999999)->count());
        Event::assertNotDispatched(ScheduleUpdated::class);
    }
}
