<?php

namespace Database\Factories;

use App\Models\TariffPlan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Phobiavr\PhoberLaravelCommon\Enums\DeviceEnum;
use Phobiavr\PhoberLaravelCommon\Enums\SessionTariffEnum;
use Phobiavr\PhoberLaravelCommon\Enums\SessionTimeEnum;

/**
 * @extends Factory<TariffPlan>
 */
class TariffPlanFactory extends Factory
{
    protected $model = TariffPlan::class;

    public function definition(): array
    {
        return [
            'tariff' => SessionTariffEnum::MORNING->value,
            'time' => SessionTimeEnum::MIN_30->value,
            'price' => $this->faker->randomFloat(2, 10, 100),
            'device' => DeviceEnum::HTC->value,
        ];
    }
}
