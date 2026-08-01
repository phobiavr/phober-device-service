<?php

namespace Tests\Unit\Services;

use App\Events\ScheduleUpdated;
use App\Models\Instance;
use App\Models\Schedule;
use App\Services\ScheduleService;
use DateTime;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Event;
use Phobiavr\PhoberLaravelCommon\Data\SchedulePayload;
use Phobiavr\PhoberLaravelCommon\Enums\ScheduleEnum;
use Phobiavr\PhoberLaravelCommon\Testing\ClearsExistingRows;
use Tests\TestCase;

class ScheduleServiceTest extends TestCase
{
    use ClearsExistingRows;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearExistingRows(Instance::class);
    }

    public function test_creates_a_schedule_from_a_payload_and_dispatches_schedule_updated(): void
    {
        Event::fake();
        $instance = Instance::factory()->create();

        $payload = new SchedulePayload(ScheduleEnum::RESERVATION, $instance->id, new DateTime('+1 hour'), new DateTime('+2 hours'));

        $schedule = app(ScheduleService::class)->create($payload);

        $this->assertTrue($schedule->exists);
        $this->assertSame(ScheduleEnum::RESERVATION->value, $schedule->type);
        $this->assertSame($instance->id, $schedule->instance_id);

        Event::assertDispatched(ScheduleUpdated::class, fn ($e) => $e->action === 'created' && $e->schedule->is($schedule));
    }

    public function test_save_reuses_a_given_schedule_instead_of_creating_a_new_one(): void
    {
        $instance = Instance::factory()->create();
        $existing = Schedule::factory()->for($instance)->queue()->create();

        $updated = app(ScheduleService::class)->save(ScheduleEnum::IN_SESSION, $instance->id, 30, $existing);

        $this->assertSame($existing->id, $updated->id);
        $this->assertSame(ScheduleEnum::IN_SESSION->value, $updated->type);
        $this->assertSame(30.0, $updated->start->diffInMinutes($updated->end));
    }

    public function test_save_without_minutes_leaves_the_schedule_open_ended(): void
    {
        $instance = Instance::factory()->create();

        $schedule = app(ScheduleService::class)->save(ScheduleEnum::QUEUE, $instance->id);

        $this->assertNull($schedule->end);
    }

    public function test_save_only_sets_session_id_when_one_is_given(): void
    {
        $instance = Instance::factory()->create();

        $schedule = app(ScheduleService::class)->save(ScheduleEnum::QUEUE, $instance->id, sessionId: 42);

        $this->assertSame(42, $schedule->session_id);
    }

    public function test_active_for_instance_by_id_returns_the_current_active_schedule(): void
    {
        $instance = Instance::factory()->create();
        $active = Schedule::factory()->for($instance)->create();

        $this->assertSame($active->id, app(ScheduleService::class)->activeForInstanceById($instance->id)->id);
    }

    public function test_active_for_instance_by_mac_looks_the_instance_up_by_mac_address(): void
    {
        $instance = Instance::factory()->create(['mac_address' => '11:22:33:44:55:66']);
        $active = Schedule::factory()->for($instance)->create();

        $this->assertSame($active->id, app(ScheduleService::class)->activeForInstanceByMac('11:22:33:44:55:66')->id);
    }

    public function test_cancel_marks_a_schedule_canceled_and_refuses_an_already_canceled_one(): void
    {
        $schedule = Schedule::factory()->create();

        $canceled = app(ScheduleService::class)->cancel($schedule->id);
        $this->assertSame(ScheduleEnum::CANCELED->value, $canceled->fresh()->type);

        $this->expectException(ModelNotFoundException::class);
        app(ScheduleService::class)->cancel($schedule->id);
    }
}
