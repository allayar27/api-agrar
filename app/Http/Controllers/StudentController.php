<?php

namespace App\Http\Controllers;


use App\Models\Student;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;

class StudentController extends Controller
{

    public function update(UpdateStudentRequest $request, Student $student)
    {
        //
    }

    
    public function destroy(Student $student)
    {
        //
    }
}
