<?php

namespace App\Http\Requests\Preferences;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TimerRequest extends FormRequest
{
    
    private $max_second = 59;
    private $max_minute = 59;
    private $max_hour = 24;
    
    
    private $min_second = 1;
    private $min_minute = 0;
    private $min_hour = 0;
    
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {
        if ($request->input('hour')>0 || $request->input('minute')>0) {
            return [
            'hour'   => "required|numeric|min:0|max:$this->max_hour",
            'minute' => "required|numeric|min:0|max:$this->max_minute",
            'second' => "required|numeric|min:0|max:$this->max_second",
            ];
        } else {
            return [
            'hour'   => "required|numeric|min:0|max:$this->max_hour",
            'minute' => "required|numeric|min:0|max:$this->max_minute",
            'second' => "required|numeric|min:$this->min_second|max:$this->max_second",
            ];
        }
    }
    
    public function messages()
    {
        return [
            'hour.required' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.hour_required', __('default/permission.validation_messages.hour_required')),
            'minute.required'  =>default_trans(Session::get('userOrganizationId').'/permission.validation_messages.minute_required', __('default/permission.validation_messages.minute_required')),
            'second.required' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.second_required', __('default/permission.validation_messages.second_required')),
            'hour.max' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.hour_max', __('default/permission.validation_messages.hour_max', ['attribute' => $this->max_hour]), ['attribute' => $this->max_hour]),
            'minute.max'  =>default_trans(Session::get('userOrganizationId').'/permission.validation_messages.minute_max', __('default/permission.validation_messages.minute_max', ['attribute' => $this->max_minute]), ['attribute' => $this->max_minute]),
            'second.max' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.second_max', __('default/permission.validation_messages.second_max', ['attribute' => $this->max_second]), ['attribute' => $this->max_second]),
            'hour.min' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.hour_min', __('default/permission.validation_messages.hour_min', ['attribute' => $this->min_hour]), ['attribute' => $this->min_hour]),
            'minute.min'  =>default_trans(Session::get('userOrganizationId').'/permission.validation_messages.minute_min', __('default/permission.validation_messages.minute_min', ['attribute' => $this->min_minute]), ['attribute' => $this->min_minute]),
            'second.min' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.second_min', __('default/permission.validation_messages.second_min', ['attribute' => $this->min_second]), ['attribute' => $this->min_second]),
        ];
    }
}
