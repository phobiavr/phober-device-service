<?php

namespace Database\Factories;

use App\Models\Instance;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Phobiavr\PhoberLaravelCommon\Enums\ScheduleEnum;

/**
 * @extends Factory<Schedule>
 */
class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    public function definition(): array
    {
        return [
            'instance_id' => Instance::factory(),
            'type' => ScheduleEnum::IN_SESSION->value,
            'start' => now()->subMinutes(5),
            'end' => now()->addMinutes(25),
            'session_id' => null,
        ];
    }

    public function queue(): static
    {
        return $this->state(fn () => ['type' => ScheduleEnum::QUEUE->value, 'start' => null, 'end' => null]);
    }

    public function canceled(): static
    {
        return $this->state(fn () => ['type' => ScheduleEnum::CANCELED->value]);
    }

    public function upcoming(): static
    {
        return $this->state(fn () => [
            'type' => ScheduleEnum::RESERVATION->value,
            'start' => now()->addMinutes(10),
            'end' => now()->addMinutes(40),
        ]);
    }

    public function openEnded(): static
    {
        return $this->state(fn () => ['start' => now()->subMinutes(5), 'end' => null]);
    }
}
