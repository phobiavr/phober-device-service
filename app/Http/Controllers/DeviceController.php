<?php

namespace App\Http\Controllers;

use App\Http\Resources\SimpleDeviceResource;
use App\Services\DeviceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Response;

class DeviceController extends BaseController {
    public function __construct(private readonly DeviceService $service) {
    }

    public function index(): JsonResponse {
        return Response::json(SimpleDeviceResource::collection($this->service->allWithMedia()));
    }
}
