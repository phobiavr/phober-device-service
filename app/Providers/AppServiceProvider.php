<?php

namespace App\Providers;

use App\Events\ScheduleUpdated;
use App\Listeners\BroadcastScheduleChanged;
use App\Models\Device;
use App\Models\Game;
use App\Services\SessionScheduleHandler;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Phobiavr\PhoberLaravelCommon\Contracts\SessionScheduleHandlerInterface;

class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void {
        $this->app->bind(SessionScheduleHandlerInterface::class, SessionScheduleHandler::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void {
        Relation::morphMap([
            'device-game' => Game::class,
            'device-model' => Device::class,
        ]);

        Event::listen(ScheduleUpdated::class, BroadcastScheduleChanged::class);
    }
}
