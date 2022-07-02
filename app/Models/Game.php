<?php

namespace App\Models;

use Abdukhaligov\LaravelPageable\PaginateModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

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
class Game extends PaginateModel implements HasMedia {
  use InteractsWithMedia;

  protected $casts = ["multiplayer" => "boolean", "description" => "array"];
  protected $appends = ['preview'];
  protected $hidden = ['media', 'updated_at', 'created_at'];
  protected $with = ['genres', 'devices'];

  public function media(): MorphMany {
    return $this->morphMany(config('media-library.media_model'), 'model');
  }

  public function registerMediaCollections(): void {
    $this->addMediaCollection('preview')->useDisk('media');
  }

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
    return $this
      ->belongsToMany(Genre::class, 'game_genre');
  }

  public function devices(): BelongsToMany {
    return $this
      ->belongsToMany(Device::class, 'game_device');
  }
}
