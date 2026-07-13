<?php

namespace App\Services;

use App\Models\Instance;
use App\Models\TariffPlan;
use Illuminate\Database\Eloquent\Collection;
use Phobiavr\PhoberLaravelCommon\Data\PricePayload;

class TariffPlanService {
    public function all(): Collection {
        return TariffPlan::all();
    }

    public function find(PricePayload $payload): ?TariffPlan {
        $device = $payload->device?->value ?? Instance::findOrFail($payload->instanceId)->device;

        return TariffPlan::query()
            ->where('device', $device)
            ->where('tariff', $payload->tariff->value)
            ->where('time', $payload->time->value)
            ->first();
    }
}
