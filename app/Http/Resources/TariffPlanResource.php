<?php

namespace App\Http\Resources;

use App\Models\TariffPlan;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TariffPlan */
class TariffPlanResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>|Arrayable<array-key, mixed>|\JsonSerializable
     */
    public function toArray($request) {
        return [
            "id"     => $this->id,
            "device" => $this->device,
            "tariff" => $this->tariff,
            "time"   => $this->time,
            "price"  => $this->price,
        ];
    }
}
