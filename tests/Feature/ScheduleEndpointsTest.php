<?php

namespace Tests\Feature;

use App\Models\Instance;
use App\Models\Schedule;
use Illuminate\Support\Str;
use Phobiavr\PhoberLaravelCommon\Enums\ScheduleEnum;
use Phobiavr\PhoberLaravelCommon\Testing\ClearsExistingRows;
use Tests\TestCase;

class ScheduleEndpointsTest extends TestCase
{
    use ClearsExistingRows;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearExistingRows(Instance::class);
    }

    private function privateHeaders(): array
    {
        return ['X-Service-Secret' => config('service.secret')];
    }

    public function test_rejects_the_private_schedule_routes_without_the_service_secret(): void
    {
        $this->postJson('/schedule', [])->assertStatus(401);
    }

    public function test_creates_a_schedule_for_an_active_instance_with_no_conflicts(): void
    {
        $instance = Instance::factory()->create();

        $this->withHeaders($this->privateHeaders())->postJson('/schedule', [
            'type' => ScheduleEnum::RESERVATION->value,
            'instance_id' => $instance->id,
            'start' => now()->addHour()->format('Y-m-d H:i:s'),
            'end' => now()->addHours(2)->format('Y-m-d H:i:s'),
        ])->assertOk()->assertJsonPath('type', ScheduleEnum::RESERVATION->value);
    }

    public function test_rejects_a_schedule_for_an_inactive_instance(): void
    {
        $instance = Instance::factory()->inactive()->create();

        $this->withHeaders($this->privateHeaders())->postJson('/schedule', [
            'type' => ScheduleEnum::RESERVATION->value,
            'instance_id' => $instance->id,
            'start' => now()->addHour()->format('Y-m-d H:i:s'),
        ])->assertStatus(422)->assertJsonValidationErrors(['instance_id']);
    }

    public function test_rejects_a_schedule_that_conflicts_with_an_existing_one(): void
    {
        $instance = Instance::factory()->create();
        Schedule::factory()->for($instance)->create(['start' => now()->addHour(), 'end' => now()->addHours(3)]);

        $this->withHeaders($this->privateHeaders())->postJson('/schedule', [
            'type' => ScheduleEnum::RESERVATION->value,
            'instance_id' => $instance->id,
            'start' => now()->addHours(2)->format('Y-m-d H:i:s'),
            'end' => now()->addHours(4)->format('Y-m-d H:i:s'),
        ])->assertStatus(422)->assertJsonValidationErrors(['start']);
    }

    public function test_replays_the_same_response_for_a_repeated_idempotency_key_on_schedule_creation(): void
    {
        $instance = Instance::factory()->create();
        $key = (string) Str::uuid();
        $payload = [
            'type' => ScheduleEnum::RESERVATION->value,
            'instance_id' => $instance->id,
            'start' => now()->addHour()->format('Y-m-d H:i:s'),
        ];

        $this->withHeaders(array_merge($this->privateHeaders(), ['Idempotency-Key' => $key]))
            ->postJson('/schedule', $payload)->assertOk();

        $this->withHeaders(array_merge($this->privateHeaders(), ['Idempotency-Key' => $key]))
            ->postJson('/schedule', $payload)
            ->assertOk()
            ->assertHeader('Idempotency-Replayed', 'true');

        $this->assertSame(1, Schedule::where('instance_id', $instance->id)->count());
    }

    public function test_returns_the_active_schedule_for_an_instance_by_numeric_id(): void
    {
        $instance = Instance::factory()->create();
        Schedule::factory()->for($instance)->create(['type' => ScheduleEnum::IN_SESSION->value]);

        $this->withHeaders($this->privateHeaders())->getJson("/schedule/{$instance->id}")
            ->assertOk()
            ->assertJsonPath('type', ScheduleEnum::IN_SESSION->value);
    }

    public function test_cancels_a_schedule(): void
    {
        $schedule = Schedule::factory()->create();

        $this->withHeaders($this->privateHeaders())->deleteJson("/schedule/{$schedule->id}")->assertNoContent();

        $this->assertSame(ScheduleEnum::CANCELED->value, $schedule->fresh()->type);
    }

    public function test_returns_the_active_schedule_for_an_instance_by_mac_address_behind_the_overlay_secret(): void
    {
        $instance = Instance::factory()->create(['mac_address' => 'AA:BB:CC:DD:EE:02']);
        Schedule::factory()->for($instance)->create();

        $this->withHeaders(['X-Overlay-Secret' => config('service.overlay_secret')])
            ->getJson('/schedule/AA:BB:CC:DD:EE:02')
            ->assertOk();
    }

    public function test_rejects_the_overlay_mac_lookup_without_the_overlay_secret(): void
    {
        Instance::factory()->create(['mac_address' => 'AA:BB:CC:DD:EE:03']);

        $this->getJson('/schedule/AA:BB:CC:DD:EE:03')->assertStatus(401);
    }
}
