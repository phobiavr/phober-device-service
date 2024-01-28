<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *    title="Phober - Device Service",
 *    version="V 1.0.0",
 *    @OA\Contact(
 *      name="Hikmat",
 *      url="https://www.linkedin.com/in/abdukhaligov/",
 *      email="hikmat.pou@gmail.com"
 *    ),
 * )
 *
 * @OA\Server(
 *      url="http://localhost:8300",
 *      description="localhost"
 * )
 *
 *
 * @OA\Get(
 *   path="/games",
 *   summary="Get Games",
 *   operationId="games",
 *   tags={"Games"},
 *   security={},
 *   @OA\Parameter(
 *     name="size",
 *     in="path",
 *     required=false,
 *     @OA\Schema(
 *       type="integer"
 *     )
 *   ),
 *   @OA\Parameter(
 *     name="page",
 *     in="path",
 *     required=false,
 *     @OA\Schema(
 *       type="integer"
 *     )
 *   ),
 *   @OA\Response(
 *     response="200",
 *     description="ok",
 *   )
 * )
 *
 *
 * @OA\Get(
 *   path="/games/{id}",
 *   summary="Get Game by ID",
 *   operationId="gameById",
 *   tags={"Games"},
 *   security={},
 *   @OA\Parameter(
 *     name="id",
 *     in="path",
 *     required=true,
 *     @OA\Schema(
 *       type="integer"
 *     )
 *   ),
 *   @OA\Response(
 *     response="200",
 *     description="ok",
 *   )
 * )
 *
 * @OA\Get(
 *   path="/games/search/by-device/{device}",
 *   summary="Get Games by Device slug",
 *   operationId="gamesByDevice",
 *   tags={"Games"},
 *   security={},
 *   @OA\Parameter(
 *     name="device",
 *     in="path",
 *     required=true,
 *     @OA\Schema(
 *       type="string"
 *     )
 *   ),
 *   @OA\Parameter(
 *     name="size",
 *     in="path",
 *     required=false,
 *     @OA\Schema(
 *       type="integer"
 *     )
 *   ),
 *   @OA\Parameter(
 *     name="page",
 *     in="path",
 *     required=false,
 *     @OA\Schema(
 *       type="integer"
 *     )
 *   ),
 *   @OA\Response(
 *     response="200",
 *     description="ok",
 *   )
 * )
 *
 * @OA\Get(
 *   path="/games/search/by-genre/{genre}",
 *   summary="Get Games by Genre slug",
 *   operationId="gamesByGenre",
 *   tags={"Games"},
 *   security={},
 *   @OA\Parameter(
 *     name="genre",
 *     in="path",
 *     required=true,
 *     @OA\Schema(
 *       type="string"
 *     )
 *   ),
 *   @OA\Parameter(
 *     name="size",
 *     in="path",
 *     required=false,
 *     @OA\Schema(
 *       type="integer"
 *     )
 *   ),
 *   @OA\Parameter(
 *     name="page",
 *     in="path",
 *     required=false,
 *     @OA\Schema(
 *       type="integer"
 *     )
 *   ),
 *   @OA\Response(
 *     response="200",
 *     description="ok",
 *   )
 * )
 *
 * @OA\Get(
 *   path="/games/search/by-rating/{rating}",
 *   summary="Get Games by Rating",
 *   operationId="gamesByRating",
 *   tags={"Games"},
 *   security={},
 *   @OA\Parameter(
 *     name="rating",
 *     in="path",
 *     required=true,
 *     @OA\Schema(
 *       type="integer"
 *     )
 *   ),
 *   @OA\Parameter(
 *     name="size",
 *     in="path",
 *     required=false,
 *     @OA\Schema(
 *       type="integer"
 *     )
 *   ),
 *   @OA\Parameter(
 *     name="page",
 *     in="path",
 *     required=false,
 *     @OA\Schema(
 *       type="integer"
 *     )
 *   ),
 *   @OA\Response(
 *     response="200",
 *     description="ok",
 *   )
 * )
 *
 *  @OA\Get(
 *   path="/games/search/multiplayer",
 *   summary="Get Multiplayer Games",
 *   operationId="gamesMultiplayer",
 *   tags={"Games"},
 *   security={},
 *   @OA\Parameter(
 *     name="size",
 *     in="path",
 *     required=false,
 *     @OA\Schema(
 *       type="integer"
 *     )
 *   ),
 *   @OA\Parameter(
 *     name="page",
 *     in="path",
 *     required=false,
 *     @OA\Schema(
 *       type="integer"
 *     )
 *   ),
 *   @OA\Response(
 *     response="200",
 *     description="ok",
 *   )
 * )
 *
 *
 * @OA\Get(
 *   path="/devices",
 *   summary="Get Devices",
 *   operationId="devices",
 *   tags={"Devices"},
 *   security={},
 *   @OA\Response(
 *     response="200",
 *     description="ok",
 *   )
 * )
 *
 *
 * @OA\Get(
 *   path="/genres",
 *   summary="Get Genres",
 *   operationId="genres",
 *   tags={"Genres"},
 *   security={},
 *   @OA\Response(
 *     response="200",
 *     description="ok",
 *   )
 * )
 */


class Controller extends BaseController {
  use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
