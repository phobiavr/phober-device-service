<?php

namespace App\Http\Requests\Price;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Phobiavr\PhoberLaravelCommon\Data\PricePayload;
use Phobiavr\PhoberLaravelCommon\Enums\DeviceEnum;
use Phobiavr\PhoberLaravelCommon\Enums\SessionTariffEnum;
use Phobiavr\PhoberLaravelCommon\Enums\SessionTimeEnum;

class ShowRequest extends FormRequest {
    public function rules(): array {
        return [
            'instance_id' => ['required_without:device', 'exists:instances,id'],
            'device'      => ['required_without:instance_id', Rule::enum(DeviceEnum::class)],
            'tariff'      => ['required', Rule::enum(SessionTariffEnum::class)],
            'time'        => ['required', Rule::enum(SessionTimeEnum::class)],
        ];
    }

    public function payload(): PricePayload {
        return PricePayload::fromArray($this->validated());
    }
}
