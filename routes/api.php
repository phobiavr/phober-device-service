<?php

use App\Http\Requests\PriceRequest;
use App\Http\Requests\ScheduleRequest;
use App\Http\Resources\GameResource;
use App\Http\Resources\InstanceResource;
use App\Http\Resources\ScheduleResource;
use App\Http\Resources\TariffPlanResource;
use App\Models\Device;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Instance;
use App\Models\Schedule;
use App\Models\TariffPlan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Shared\Clients\StaffClient;
use Shared\Enums\ScheduleEnum;
use Shared\Pageable\PageableCollection;
use Shared\Pageable\PageableRequest;
use Symfony\Component\HttpFoundation\Response as ResponseFoundation;

Route::middleware('auth.server')->get('', function () {
    return Auth::guard('server')->user();
});

Route::get('/games', function (PageableRequest $request) {
    $list = Game::paginateFromRequest($request);

    $response = new PageableCollection($list, GameResource::class);

    return Response::json($response->jsonSerialize());
});

Route::middleware('private')->group(function () {
    Route::post('/schedule', function (ScheduleRequest $request) {
        return Response::json(Schedule::create($request->validated()));
    });

    Route::get('/schedule/{idOrMacAddress}', function ($idOrMacAddress) {
        $instance = Instance::findByIdOrMacAddressOrFail($idOrMacAddress);

        return Response::json(ScheduleResource::make($instance->getActiveSchedule()));
    });

    Route::delete('/schedule/{id}', function ($id) {
        $schedule = Schedule::where('type', '<>', ScheduleEnum::CANCELED->value)->findOrFail($id);
        $schedule->type = ScheduleEnum::CANCELED;
        $schedule->save();

        return Response::json('', ResponseFoundation::HTTP_NO_CONTENT);
    });
});

Route::get('/tariff-plans', function () {
    $response = TariffPlan::all();

    return Response::json(TariffPlanResource::collection($response));
});

Route::post('/price', function (PriceRequest $request) {
    $deviceFromInstance = fn() => Instance::find($request->get('instance_id'))->device;

    $plan = TariffPlan::query()
        ->where('device', $request->get('device', $deviceFromInstance()))
        ->where('tariff', $request->get('tariff'))
        ->where('time', $request->get('time'))->get()->first();

    return Response::json(TariffPlanResource::make($plan));
});

Route::get('/instances', function () {
    $instances = Instance::all();

    return Response::json(InstanceResource::collection($instances));
});

Route::get('/instance/{idOrMacAddress}', function ($idOrMacAddress) {
    $instance = Instance::findByIdOrMacAddressOrFail($idOrMacAddress);

    if ($schedule = $instance->getActiveSchedule()) {
        $sessionInfo = StaffClient::sessionByScheduleId($schedule->id);

        if (!$sessionInfo->failed()) {
            $instance->session = $sessionInfo->json();
        }
    }

    return Response::json(InstanceResource::make($instance));
});

Route::post('/games/search', function (PageableRequest $request) {
    $query = Game::query();

    if ($device = $request->get('device')) {
        $query->whereRelation('devices', 'type', $device);
    }

    if ($genre = $request->get('genre')) {
        $query->whereRelation('genres', 'slug', $genre);
    }

    if ($request->get('multiplayer')) {
        $query->where('multiplayer', '=', true);
    }

    if ($rating = $request->get('rating')) {
        $query->where('rating', '=', $rating);
    }

    $list = $query->paginateFromRequest($request);


    return Response::json(new PageableCollection($list, GameResource::class));
});

Route::get('/games/{id}', function (int $id) {
    $game = Game::findOrFail($id);

    return GameResource::make($game);
});

Route::get('/genres', function () {
    $response = Genre::all();

    return Response::json($response);
});

Route::get('/devices', function () {
    $response = Device::all();

    return Response::json($response);
});
