<?php

namespace App\Providers;

use App\Services\SessionScheduleHandler;
use Illuminate\Support\ServiceProvider;
use Phobiavr\PhoberLaravelCommon\Contracts\SessionScheduleHandlerInterface;

class DependencyInjectionServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(SessionScheduleHandlerInterface::class, SessionScheduleHandler::class);
    }
}
