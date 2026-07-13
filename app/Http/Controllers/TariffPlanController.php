<?php

namespace App\Http\Controllers;

use App\Http\Requests\Price\ShowRequest;
use App\Http\Resources\TariffPlanResource;
use App\Services\TariffPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Response;

class TariffPlanController extends BaseController {
    public function __construct(private readonly TariffPlanService $service) {
    }

    public function index(): JsonResponse {
        return Response::json(TariffPlanResource::collection($this->service->all()));
    }

    public function price(ShowRequest $request): JsonResponse {
        $plan = $this->service->find($request->payload());

        return Response::json(TariffPlanResource::make($plan));
    }
}
