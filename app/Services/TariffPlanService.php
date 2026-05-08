<?php

namespace App\Services;

use App\Models\TariffPlan;
use Illuminate\Database\Eloquent\Collection;

class TariffPlanService {
    public function all(): Collection {
        return TariffPlan::all();
    }

    public function find(string $device, string $tariff, string $time): ?TariffPlan {
        return TariffPlan::query()
            ->where('device', $device)
            ->where('tariff', $tariff)
            ->where('time', $time)
            ->first();
    }
}
