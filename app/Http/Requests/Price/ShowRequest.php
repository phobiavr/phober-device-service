<?php

namespace App\Http\Requests\Price;

use App\Models\Instance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
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

    public function device(): string {
        if ($device = $this->input('device')) {
            return $device;
        }

        return Instance::findOrFail($this->input('instance_id'))->device;
    }

    public function tariff(): string {
        return $this->input('tariff');
    }

    public function time(): string {
        return $this->input('time');
    }
}
