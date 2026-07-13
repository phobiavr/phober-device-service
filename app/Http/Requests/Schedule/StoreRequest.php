<?php

namespace App\Http\Requests\Schedule;

use App\Models\Schedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Phobiavr\PhoberLaravelCommon\Data\SchedulePayload;
use Phobiavr\PhoberLaravelCommon\Enums\ScheduleEnum;

class StoreRequest extends FormRequest {
    public function rules(): array {
        ValidatorFacade::extend('free', function ($attribute, $value, $parameters, Validator $validator) {
            $data = $validator->getData();

            $start = $data['start'];
            $end   = $data['end'];

            if (!$instanceId = ($data['instance_id'] ?? false)) {
                return false;
            }

            $conflicting = Schedule::query()
                ->where('instance_id', $instanceId)
                ->where('type', '<>', ScheduleEnum::CANCELED->value)
                ->where(function ($query) use ($start, $end) {
                    $query->where(function ($query) {
                        $query->whereNull('start')->whereNull('end');
                    })->orWhere(function ($query) use ($start) {
                        $query->where('end', '>=', $start)->whereNull('start');
                    })->orWhere(function ($query) use ($end) {
                        if ($end !== null) {
                            $query->where('end', '<=', $end)->whereNull('start');
                        }
                    })->orWhere(function ($query) use ($start) {
                        $query->where('start', '<=', $start)->whereNull('end');
                    })->orWhere(function ($query) use ($end) {
                        if ($end !== null) {
                            $query->where('start', '>=', $end)->whereNull('end');
                        }
                    })->orWhere(function ($query) use ($start, $end) {
                        if ($end === null) {
                            $query->where('start', '>=', $start)->where('end', '<=', $start);
                        } else {
                            $query->orWhere(function ($q) use ($start) {
                                $q->where('start', '<=', $start)->where('end', '>=', $start);
                            })->orWhere(function ($q) use ($end) {
                                $q->where('start', '<=', $end)->where('end', '>=', $end);
                            });
                        }
                    });
                });

            return !$conflicting->exists();
        }, 'The schedule conflicts with an existing schedule.');

        $instanceExists = Rule::exists('instances', 'id')->where(fn($q) => $q->where('active', true));

        return [
            'type'        => ['required', Rule::enum(ScheduleEnum::class)],
            'instance_id' => ['required', $instanceExists],
            'start'       => ['required', 'date_format:Y-m-d H:i:s', 'free'],
            'end'         => ['nullable', 'date', 'after:start'],
        ];
    }

    public function payload(): SchedulePayload {
        return SchedulePayload::fromArray($this->validated());
    }
}
