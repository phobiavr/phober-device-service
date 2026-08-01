<?php

namespace Tests\Feature;

use App\Models\Instance;
use Phobiavr\PhoberLaravelCommon\Testing\ClearsExistingRows;
use Tests\TestCase;

class InstanceEndpointsTest extends TestCase
{
    use ClearsExistingRows;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearExistingRows(Instance::class);
    }

    public function test_lists_instances_sorted_by_id(): void
    {
        Instance::factory()->count(3)->create();

        $this->getJson('/instances')->assertOk()->assertJsonCount(3);
    }

    public function test_shows_an_instance_by_id_including_its_label(): void
    {
        $instance = Instance::factory()->create(['device' => 'HTC']);

        $this->getJson("/instance/{$instance->id}")
            ->assertOk()
            ->assertJsonPath('label', 'HTC - 1')
            ->assertJsonPath('id', $instance->id);
    }

    public function test_shows_an_instance_by_mac_address(): void
    {
        $instance = Instance::factory()->create(['mac_address' => 'AA:BB:CC:DD:EE:01']);

        $this->getJson('/instance/AA:BB:CC:DD:EE:01')->assertOk()->assertJsonPath('id', $instance->id);
    }

    public function test_returns_404_for_an_unknown_instance(): void
    {
        $this->getJson('/instance/999999')->assertStatus(404);
    }
}
