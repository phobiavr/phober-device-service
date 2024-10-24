<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Phobiavr\PhoberLaravelCommon\Enums\ScheduleEnum;
use Phobiavr\PhoberLaravelCommon\Traits\Authorable;

/**
 * @property \DateTime $start
 * @property \DateTime $end
 */
class Schedule extends Model {
    use Authorable;

    protected $fillable = [
        'type', 'instance_id', 'start', 'end'
    ];

    protected $casts = [
        'start' => 'datetime',
        'end'   => 'datetime',
    ];

    public function instance(): BelongsTo {
        return $this->belongsTo(DeviceInstance::class);
    }

    public function isActive(): bool {
        $now = now()->format('Y-m-d H:i:s');

        return $this->type !== ScheduleEnum::CANCELED->value && (
            ($this->start === null && $this->end === null) ||
            ($this->start === null && $this->end > $now) ||
            ($this->start < $now && $this->end === null) ||
            ($this->start < $now && $this->end > $now)
        );
    }
}
