<?php

namespace App\Models;

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
 */
class Game extends Model implements HasMedia {
  use InteractsWithMedia, WithPagination;

  protected $casts = ["multiplayer" => "boolean", "description" => "array"];

  public function media(): MorphMany {
    return $this->morphMany(config('media-library.media_model'), 'model');
  }

  public function registerMediaCollections(): void {
    $this->addMediaCollection('preview')->useDisk('media');
  }
}
