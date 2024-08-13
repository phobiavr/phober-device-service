<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|\JsonSerializable
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
