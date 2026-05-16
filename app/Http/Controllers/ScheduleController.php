<?php

namespace App\Http\Controllers;

use App\Http\Requests\Schedule\StoreRequest;
use App\Http\Resources\ScheduleResource;
use App\Services\ScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as ResponseFoundation;

class ScheduleController extends BaseController {
    public function __construct(private readonly ScheduleService $service) {
    }

    public function store(StoreRequest $request): JsonResponse {
        $schedule = $this->service->create($request->data());

        return Response::json($schedule);
    }

    public function activeForInstance(string $idOrMacAddress): JsonResponse {
        return Response::json(ScheduleResource::make($this->service->activeForInstance($idOrMacAddress)));
    }

    public function cancel(int $id): JsonResponse {
        $this->service->cancel($id);

        return Response::json(status: ResponseFoundation::HTTP_NO_CONTENT);
    }
}
