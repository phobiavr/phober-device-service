<?php

namespace Tests\Unit\Models;

use App\Models\Instance;
use App\Models\Schedule;
use Phobiavr\PhoberLaravelCommon\Testing\ClearsExistingRows;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    use ClearsExistingRows;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearExistingRows(Instance::class);
    }

    public function test_is_active_when_both_start_and_end_are_open_ended(): void
    {
        $schedule = Schedule::factory()->create(['start' => null, 'end' => null]);
        $this->assertTrue($schedule->isActive());
    }

    public function test_is_active_when_only_the_end_is_in_the_future_and_start_is_open(): void
    {
        $schedule = Schedule::factory()->create(['start' => null, 'end' => now()->addMinutes(10)]);
        $this->assertTrue($schedule->isActive());
    }

    public function test_is_active_when_start_has_passed_and_end_is_open_ended(): void
    {
        $schedule = Schedule::factory()->create(['start' => now()->subMinutes(5), 'end' => null]);
        $this->assertTrue($schedule->isActive());
    }

    public function test_is_active_when_now_falls_within_a_bounded_start_end_window(): void
    {
        $schedule = Schedule::factory()->create(['start' => now()->subMinutes(5), 'end' => now()->addMinutes(5)]);
        $this->assertTrue($schedule->isActive());
    }

    public function test_is_not_active_once_the_end_has_passed(): void
    {
        $schedule = Schedule::factory()->create(['start' => now()->subMinutes(10), 'end' => now()->subMinute()]);
        $this->assertFalse($schedule->isActive());
    }

    public function test_is_not_active_before_its_start_time_even_with_an_open_ended_end(): void
    {
        $schedule = Schedule::factory()->create(['start' => now()->addMinutes(5), 'end' => null]);
        $this->assertFalse($schedule->isActive());
    }

    public function test_is_never_active_once_canceled_regardless_of_the_time_window(): void
    {
        $schedule = Schedule::factory()->canceled()->create(['start' => null, 'end' => null]);
        $this->assertFalse($schedule->isActive());
    }
}
