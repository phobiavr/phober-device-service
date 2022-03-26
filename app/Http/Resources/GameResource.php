<?php

namespace App\Http\Resources;

use App\Models\Game;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class GameResource extends JsonResource {
  /**
   * Transform the resource into an array.
   *
   * @param Request $request
   * @return array|Arrayable|JsonSerializable
   */
  public function toArray($request): array|JsonSerializable|Arrayable {
    /** @var Game $this */

    return [
      "id" => $this->id,
      "name" => $this->name,
      "slug" => $this->slug,
      "video" => "https://www.youtube.com/watch?v=" . $this->video,
      "description" => $this->description,
      "rating" => $this->rating,
      "multiplayer" => $this->multiplayer,
      "preview" => $this->getMedia('preview')->first()?->original_url
    ];
  }
}
