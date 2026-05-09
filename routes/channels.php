<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('schedule.{deviceId}', function ($user, $deviceId) {
    return true;
});
