<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model {
    protected $hidden = ['created_at', 'updated_at', 'pivot'];
    protected $casts = [
        "description" => "array"
    ];
}
