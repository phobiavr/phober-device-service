<?php

namespace App\Http\Controllers;

use App\Http\Requests\Game\SearchRequest;
use App\Http\Resources\GameResource;
use App\Services\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Response;
use Phobiavr\PhoberLaravelCommon\Pageable\PageableCollection;
use Phobiavr\PhoberLaravelCommon\Pageable\PageableRequest;

class GameController extends BaseController {
    public function __construct(private readonly GameService $service) {
    }

    public function index(PageableRequest $request): JsonResponse {
        $list = $this->service->paginate($request);

        return Response::json((new PageableCollection($list, GameResource::class))->jsonSerialize());
    }

    public function search(SearchRequest $request): JsonResponse {
        $list = $this->service->search($request, $request->filters());

        return Response::json(new PageableCollection($list, GameResource::class));
    }

    public function show(int $id): JsonResponse {
        return Response::json(GameResource::make($this->service->find($id)));
    }
}
