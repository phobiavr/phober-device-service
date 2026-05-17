<?php

namespace App\Providers;

use App\Models\Device;
use App\Models\Game;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class MorphMapServiceProvider extends ServiceProvider {
    public function boot(): void {
        Relation::morphMap([
            'device-game' => Game::class,
            'device-model' => Device::class,
        ]);
    }
}
