<?php

namespace App\Providers;

use App\Events\ScheduleUpdated;
use App\Listeners\ScheduleUpdatedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
//        ScheduleUpdated::class => [
//            ScheduleUpdatedListener::class,
//        ],
    ];
}
