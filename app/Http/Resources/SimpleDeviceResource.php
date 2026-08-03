<?php

namespace App\Http\Resources;

use App\Models\Device;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Device */
class SimpleDeviceResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>|Arrayable<array-key, mixed>|\JsonSerializable
     */
    public function toArray($request) {
        return [
            "id"   => $this->id,
            "name" => $this->name,
            "type" => $this->type,
            "slug" => $this->slug,
            "logo" => $this->getFirstMediaUrl('logo'),
        ];
    }
}
