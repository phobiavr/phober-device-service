<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pageable;
use App\Http\Resources\GameCollection;
use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

class GameController extends Controller {
  public function index(Pageable $request): JsonResponse {
    $response = GameCollection::make(Game::paginate($request))->jsonSerialize();

    return Response::json($response);
  }
}
