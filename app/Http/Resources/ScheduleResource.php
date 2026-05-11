<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request) {
        $type = 'N/A';
        $countdown = 0;

        if ($this->resource) {
            $type = $this->type;
            $countdown = $this->end ? now()->diffInSeconds($this->end) : -1;
        }

        return [
            'type'      => $type,
            'countdown' => (int) $countdown,
        ];
    }
}
