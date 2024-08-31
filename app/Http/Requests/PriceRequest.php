<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Shared\Enums\DeviceEnum;
use Shared\Enums\SessionTariffEnum;
use Shared\Enums\SessionTimeEnum;

class PriceRequest extends FormRequest {
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules() {
        return [
            'instance_id' => ['required_without:device', 'exists:instances,id'],
            'device' => ['required_without:instance_id', Rule::enum(DeviceEnum::class)],
            'tariff' => ['required', Rule::enum(SessionTariffEnum::class)],
            'time' => ['required', Rule::enum(SessionTimeEnum::class)]
        ];
    }
}
