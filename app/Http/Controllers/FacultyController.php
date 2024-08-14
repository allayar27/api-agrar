<?php

namespace App\Http\Controllers;

use App\Models\Faculty;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FacultyController extends Controller
{
    public function allFaculties()
    {
        $day = request('day') ? request('day') : Carbon::today()->format('Y-m-d');

        $faculties = Faculty::with([
            'facultyEducationDays' => function ($query) use ($day) {
                $query->where('day', $day);
            }
        ])->withCount('students')->get();

        $results = $faculties->map(function ($faculty) use ($day) {
            $total_students = $faculty->students_count;
            $educationDay = $faculty->facultyEducationDays->where('day', $day)->first();
            $come_students = $educationDay ? $educationDay->come_students : 0;
            $late_students = $educationDay ? $educationDay->late_students : 0;

            return [
                'id' => $faculty->id,
                'faculty' => $faculty->name,
                'total_students' => $total_students,
                'come_students' => $come_students,
                'late_students' => $late_students,
            ];
        });

        return $this->data([
            'success' => true,
            'total' => $faculties->count(),
            'data' => $results,
            'day' => $day,
        ]);
    }


}
