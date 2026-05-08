<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Response;
use Phobiavr\PhoberLaravelCommon\Pageable\PageableCollection;
use Phobiavr\PhoberLaravelCommon\Pageable\PageableRequest;

class PostController extends BaseController {
    public function __construct(private readonly PostService $service) {
    }

    public function index(PageableRequest $request): JsonResponse {
        $list = $this->service->paginate($request);

        return Response::json((new PageableCollection($list, PostResource::class))->jsonSerialize());
    }
}
