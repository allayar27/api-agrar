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

//    public function withValidator($validator)
//    {
//        $validator->after(function ($validator) {
//            if (!$this->idExistsInEitherTable($this->EmployeeID)) {
//                $validator->errors()->add('id', 'The id must exist in either students or teachers table.');
//            }
//        });
//    }

//    protected function idExistsInEitherTable($id)
//    {
//        $studentExists = \App\Models\Student::where('hemis_id', $id)->exists();
//        $teacherExists = \App\Models\Teacher::where('hemis_id', $id)->exists();
//
//        return $studentExists || $teacherExists;
//    }
}
