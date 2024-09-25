<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Phobiavr\PhoberLaravelCommon\Pageable\Pageable;

class Post extends Model {
    use Pageable;

    protected $casts = ['post' => 'array'];
}
