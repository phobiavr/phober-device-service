<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Device>
 */
class DeviceFactory extends Factory
{
    protected $model = Device::class;

    public function definition(): array
    {
        $type = 'test-device-'.$this->faker->unique()->numberBetween(1, 1000000);

        return [
            'name' => $type,
            'type' => $type,
            'slug' => $type,
            'description' => null,
        ];
    }
}
