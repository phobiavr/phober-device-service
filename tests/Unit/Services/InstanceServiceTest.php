<?php

namespace Tests\Unit\Services;

use App\Models\Instance;
use App\Models\Schedule;
use App\Services\InstanceService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Group;
use Phobiavr\PhoberLaravelCommon\Testing\ClearsExistingRows;
use Tests\TestCase;

class InstanceServiceTest extends TestCase
{
    use ClearsExistingRows;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearExistingRows(Instance::class);
    }

    public function test_enriches_the_instance_with_session_info_from_staff_service_when_a_schedule_is_active(): void
    {
        $instance = Instance::factory()->create();
        Schedule::factory()->for($instance)->create(['session_id' => 77]);

        Http::fake(['http://staff-service/sessions/77' => Http::response(['id' => 77, 'customer' => 'Jane'])]);

        $result = app(InstanceService::class)->findWithSession((string) $instance->id);

        $this->assertSame(['id' => 77, 'customer' => 'Jane'], $result->session);
    }

    #[Group('slow')]
    public function test_does_not_fail_when_staff_service_is_unreachable(): void
    {
        $instance = Instance::factory()->create();
        Schedule::factory()->for($instance)->create(['session_id' => 77]);

        Http::fake(['http://staff-service/*' => fn () => throw new ConnectionException('refused')]);

        $result = app(InstanceService::class)->findWithSession((string) $instance->id);

        $this->assertSame($instance->id, $result->id);
    }

    public function test_leaves_the_session_property_unset_when_there_is_no_active_schedule(): void
    {
        $instance = Instance::factory()->create();

        $result = app(InstanceService::class)->findWithSession((string) $instance->id);

        $this->assertFalse(isset($result->session));
    }
}
