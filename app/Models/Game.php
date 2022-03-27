<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
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
class Game extends Model implements HasMedia {
  use InteractsWithMedia, WithPagination;

  protected $casts = ["multiplayer" => "boolean", "description" => "array"];
  protected $appends = ['preview'];
  protected $hidden = ['media', 'updated_at', 'created_at'];

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
}
