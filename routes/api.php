<?php

use App\Http\Resources\GameResource;
use App\Models\Device;
use App\Models\Game;
use App\Models\Genre;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Shared\Pageable\PageableCollection;
use Shared\Pageable\PageableRequest;

Route::middleware('auth.server')->get('', function () {
    return Auth::guard('server')->user();
});

Route::get('/games', function (PageableRequest $request) {
    $list = Game::paginateFromRequest($request);

    $response = new PageableCollection($list, GameResource::class);

    return Response::json($response->jsonSerialize());
});

Route::post('/games/search', function (PageableRequest $request) {
    $query = Game::query();

    if ($device = $request->get('device')) {
        $query->whereRelation('devices', 'slug', $device);
    }

    if ($genre = $request->get('genre')) {
        $query->whereRelation('genres', 'slug', $genre);
    }

    if ($request->get('multiplayer')) {
        $query->where('multiplayer', '=', true);
    }

    if ($rating = $request->get('rating')) {
        $query->where('rating', '=', $rating);
    }

    $list = $query->paginateFromRequest($request);


    return Response::json(new PageableCollection($list, GameResource::class));
});

Route::get('/games/{id}', function (int $id) {
    $game = Game::findOrFail($id);

    return GameResource::make($game);
});

Route::get('/genres', function () {
    $response = Genre::all();

    return Response::json($response);
});

Route::get('/devices', function () {
    $response = Device::all();

    return Response::json($response);
});
