<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'data'=>'required|array',
            'data.*.EmployeeID' => 'required|int',
            'data.*.AccessTime' => 'required|',
            'data.*.AccessDate' => 'required|',
            'data.*.PersonGroup' => 'required|string',
            'data.*.DeviceName' => 'required|string',
        ];
    }
}
