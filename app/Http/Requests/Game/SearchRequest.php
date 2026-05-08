<?php

namespace App\Http\Requests\Game;

use Phobiavr\PhoberLaravelCommon\Pageable\PageableRequest;

class SearchRequest extends PageableRequest {
    public function rules(): array {
        return [
            'device'      => 'nullable|string',
            'genre'       => 'nullable|string',
            'multiplayer' => 'nullable|boolean',
            'rating'      => 'nullable|numeric',
        ];
    }

    public function filters(): array {
        return [
            'device'      => $this->input('device'),
            'genre'       => $this->input('genre'),
            'multiplayer' => $this->boolean('multiplayer'),
            'rating'      => $this->input('rating'),
        ];
    }
}
