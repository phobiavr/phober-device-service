<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Instance extends Model {
    protected $casts = [
        'active'             => 'boolean',
        'deactivation_start' => 'datetime',
        'deactivation_end'   => 'datetime',
    ];

    protected $with = ['schedules'];

    public $session = null;

    public function schedules(): HasMany {
        return $this->hasMany(Schedule::class, 'instance_id');
    }

    public function device(): BelongsTo {
        return $this->belongsTo(Device::class);
    }

    public function getActiveSchedule() {
        return $this->schedules->filter(function ($schedule) {
            return $schedule->isActive();
        })->sortBy('end')->first();
    }

    public static function findByIdOrMacAddressOrFail($idOrMacAddress): Model|Builder {
        return static::query()
            ->where('id', $idOrMacAddress)
            ->orWhere('mac_address', $idOrMacAddress)
            ->firstOrFail();
    }

    public function getLabelAttribute(): string {
        $position = DeviceInstance::where('device', $this->device)
            ->where('id', '<=', $this->id)
            ->count();

        return "{$this->device} - {$position}";
    }
}
