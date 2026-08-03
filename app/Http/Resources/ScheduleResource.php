<?php

namespace App\Http\Resources;

use App\Models\Schedule;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Schedule */
class ScheduleResource extends JsonResource {
    public ?string $servicedByName = null;
    public ?string $customer = null;

    /** @return array<string, mixed>|Arrayable<array-key, mixed>|\JsonSerializable */
    public function toArray($request) {
        $type = 'N/A';
        $countdown = 0;

        if ($this->resource) {
            $type = $this->type;
            $countdown = $this->end ? now()->diffInSeconds($this->end) : -1;
        }

        return [
            'type'             => $type,
            'countdown'        => (int) $countdown,
            'serviced_by_name' => $this->servicedByName,
            'customer'         => $this->customer,
            'start'            => $this->resource?->start,
            'end'              => $this->resource?->end,
        ];
    }
}
