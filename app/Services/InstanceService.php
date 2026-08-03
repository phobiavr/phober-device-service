<?php

namespace App\Services;

use App\Models\Instance;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Phobiavr\PhoberLaravelCommon\Clients\StaffClient;
use Phobiavr\PhoberLaravelCommon\Exceptions\ServiceUnavailableException;

class InstanceService {
    /** @return Collection<int, Instance> */
    public function all(): Collection {
        return Instance::all()->sortBy('id');
    }

    public function findWithSession(string $idOrMacAddress): Instance {
        $instance = Instance::findByIdOrMacAddressOrFail($idOrMacAddress);

        if ($schedule = $instance->getActiveSchedule()) {
            try {
                $sessionInfo = StaffClient::sessionById((int) $schedule->session_id);

                if (!$sessionInfo->failed()) {
                    $instance->session = $sessionInfo->json();
                }
            } catch (ServiceUnavailableException $e) {
                Log::error('Failed to load session for instance: staff-service unreachable', [
                    'session_id' => $schedule->session_id,
                    'message'    => $e->getMessage(),
                ]);
            }
        }

        return $instance;
    }
}
