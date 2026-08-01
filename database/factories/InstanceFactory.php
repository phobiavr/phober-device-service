<?php

namespace Database\Factories;

use App\Models\Instance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Phobiavr\PhoberLaravelCommon\Enums\DeviceEnum;

/**
 * @extends Factory<Instance>
 */
class InstanceFactory extends Factory
{
    protected $model = Instance::class;

    public function definition(): array
    {
        return [
            'mac_address' => $this->faker->unique()->macAddress(),
            'device' => $this->faker->randomElement(array_column(DeviceEnum::cases(), 'value')),
            'active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['active' => false]);
    }
}
