<?php

namespace App\Services;

use App\Models\Device;
use Illuminate\Database\Eloquent\Collection;

class DeviceService {
    /** @return Collection<int, Device> */
    public function allWithMedia(): Collection {
        return Device::with('media')->get();
    }
}
