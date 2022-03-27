<?php

use App\Http\Requests\Pageable;
use App\Http\Resources\PaginationCollection;
use App\Models\Game;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware('auth.server')->get('/', function () {
  return Auth::guard('server')->user();
});

Route::get('/games', function (Pageable $request){
  $response = PaginationCollection::make(Game::paginate($request))->jsonSerialize();

  return Response::json($response);
});

Route::get('/genres', function (Request $request){
  $response = Genre::all();

  return Response::json($response);
});
