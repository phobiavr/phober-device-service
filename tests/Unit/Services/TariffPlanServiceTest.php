<?php

namespace Tests\Unit\Services;

use App\Models\Instance;
use App\Models\TariffPlan;
use App\Services\TariffPlanService;
use Phobiavr\PhoberLaravelCommon\Data\PricePayload;
use Phobiavr\PhoberLaravelCommon\Enums\DeviceEnum;
use Phobiavr\PhoberLaravelCommon\Enums\SessionTariffEnum;
use Phobiavr\PhoberLaravelCommon\Enums\SessionTimeEnum;
use Phobiavr\PhoberLaravelCommon\Testing\ClearsExistingRows;
use Tests\TestCase;

class TariffPlanServiceTest extends TestCase
{
    use ClearsExistingRows;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearExistingRows(Instance::class, TariffPlan::class);
    }

    public function test_finds_a_plan_by_explicit_device(): void
    {
        TariffPlan::factory()->create([
            'device' => DeviceEnum::OCULUS->value,
            'tariff' => SessionTariffEnum::EVENING->value,
            'time' => SessionTimeEnum::MIN_60->value,
            'price' => 77,
        ]);

        $payload = PricePayload::forDevice(DeviceEnum::OCULUS, SessionTariffEnum::EVENING, SessionTimeEnum::MIN_60);

        $this->assertEquals(77, app(TariffPlanService::class)->find($payload)->price);
    }

    public function test_resolves_the_device_from_the_instance_when_only_instance_id_is_given(): void
    {
        $instance = Instance::factory()->create(['device' => DeviceEnum::HTC->value]);
        TariffPlan::factory()->create([
            'device' => DeviceEnum::HTC->value,
            'tariff' => SessionTariffEnum::MORNING->value,
            'time' => SessionTimeEnum::MIN_15->value,
            'price' => 20,
        ]);

        $payload = PricePayload::forInstance($instance->id, SessionTariffEnum::MORNING, SessionTimeEnum::MIN_15);

        $this->assertEquals(20, app(TariffPlanService::class)->find($payload)->price);
    }

    public function test_returns_null_when_no_matching_plan_exists(): void
    {
        $payload = PricePayload::forDevice(DeviceEnum::DOF_3, SessionTariffEnum::EXTRA, SessionTimeEnum::MIN_60);

        $this->assertNull(app(TariffPlanService::class)->find($payload));
    }
}
