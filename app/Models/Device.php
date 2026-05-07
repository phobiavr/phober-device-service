<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Device extends Model implements HasMedia {
    use InteractsWithMedia;

    protected $hidden = ['created_at', 'updated_at', 'pivot'];
    protected $casts = [
        "description" => "array"
    ];

    public function registerMediaCollections(): void {
        $this->addMediaCollection('logo')->useDisk('media');
    }
}
