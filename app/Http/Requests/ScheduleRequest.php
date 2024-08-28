<?php

namespace App\Http\Requests;

use App\Models\Schedule;
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
        \Illuminate\Support\Facades\Validator::extend('free', function ($attribute, $value, $parameters, \Illuminate\Validation\Validator $validator) {
            $data = $validator->getData();

            $start = $data['start'];
            $end = $data['end'];
            $instanceId = $data['instance_id'];

            $conflictingSchedules = Schedule::query()
                ->where('instance_id', $instanceId)
                ->where(function ($query) use ($start, $end) {
                    $query->where(function ($query) {
                        $query->whereNull('start')
                            ->whereNull('end');
                    })->orWhere(function ($query) use ($start, $end) {
                        $query->where('end', '>=', $start)
                            ->whereNull('start');

                        if ($end !== null) {
                            $query->where('end', '<=', $end);
                        }
                    })->orWhere(function ($query) use ($start, $end) {
                        $query->where('start', '<=', $start)
                            ->whereNull('end');

                        if ($end !== null) {
                            $query->where('start', '>=', $end);
                        }
                    })->orWhere(function ($query) use ($start, $end) {
                        if ($end === null) {
                            $query->where('start', '>=', $start)
                                ->where('end', '<=', $start);
                        } else {
                            $query->where('start', '>=', $start)
                                ->where('end', '<=', $end);
                        }
                    });
                });

            return !$conflictingSchedules->exists();
        }, 'The schedule conflicts with an existing schedule.');

        return [
            'type'        => ['required', Rule::enum(ScheduleEnum::class)],
            'instance_id' => ['required', 'exists:device_instances,id'],
            'start'       => ['required', 'date_format:Y-m-d H:i:s', 'after:now', 'free'],
            'end'         => ['nullable', 'date', 'after:start'],
        ];
    }
}
