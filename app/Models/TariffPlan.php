<?php

namespace App\Models;

use Database\Factories\TariffPlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $tariff
 * @property string $time
 * @property float $price
 * @property string $device
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class TariffPlan extends Model {
    /** @use HasFactory<TariffPlanFactory> */
    use HasFactory;
}
