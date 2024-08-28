<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Shared\Device\ScheduleEnum;

class ScheduleRequest extends FormRequest {
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules() {
        return [
            'type' => ['required', Rule::enum(ScheduleEnum::class)],
            'instance_id' => ['required', 'exists:device_instances,id']
        ];
    }
}
