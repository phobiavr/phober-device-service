<?php

namespace App\Services;

use App\Models\Game;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Phobiavr\PhoberLaravelCommon\Pageable\PageableRequest;

class GameService {
    /** @return LengthAwarePaginator<int, Game> */
    public function paginate(PageableRequest $request): LengthAwarePaginator {
        return Game::paginateFromRequest($request);
    }

    /**
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, Game>
     */
    public function search(PageableRequest $request, array $filters): LengthAwarePaginator {
        $query = Game::query();

        if (!empty($filters['device'])) {
            $query->whereRelation('devices', 'type', $filters['device']);
        }

        if (!empty($filters['genre'])) {
            $query->whereRelation('genres', 'slug', $filters['genre']);
        }

        if (!empty($filters['multiplayer'])) {
            $query->where('multiplayer', true);
        }

        if (!empty($filters['rating'])) {
            $query->where('rating', $filters['rating']);
        }

        return $query->paginateFromRequest($request);
    }

    public function find(int $id): Game {
        return Game::findOrFail($id);
    }
}
