<?php

namespace App\Models;

use Database\Factories\ScheduleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Phobiavr\PhoberLaravelCommon\Enums\ScheduleEnum;
use Phobiavr\PhoberLaravelCommon\Traits\Authorable;

/**
 * @property int $id
 * @property string $type
 * @property int $instance_id
 * @property int|null $session_id
 * @property \Carbon\Carbon|null $start
 * @property \Carbon\Carbon|null $end
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property Instance|null $instance
 */
class Schedule extends Model {
    use Authorable;
    /** @use HasFactory<ScheduleFactory> */
    use HasFactory;

    protected $fillable = [
        'type', 'instance_id', 'start', 'end', 'session_id',
    ];

    protected $casts = [
        'start' => 'datetime',
        'end'   => 'datetime',
    ];

    /** @return BelongsTo<Instance, $this> */
    public function instance(): BelongsTo {
        return $this->belongsTo(Instance::class);
    }

    public function isActive(): bool {
        $now = now()->format('Y-m-d H:i:s');

        return $this->type !== ScheduleEnum::CANCELED->value && (
            ($this->start === null && $this->end === null) ||
            ($this->start === null && $this->end > $now) ||
            ($this->start <= $now && $this->end === null) ||
            ($this->start <= $now && $this->end > $now)
        );
    }
}
