<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Instance extends Model {
    protected $table = 'device_instances';
    protected $casts = [
        'active'             => 'boolean',
        'deactivation_start' => 'datetime',
        'deactivation_end'   => 'datetime',
    ];

    public function schedules(): HasMany {
        return $this->hasMany(Schedule::class, 'instance_id');
    }

    public function activeSchedules(): HasMany {
        return $this->schedules()->active();
    }

    public function getActiveAttribute() {
        return !$this->activeSchedules()->exists();
    }

    public function device(): BelongsTo {
        return $this->belongsTo(Device::class);
    }

    public function getLabelAttribute(): string {
        $deviceName = $this->device->name;

        $position = self::where('device_id', $this->device_id)
            ->where('id', '<=', $this->id)
            ->count();

        return "{$deviceName} - {$position}";
    }
}
