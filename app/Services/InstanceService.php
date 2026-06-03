<?php

namespace App\Services;

use App\Models\Instance;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Collection;
use Phobiavr\PhoberLaravelCommon\Clients\StaffClient;

class InstanceService {
    public function all(): Collection {
        return Instance::all()->sortBy('id');
    }

    public function findWithSession(string $idOrMacAddress): Instance {
        /** @var Instance $instance */
        $instance = Instance::findByIdOrMacAddressOrFail($idOrMacAddress);

        /** @var Schedule $schedule */
        if ($schedule = $instance->getActiveSchedule()) {
            $sessionInfo = StaffClient::sessionById($schedule->session_id);

            if (!$sessionInfo->failed()) {
                $instance->session = $sessionInfo->json();
            }
        }

        return $instance;
    }
}
