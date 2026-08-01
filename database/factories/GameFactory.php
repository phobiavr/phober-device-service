<?php

namespace Database\Factories;

use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
{
    protected $model = Game::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)),
            'video' => null,
            'description' => ['en' => $this->faker->sentence()],
            'rating' => $this->faker->numberBetween(0, 5),
            'multiplayer' => false,
        ];
    }
}
