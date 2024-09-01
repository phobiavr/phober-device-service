<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstanceResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request) {
        return [
            "id"       => $this->id,
            "device"   => $this->device,
            "active"   => $this->active,
            "session" => $this->session,
            'schedule' => ScheduleResource::make($this?->getActiveSchedule()),
        ];
    }
}
