<?php

use App\Models\Instance;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('instances', function ($user) {
    return $user !== null;
});

Broadcast::channel('schedule.{deviceId}', function ($user, $deviceId) {
    return $user !== null && Instance::existsByIdOrMacAddress($deviceId);
});
