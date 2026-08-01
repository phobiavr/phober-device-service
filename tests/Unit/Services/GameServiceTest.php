<?php

namespace Tests\Unit\Services;

use App\Models\Device;
use App\Models\Game;
use App\Models\Genre;
use App\Services\GameService;
use Phobiavr\PhoberLaravelCommon\Pageable\PageableRequest;
use Phobiavr\PhoberLaravelCommon\Testing\ClearsExistingRows;
use Tests\TestCase;

class GameServiceTest extends TestCase
{
    use ClearsExistingRows;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearExistingRows(Game::class, Genre::class, Device::class);
    }

    private function pageableRequest(array $query = []): PageableRequest
    {
        $request = PageableRequest::create('/games/search', 'POST', $query);
        $request->setContainer(app())->setRedirector(app('redirect'));
        $request->validateResolved();

        return $request;
    }

    public function test_filters_games_by_device(): void
    {
        $device = Device::factory()->create();
        $matching = Game::factory()->create();
        // devices() is a belongsToMany keyed by devices.type (not devices.id) —
        // attach() takes the related key value directly, i.e. the type string.
        $matching->devices()->attach($device->type);
        Game::factory()->create();

        $results = app(GameService::class)->search($this->pageableRequest(), ['device' => $device->type]);

        $this->assertSame([$matching->id], collect($results->items())->pluck('id')->all());
    }

    public function test_filters_games_by_genre_slug(): void
    {
        $genre = Genre::factory()->create();
        $matching = Game::factory()->create();
        $matching->genres()->attach($genre->id);
        Game::factory()->create();

        $results = app(GameService::class)->search($this->pageableRequest(), ['genre' => $genre->slug]);

        $this->assertSame([$matching->id], collect($results->items())->pluck('id')->all());
    }

    public function test_treats_an_explicit_multiplayer_false_filter_as_a_no_op(): void
    {
        Game::factory()->create(['multiplayer' => true]);
        Game::factory()->create(['multiplayer' => false]);

        $results = app(GameService::class)->search($this->pageableRequest(), ['multiplayer' => false]);

        $this->assertSame(2, $results->total());
    }

    public function test_filters_games_by_exact_rating(): void
    {
        Game::factory()->create(['rating' => 5]);
        Game::factory()->create(['rating' => 3]);

        $results = app(GameService::class)->search($this->pageableRequest(), ['rating' => 5]);

        $this->assertSame(1, $results->total());
    }
}
