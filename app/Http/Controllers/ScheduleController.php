<?php

namespace App\Http\Controllers;

use App\Http\Requests\Schedule\StoreRequest;
use App\Http\Resources\ScheduleResource;
use App\Services\ScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Response;
use Phobiavr\PhoberLaravelCommon\Clients\StaffClient;
use Symfony\Component\HttpFoundation\Response as ResponseFoundation;

class ScheduleController extends BaseController {
    public function __construct(private readonly ScheduleService $service) {
    }

    //TODO:: refactor
    public function store(StoreRequest $request): JsonResponse {
        $schedule = $this->service->create($request->data());

        return Response::json($schedule);
    }

    public function activeForInstance(string $idOrMacAddress): JsonResponse {
        $schedule = $this->service->activeForInstance($idOrMacAddress);

        $resource = ScheduleResource::make($schedule);

        if ($schedule?->session_id) {
            $response = StaffClient::sessionById($schedule->session_id);
            if ($response->ok()) {
                $data = json_decode($response->body());
                $resource->servicedByName = $data->serviced_by_name ?? null;
                $resource->customer = $data->customer ?? null;
            }
        }

        return Response::json($resource);
    }

    public function cancel(int $id): JsonResponse {
        $this->service->cancel($id);

        return Response::json(status: ResponseFoundation::HTTP_NO_CONTENT);
    }
}
