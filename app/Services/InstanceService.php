<?php

namespace App\Services;

use App\Models\Instance;
use Illuminate\Database\Eloquent\Collection;
use Phobiavr\PhoberLaravelCommon\Clients\StaffClient;

class InstanceService {
    public function all(): Collection {
        return Instance::all();
    }

    public function findWithSession(string $idOrMacAddress): Instance {
        $instance = Instance::findByIdOrMacAddressOrFail($idOrMacAddress);

        if ($schedule = $instance->getActiveSchedule()) {
            $sessionInfo = StaffClient::sessionByScheduleId($schedule->id);

            if (!$sessionInfo->failed()) {
                $instance->session = $sessionInfo->json();
            }
        }

        return $instance;
    }
}
