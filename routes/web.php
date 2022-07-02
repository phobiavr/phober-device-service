<?php

use App\Http\Requests\Pageable;
use App\Http\Resources\GameResource;
use App\Http\Resources\PaginationCollection;
use App\Models\Device;
use App\Models\Game;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;


Route::middleware('auth.server')->get('/', function () {
  return Auth::guard('server')->user();
});

Route::get('/games', function (Pageable $request){
  $list = Game::query()->paginateFromRequest($request);

  $response = new PaginationCollection($list, GameResource::class);

  return Response::json($response->jsonSerialize());
});

Route::get('/games/{model}', function (Game $model){
  return Response::json($model);
});

Route::get('/genres', function (Request $request){
  $response = Genre::all();

  return Response::json($response);
});

Route::get('/devices', function (Request $request){
  $response = Device::all();

  return Response::json($response);
});

Route::get('/games-search/by-device/{device}', function (Pageable $request, string $device){
  $list = Game::query()
    ->whereRelation('devices', 'slug', $device)
    ->paginateFromRequest($request);

  $response = new PaginationCollection($list, GameResource::class);

  return Response::json($response->jsonSerialize());
});

Route::get('/games-search/by-genre/{genre}', function (Pageable $request, string $genre){
  $list = Game::query()
    ->whereRelation('genres', 'slug', $genre)
    ->paginateFromRequest($request);

  $response = new PaginationCollection($list, GameResource::class);

  return Response::json($response->jsonSerialize());
});

Route::get('/games-search/by-rating/{rating}', function (Pageable $request, int $rating){
  $list = Game::query()
    ->where('rating', '=', $rating)
    ->paginateFromRequest($request);

  $response = new PaginationCollection($list, GameResource::class);

  return Response::json($response->jsonSerialize());
});

Route::get('/games-search/multiplayer', function (Pageable $request){
  $list = Game::query()
    ->where('multiplayer', '=', true)
    ->paginateFromRequest($request);

  $response = new PaginationCollection($list, GameResource::class);

  return Response::json($response->jsonSerialize());
});
