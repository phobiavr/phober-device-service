<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable {
    public function toArray(): array {
        return [
            "id"         => $this->id,
            "username"   => $this->username,
            "first_name" => $this->first_name,
            "last_name"  => $this->last_name,
            "email"      => $this->email,
        ];
    }
}
