<?php

namespace App\Http\Resources;

use App\Models\Game;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Game */
class GameResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>|Arrayable<array-key, mixed>|\JsonSerializable
     */
    public function toArray($request) {
        return [
            "id"          => $this->id,
            "name"        => $this->name,
            "slug"        => $this->slug,
            "video"       => $this->video,
            "device"      => SimpleDeviceResource::collection($this->devices),
            'description' => $this->description,
            "rating"      => $this->rating,
            "multiplayer" => $this->multiplayer,
            "preview"     => $this->preview,
            "genres"      => $this->genres
        ];
    }
}
