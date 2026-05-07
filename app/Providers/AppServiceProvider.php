<?php

namespace App\Providers;

use App\Models\Device;
use App\Models\Game;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        //
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
    }
}
