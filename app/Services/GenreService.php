<?php

namespace App\Services;

use App\Models\Genre;
use Illuminate\Database\Eloquent\Collection;

class GenreService {
    /** @return Collection<int, Genre> */
    public function all(): Collection {
        return Genre::all();
    }
}
