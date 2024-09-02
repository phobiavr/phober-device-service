<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Shared\Pageable\Pageable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

/**
 * @property integer id
 * @property string name
 * @property string slug
 * @property string video
 * @property array description
 * @property integer rating
 * @property boolean multiplayer
 * @property string preview
 */
class Game extends Model implements HasMedia {
    use InteractsWithMedia, Pageable, HasTranslations;

    public function getMorphClass() {
        return 'device-game';
    }

    public array $translatable = ['description'];

    protected $casts = ["multiplayer" => "boolean"];
    protected $appends = ['preview'];
    protected $hidden = ['media', 'updated_at', 'created_at'];
    protected $with = ['genres', 'devices'];

    public function video(): Attribute {
        return Attribute::make(
            get: fn($value) => $value ? "https://www.youtube.com/watch?v=" . $value : null
        );
    }

    public function preview(): Attribute {
        return Attribute::make(
            get: fn() => $this->getMedia('preview')->first()?->original_url
        );
    }

    public function genres(): BelongsToMany {
        return $this->belongsToMany(Genre::class, 'game_genre', 'game_id', 'genre_id');
    }

    public function devices(): BelongsToMany {
        return $this->belongsToMany(Device::class, 'game_device', 'game_id', 'device', 'id', 'type');
    }
}
