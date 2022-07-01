<?php

namespace App\Http\Resources;

use App\Models\Device;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SimpleDeviceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
      /** @var Device $this */

      return [
          "id" => $this->id,
          "name" => $this->name,
          "slug" => $this->slug,
        ];
    }
}
