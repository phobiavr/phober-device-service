<?php

namespace Tests\Unit\Jobs;

use App\Events\Broadcast\ScheduleUpdatedPrivate;
use App\Events\Broadcast\ScheduleUpdatedPublic;
use App\Jobs\NotifyUpcomingSchedules;
use App\Models\Instance;
use App\Models\Schedule;
use Illuminate\Support\Facades\Event;
use Phobiavr\PhoberLaravelCommon\Enums\ScheduleEnum;
use Phobiavr\PhoberLaravelCommon\Testing\ClearsExistingRows;
use Tests\TestCase;

class NotifyUpcomingSchedulesTest extends TestCase
{
    use ClearsExistingRows;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearExistingRows(Instance::class);
    }

    public function test_broadcasts_for_schedules_starting_within_the_next_15_minutes(): void
    {
        Event::fake();
        $instance = Instance::factory()->create();
        Schedule::factory()->for($instance)->create(['type' => ScheduleEnum::RESERVATION->value, 'start' => now()->addMinutes(10), 'end' => now()->addMinutes(40)]);

        (new NotifyUpcomingSchedules())->handle();

        Event::assertDispatched(ScheduleUpdatedPrivate::class);
        Event::assertDispatched(ScheduleUpdatedPublic::class);
    }

    public function test_ignores_schedules_further_than_15_minutes_out_or_already_canceled(): void
    {
        Event::fake();
        $instance = Instance::factory()->create();
        Schedule::factory()->for($instance)->create(['start' => now()->addMinutes(30), 'end' => now()->addMinutes(60)]);
        Schedule::factory()->for($instance)->create(['type' => ScheduleEnum::CANCELED->value, 'start' => now()->addMinutes(5), 'end' => now()->addMinutes(20)]);

        (new NotifyUpcomingSchedules())->handle();

        Event::assertNotDispatched(ScheduleUpdatedPrivate::class);
    }
}
