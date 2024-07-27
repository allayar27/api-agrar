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
            'type' => 'required|in:in,out',
            'kind' =>'required|in:student,teacher',
            'date' => 'required|date',
            'score' => 'required|numeric',
            'device_id' => 'required',
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
        $studentExists = \App\Models\Student::where('id', $id)->exists();
        $teacherExists = \App\Models\Teacher::where('id', $id)->exists();

        return $studentExists || $teacherExists;
    }
}
