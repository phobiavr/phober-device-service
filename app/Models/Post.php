<?php

namespace App\Models;

use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Phobiavr\PhoberLaravelCommon\Pageable\Pageable;

/**
 * @property int $id
 * @property string $title
 * @property array<string, mixed> $post
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Post extends Model {
    /** @use HasFactory<PostFactory> */
    use HasFactory;
    use Pageable;

    protected $casts = ['post' => 'array'];
}
