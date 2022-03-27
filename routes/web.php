<?php

use App\Http\Requests\Pageable;
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
  $response = PaginationCollection::make(Game::query()->paginateFromRequest($request))->jsonSerialize();

  return Response::json($response);
});

Route::get('/genres', function (Request $request){
  $response = Genre::all();

  return Response::json($response);
});

Route::get('/devices', function (Request $request){
  $response = Device::all();

  return Response::json($response);
});

Route::get('/gamesByDevice/{device}', function (Pageable $request, string $device){
  $response = PaginationCollection::make(
    Game::query()
      ->whereRelation('devices', 'slug', $device)
      ->paginateFromRequest($request)
  )->jsonSerialize();

  return Response::json($response);
});

Route::get('/gamesByGenre/{genre}', function (Pageable $request, string $genre){
  $response = PaginationCollection::make(
    Game::query()
      ->whereRelation('genres', 'slug', $genre)
      ->paginateFromRequest($request)
  )->jsonSerialize();

  return Response::json($response);
});
