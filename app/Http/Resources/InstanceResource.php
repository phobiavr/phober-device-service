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
        $upcoming = $this->resource->getUpcomingSchedule();

        return [
            'id'               => $this->id,
            'label'            => $this->label,
            'device'           => $this->device,
            'mac_address'      => $this->mac_address,
            'active'           => $this->active,
            'created_at'       => $this->created_at,
            'session'          => $this->session,
            'schedule'         => ScheduleResource::make($this->getActiveSchedule()),
            'upcoming_schedule' => $upcoming ? [
                'type'      => $upcoming->type,
                'starts_in' => (int) now()->diffInSeconds($upcoming->start),
            ] : null,
        ];
    }
}
