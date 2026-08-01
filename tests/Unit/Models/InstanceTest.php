<?php

namespace Tests\Unit\Models;

use App\Models\Instance;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Phobiavr\PhoberLaravelCommon\Enums\ScheduleEnum;
use Phobiavr\PhoberLaravelCommon\Testing\ClearsExistingRows;
use Tests\TestCase;

class InstanceTest extends TestCase
{
    use ClearsExistingRows;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearExistingRows(Instance::class);
    }

    public function test_returns_the_soonest_ending_active_schedule(): void
    {
        $instance = Instance::factory()->create();

        Schedule::factory()->for($instance)->create(['type' => ScheduleEnum::IN_SESSION->value, 'start' => now()->subMinutes(10), 'end' => now()->addMinutes(30)]);
        $soonest = Schedule::factory()->for($instance)->create(['type' => ScheduleEnum::IN_SESSION->value, 'start' => now()->subMinutes(5), 'end' => now()->addMinutes(5)]);
        Schedule::factory()->for($instance)->canceled()->create(['start' => now()->subMinute(), 'end' => now()->addMinute()]);

        $this->assertSame($soonest->id, $instance->fresh()->getActiveSchedule()->id);
    }

    public function test_returns_null_when_no_schedule_is_active(): void
    {
        $instance = Instance::factory()->create();
        Schedule::factory()->for($instance)->create(['start' => now()->addHour(), 'end' => now()->addHours(2)]);

        $this->assertNull($instance->fresh()->getActiveSchedule());
    }

    public function test_finds_the_schedule_starting_within_the_next_15_minutes_as_upcoming(): void
    {
        $instance = Instance::factory()->create();

        $upcoming = Schedule::factory()->for($instance)->create(['type' => ScheduleEnum::RESERVATION->value, 'start' => now()->addMinutes(10), 'end' => now()->addMinutes(40)]);
        Schedule::factory()->for($instance)->create(['type' => ScheduleEnum::RESERVATION->value, 'start' => now()->addMinutes(20), 'end' => now()->addMinutes(50)]);

        $this->assertSame($upcoming->id, $instance->fresh()->getUpcomingSchedule()->id);
    }

    public function test_ignores_upcoming_schedules_further_than_15_minutes_away_or_already_canceled(): void
    {
        $instance = Instance::factory()->create();

        Schedule::factory()->for($instance)->create(['start' => now()->addMinutes(30), 'end' => now()->addMinutes(60)]);
        Schedule::factory()->for($instance)->create(['type' => ScheduleEnum::CANCELED->value, 'start' => now()->addMinutes(5), 'end' => now()->addMinutes(20)]);

        $this->assertNull($instance->fresh()->getUpcomingSchedule());
    }

    public function test_finds_an_instance_by_numeric_id_or_by_mac_address(): void
    {
        $instance = Instance::factory()->create(['mac_address' => 'AA:BB:CC:DD:EE:FF']);

        $this->assertSame($instance->id, Instance::findByIdOrMacAddressOrFail($instance->id)->id);
        $this->assertSame($instance->id, Instance::findByIdOrMacAddressOrFail('AA:BB:CC:DD:EE:FF')->id);

        $this->expectException(ModelNotFoundException::class);
        Instance::findByIdOrMacAddressOrFail('00:00:00:00:00:00');
    }

    public function test_labels_an_instance_with_its_1_based_ordinal_position_among_same_device_instances(): void
    {
        $first = Instance::factory()->create(['device' => 'HTC']);
        $second = Instance::factory()->create(['device' => 'HTC']);
        Instance::factory()->create(['device' => 'OCULUS']);

        $this->assertSame('HTC - 1', $first->fresh()->label);
        $this->assertSame('HTC - 2', $second->fresh()->label);
    }
}
