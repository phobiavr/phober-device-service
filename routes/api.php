<?php

use App\Http\Resources\GameResource;
use App\Models\Device;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Instance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
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

Route::middleware('auth.server')->post('/schedule', function (\App\Http\Requests\ScheduleRequest $request) {
    $schedule = \App\Models\Schedule::create($request->validated());

    return Response::json($schedule);
});

Route::get('/schedule', function (Request $request) {
    $query = Instance::query();

    if ($id = $request->get('id')) {
        $query->where('id', $id);
    }

    if ($macAddress = $request->get('mac_address')) {
        $query->where('mac_address', $macAddress);
    }

    abort_if((!isset($id) && !isset($macAddress)), ResponseFoundation::HTTP_UNPROCESSABLE_ENTITY);

    $type = 'N/A';
    $countdown = 0;

    if (
        ($instance = $query->first()) &&
        ($schedule = $instance->activeSchedules()->orderBy('end', 'ASC')->get()->first())
    ) {
        $type = $schedule->type;
        $countdown = $schedule->end ? $schedule->end->diffInSeconds(now()) : -1;
    }

    return Response::json(['type' => $type, 'countdown' => $countdown]);
});

Route::post('/games/search', function (PageableRequest $request) {
    $query = Game::query();

    if ($device = $request->get('device')) {
        $query->whereRelation('devices', 'slug', $device);
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
