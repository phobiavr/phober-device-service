<?php

namespace App\Models;

class Media extends \Spatie\MediaLibrary\MediaCollections\Models\Media {
  protected $connection = "db_media";
}