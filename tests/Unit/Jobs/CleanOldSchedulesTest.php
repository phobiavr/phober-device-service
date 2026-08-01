<?php

namespace Tests\Unit\Jobs;

use App\Jobs\CleanOldSchedules;
use App\Models\Instance;
use App\Models\Schedule;
use Phobiavr\PhoberLaravelCommon\Enums\ScheduleEnum;
use Phobiavr\PhoberLaravelCommon\Testing\ClearsExistingRows;
use Tests\TestCase;

class CleanOldSchedulesTest extends TestCase
{
    use ClearsExistingRows;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearExistingRows(Instance::class);
    }

    public function test_deletes_canceled_schedules_regardless_of_their_time_window(): void
    {
        $instance = Instance::factory()->create();
        $canceled = Schedule::factory()->for($instance)->canceled()->create(['start' => now()->addHour(), 'end' => now()->addHours(2)]);

        (new CleanOldSchedules())->handle();

        $this->assertNull(Schedule::find($canceled->id));
    }

    public function test_deletes_any_schedule_whose_end_has_passed_even_if_not_canceled(): void
    {
        // `Schedule::where('type', CANCELED)->orWhere('end', '<', now())` is an
        // ungrouped orWhere — it deletes ANY schedule with a past `end`,
        // regardless of type, not just canceled ones whose end has passed.
        $instance = Instance::factory()->create();
        $expired = Schedule::factory()->for($instance)->create(['type' => ScheduleEnum::IN_SESSION->value, 'start' => now()->subHours(2), 'end' => now()->subHour()]);

        (new CleanOldSchedules())->handle();

        $this->assertNull(Schedule::find($expired->id));
    }

    public function test_keeps_active_non_canceled_schedules_with_no_end_or_a_future_end(): void
    {
        $instance = Instance::factory()->create();
        $ongoing = Schedule::factory()->for($instance)->create(['type' => ScheduleEnum::IN_SESSION->value, 'start' => now()->subMinutes(5), 'end' => now()->addMinutes(25)]);
        $openEnded = Schedule::factory()->for($instance)->openEnded()->create(['type' => ScheduleEnum::QUEUE->value]);

        (new CleanOldSchedules())->handle();

        $this->assertNotNull(Schedule::find($ongoing->id));
        $this->assertNotNull(Schedule::find($openEnded->id));
    }
}
