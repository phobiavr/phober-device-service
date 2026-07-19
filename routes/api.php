<?php

use App\Http\Controllers\DeviceController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\InstanceController;
use App\Http\Controllers\MeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TariffPlanController;
use Illuminate\Support\Facades\Route;


Route::middleware('auth.server')->get('', [MeController::class, 'show']);

Route::get('/games', [GameController::class, 'index']);
Route::post('/games/search', [GameController::class, 'search']);
Route::get('/games/{id}', [GameController::class, 'show']);

Route::get('/posts', [PostController::class, 'index']);

Route::middleware('private')->prefix('/schedule')->group(function () {
    Route::post('/', [ScheduleController::class, 'store']);
    Route::get('/{idOrMacAddress}', [ScheduleController::class, 'activeForInstance']);
    Route::delete('/{id}', [ScheduleController::class, 'cancel']);
});

Route::get('/tariff-plans', [TariffPlanController::class, 'index']);
Route::post('/price', [TariffPlanController::class, 'price']);

Route::get('/instances', [InstanceController::class, 'index']);
Route::get('/instance/{idOrMacAddress}', [InstanceController::class, 'show']);

Route::get('/genres', [GenreController::class, 'index']);
Route::get('/devices', [DeviceController::class, 'index']);
