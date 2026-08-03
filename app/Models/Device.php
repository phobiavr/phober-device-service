<?php

namespace App\Models;

use Database\Factories\DeviceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string $slug
 * @property array<string, mixed>|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Device extends Model implements HasMedia {
    /** @use HasFactory<DeviceFactory> */
    use HasFactory;
    use InteractsWithMedia;

    protected $hidden = ['created_at', 'updated_at', 'pivot'];
    protected $casts = [
        "description" => "array"
    ];

    public function registerMediaCollections(): void {
        $this->addMediaCollection('logo')->useDisk('media');
    }
}
