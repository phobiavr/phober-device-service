<?php

namespace Tests\Feature;

use App\Models\Instance;
use App\Models\TariffPlan;
use Phobiavr\PhoberLaravelCommon\Enums\DeviceEnum;
use Phobiavr\PhoberLaravelCommon\Enums\SessionTariffEnum;
use Phobiavr\PhoberLaravelCommon\Enums\SessionTimeEnum;
use Phobiavr\PhoberLaravelCommon\Testing\ClearsExistingRows;
use Tests\TestCase;

class TariffPlanEndpointsTest extends TestCase
{
    use ClearsExistingRows;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearExistingRows(Instance::class, TariffPlan::class);
    }

    public function test_lists_tariff_plans(): void
    {
        TariffPlan::factory()->count(2)->create();

        $this->getJson('/tariff-plans')->assertOk()->assertJsonCount(2);
    }

    public function test_prices_a_session_for_an_instance(): void
    {
        $instance = Instance::factory()->create(['device' => DeviceEnum::HTC->value]);
        TariffPlan::factory()->create([
            'device' => DeviceEnum::HTC->value,
            'tariff' => SessionTariffEnum::MORNING->value,
            'time' => SessionTimeEnum::MIN_30->value,
            'price' => 45,
        ]);

        $this->postJson('/price', [
            'instance_id' => $instance->id,
            'tariff' => SessionTariffEnum::MORNING->value,
            'time' => SessionTimeEnum::MIN_30->value,
        ])->assertOk()->assertJsonPath('price', 45);
    }

    public function test_returns_404_when_no_tariff_plan_matches(): void
    {
        $instance = Instance::factory()->create(['device' => DeviceEnum::HTC->value]);

        $this->postJson('/price', [
            'instance_id' => $instance->id,
            'tariff' => SessionTariffEnum::EXTRA->value,
            'time' => SessionTimeEnum::MIN_60->value,
        ])->assertStatus(404)->assertJsonPath('message', 'Tariff plan not found.');
    }

    public function test_requires_either_instance_id_or_device(): void
    {
        $this->postJson('/price', [
            'tariff' => SessionTariffEnum::MORNING->value,
            'time' => SessionTimeEnum::MIN_30->value,
        ])->assertStatus(422)->assertJsonValidationErrors(['instance_id', 'device']);
    }
}
