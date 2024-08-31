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
            'id' => 'required',
            'time' => 'required',
            'kind' =>'required|in:student,teacher',
            'date' => 'required|date',
            'device_name' => 'required|string|exists:devices,name',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->idExistsInEitherTable($this->id)) {
                $validator->errors()->add('id', 'The id must exist in either students or teachers table.');
            }
        });
    }

    protected function idExistsInEitherTable($id)
    {
        $studentExists = \App\Models\Student::where('hemis_id', $id)->exists();
        $teacherExists = \App\Models\Teacher::where('hemis_id', $id)->exists();

        return $studentExists || $teacherExists;
    }
}
