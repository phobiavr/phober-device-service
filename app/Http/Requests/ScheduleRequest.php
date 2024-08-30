<?php

namespace App\Http\Requests;

use App\Models\Schedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Shared\Device\ScheduleEnum;

class ScheduleRequest extends FormRequest {
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules() {
        /*
         whereNull('start') and whereNull('end'): Checks if both start and end are null, which means the schedule is unrestricted.
         where('end', '>=', $start): If end is not provided (null), check if the schedule ends after the proposed start.
         where('start', '<=', $start): If end is not provided, check if the schedule starts before the proposed start.
         where('start', '>=', $start): If end is provided, check if the schedule overlaps with the proposed start and end.
         */
        ValidatorFacade::extend('free', function ($attribute, $value, $parameters, Validator $validator) {
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
                    })->orWhere(function ($query) use ($start, $end) {
                        if ($end !== null) {
                            $query->where('end', '<=', $end)
                                ->whereNull('start');
                        }
                    })->orWhere(function ($query) use ($start, $end) {
                        $query->where('start', '<=', $start)
                            ->whereNull('end');
                    })->orWhere(function ($query) use ($start, $end) {
                        if ($end !== null) {
                            $query->where('start', '>=', $end)
                                ->whereNull('end');
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
            'start'       => ['required', 'date_format:Y-m-d H:i:s', 'free'],
            'end'         => ['nullable', 'date', 'after:start'],
        ];
    }
}
