<?php

namespace App\Http\Controllers;

use App\Http\Resources\InstanceResource;
use App\Services\InstanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Response;

class InstanceController extends BaseController {
    public function __construct(private readonly InstanceService $service) {
    }

    public function index(): JsonResponse {
        return Response::json(InstanceResource::collection($this->service->all()));
    }

    public function show(string $idOrMacAddress): JsonResponse {
        return Response::json(InstanceResource::make($this->service->findWithSession($idOrMacAddress)));
    }
}
