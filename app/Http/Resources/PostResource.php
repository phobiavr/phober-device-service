<?php

namespace App\Http\Resources;

use App\Models\Post;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Post */
class PostResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>|Arrayable<array-key, mixed>|\JsonSerializable
     */
    public function toArray($request) {
        return [
            "title" => $this->title,
            "post"  => $this->post[app()->getLocale()] ?? $this->post['en'] ?? null,
        ];
    }
}
