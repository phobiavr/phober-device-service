<?php

namespace Tests\Unit\Services;

use App\Events\ScheduleUpdated;
use App\Models\Instance;
use App\Models\Schedule;
use App\Services\SessionScheduleHandler;
use Illuminate\Support\Facades\Event;
use Phobiavr\PhoberLaravelCommon\Enums\ScheduleEnum;
use Phobiavr\PhoberLaravelCommon\Enums\SessionScheduleActionEnum;
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

    public function test_creates_a_new_in_session_schedule_with_the_given_duration_when_starting_fresh(): void
    {
        Event::fake();
        $instance = Instance::factory()->create();

        app(SessionScheduleHandler::class)->handle($instance->id, SessionScheduleActionEnum::START, 30, 11, null);

        $schedule = Schedule::where('instance_id', $instance->id)->sole();
        $this->assertSame(ScheduleEnum::IN_SESSION->value, $schedule->type);
        $this->assertSame(30.0, $schedule->start->diffInMinutes($schedule->end));
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

    public function test_cancels_an_existing_queue_schedule_without_creating_a_new_one_when_the_action_is_cancel(): void
    {
        Event::fake();
        $instance = Instance::factory()->create();
        $queued = Schedule::factory()->for($instance)->queue()->create();

        app(SessionScheduleHandler::class)->handle($instance->id, SessionScheduleActionEnum::CANCEL, null, null, null);

        $this->assertSame(1, Schedule::where('instance_id', $instance->id)->count());
        $this->assertSame(ScheduleEnum::CANCELED->value, $queued->fresh()->type);

        Event::assertDispatched(ScheduleUpdated::class, fn ($e) => $e->action === 'cancelled');
    }

    public function test_cancels_a_non_queue_active_schedule_for_finish_cancel_actions_without_creating_a_replacement(): void
    {
        Event::fake();
        $instance = Instance::factory()->create();
        $inSession = Schedule::factory()->for($instance)->create();

        app(SessionScheduleHandler::class)->handle($instance->id, SessionScheduleActionEnum::FINISH, null, null, null);

        $this->assertSame(1, Schedule::where('instance_id', $instance->id)->count());
        $this->assertSame(ScheduleEnum::CANCELED->value, $inSession->fresh()->type);
    }

    public function test_cancels_a_queued_schedule_and_creates_a_fresh_queue_schedule_when_re_queued(): void
    {
        // The QUEUE branch inside the `$active->type === QUEUE` block only
        // special-cases START/CANCEL — a QUEUE action falls through, so the
        // existing queued schedule gets cancelled and a brand new one created.
        Event::fake();
        $instance = Instance::factory()->create();
        $queued = Schedule::factory()->for($instance)->queue()->create();

        app(SessionScheduleHandler::class)->handle($instance->id, SessionScheduleActionEnum::QUEUE, null, 99, null);

        $this->assertSame(ScheduleEnum::CANCELED->value, $queued->fresh()->type);

        $fresh = Schedule::where('instance_id', $instance->id)->where('type', ScheduleEnum::QUEUE->value)->sole();
        $this->assertSame(99, $fresh->session_id);
        $this->assertNotSame($queued->id, $fresh->id);
    }

    public function test_does_nothing_when_the_instance_does_not_exist(): void
    {
        Event::fake();

        app(SessionScheduleHandler::class)->handle(999999, SessionScheduleActionEnum::QUEUE, null, 1, null);

        $this->assertSame(0, Schedule::where('instance_id', 999999)->count());
        Event::assertNotDispatched(ScheduleUpdated::class);
    }
}
