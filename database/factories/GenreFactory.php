<?php

namespace Database\Factories;

use App\Models\Genre;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Genre>
 */
class GenreFactory extends Factory
{
    protected $model = Genre::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->word();

        return [
            'name' => $name,
            'slug' => strtolower($name),
        ];
    }
}
