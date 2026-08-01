<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Phobiavr\PhoberLaravelCommon\Pageable\Pageable;

class Post extends Model {
    use HasFactory;
    use Pageable;

    protected $casts = ['post' => 'array'];
}
