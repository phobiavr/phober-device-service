<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Post;
use Phobiavr\PhoberLaravelCommon\Testing\ClearsExistingRows;
use Tests\TestCase;

class CatalogEndpointsTest extends TestCase
{
    use ClearsExistingRows;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearExistingRows(Game::class, Genre::class, Device::class, Post::class);
    }

    public function test_paginates_games(): void
    {
        Game::factory()->count(3)->create();

        $this->getJson('/games')
            ->assertOk()
            ->assertJsonStructure(['data', 'total', 'size', 'current_page', 'total_pages'])
            ->assertJsonPath('total', 3);
    }

    public function test_searches_games_by_genre_slug(): void
    {
        $genre = Genre::factory()->create();
        $matching = Game::factory()->create();
        $matching->genres()->attach($genre->id);
        Game::factory()->create();

        $this->postJson('/games/search', ['genre' => $genre->slug])
            ->assertOk()
            ->assertJsonPath('total', 1);
    }

    public function test_shows_a_single_game(): void
    {
        $game = Game::factory()->create();

        $this->getJson("/games/{$game->id}")->assertOk()->assertJsonPath('id', $game->id);
    }

    public function test_returns_404_for_a_missing_game(): void
    {
        $this->getJson('/games/999999')->assertStatus(404);
    }

    public function test_paginates_posts(): void
    {
        Post::factory()->count(2)->create();

        $this->getJson('/posts')->assertOk()->assertJsonPath('total', 2);
    }

    public function test_lists_genres_as_a_plain_array(): void
    {
        Genre::factory()->count(2)->create();

        $this->getJson('/genres')->assertOk()->assertJsonCount(2);
    }

    public function test_lists_devices_as_a_plain_array(): void
    {
        Device::factory()->count(2)->create();

        $this->getJson('/devices')->assertOk()->assertJsonCount(2);
    }
}
