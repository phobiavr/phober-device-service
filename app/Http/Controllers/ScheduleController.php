<?php

namespace App\Http\Controllers;

use App\Http\Requests\Schedule\StoreRequest;
use App\Http\Resources\ScheduleResource;
use App\Models\Schedule;
use App\Services\ScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Phobiavr\PhoberLaravelCommon\Clients\StaffClient;
use Phobiavr\PhoberLaravelCommon\Exceptions\ServiceUnavailableException;
use Symfony\Component\HttpFoundation\Response as ResponseFoundation;

class ScheduleController extends BaseController {
    public function __construct(private readonly ScheduleService $service) {
    }

    //TODO:: refactor
    public function store(StoreRequest $request): JsonResponse {
        $schedule = $this->service->create($request->payload());

        return Response::json($schedule);
    }

    public function activeForInstance(int $id): JsonResponse {
        return $this->scheduleResponse($this->service->activeForInstanceById($id));
    }

    public function activeForInstanceByMac(string $macAddress): JsonResponse {
        return $this->scheduleResponse($this->service->activeForInstanceByMac($macAddress));
    }

    private function scheduleResponse(?Schedule $schedule): JsonResponse {
        $resource = ScheduleResource::make($schedule);

        if ($schedule?->session_id) {
            try {
                $response = StaffClient::sessionById($schedule->session_id);
                if ($response->ok()) {
                    $data = json_decode($response->body());
                    $resource->servicedByName = $data->serviced_by_name ?? null;
                    $resource->customer = $data->customer ?? null;
                }
            } catch (ServiceUnavailableException $e) {
                Log::error('Failed to enrich schedule: staff-service unreachable', [
                    'session_id' => $schedule->session_id,
                    'message'    => $e->getMessage(),
                ]);
            }
        }

        return Response::json($resource);
    }

    public function cancel(int $id): JsonResponse {
        $this->service->cancel($id);

        return Response::json(status: ResponseFoundation::HTTP_NO_CONTENT);
    }
}
