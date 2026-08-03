<?php

namespace App\Models;

use Database\Factories\GameFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Phobiavr\PhoberLaravelCommon\Pageable\Pageable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $name
 * @property string|null $slug
 * @property string|null $video
 * @property array<string, string> $description
 * @property int $rating
 * @property bool $multiplayer
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string|null $preview
 * @property \Illuminate\Database\Eloquent\Collection<int, Genre> $genres
 * @property \Illuminate\Database\Eloquent\Collection<int, Device> $devices
 */
class Game extends Model implements HasMedia {
    /** @use HasFactory<GameFactory> */
    use HasFactory, InteractsWithMedia, Pageable, HasTranslations;

    /** @var array<int, string> */
    public array $translatable = ['description'];

    protected $casts = ["multiplayer" => "boolean"];
    protected $appends = ['preview'];
    protected $hidden = ['media', 'updated_at', 'created_at'];
    protected $with = ['genres', 'devices', 'media'];

    /** @return Attribute<string|null, mixed> */
    protected function video(): Attribute {
        return Attribute::make(
            get: fn($value) => $value ? "https://www.youtube.com/watch?v=" . $value : null
        );
    }

    /** @return Attribute<string|null, mixed> */
    protected function preview(): Attribute {
        return Attribute::make(
            get: fn() => $this->getMedia('preview')->first()?->original_url
        );
    }

    /** @return BelongsToMany<Genre, $this> */
    public function genres(): BelongsToMany {
        return $this->belongsToMany(Genre::class, 'game_genre', 'game_id', 'genre_id');
    }

    /** @return BelongsToMany<Device, $this> */
    public function devices(): BelongsToMany {
        return $this->belongsToMany(Device::class, 'game_device', 'game_id', 'device', 'id', 'type');
    }
}
