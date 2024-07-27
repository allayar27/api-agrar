<?php

namespace App\Http\Controllers;

use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FacultyController extends Controller
{
    public function allFaculties()
    {
        $date = request()->input('day', Carbon::now()->format('Y-m-d'));

        // Faculties ni groups va groupeducationdays bilan yuklaymiz
        $faculties = Faculty::with([
            'groups.groupeducationdays' => function ($query) use ($date) {
                $query->where('day', $date);
            }
        ])->get();

        $results = $faculties->map(function ($faculty) use ($date) {
            $totalstudents = 0;
            $comeStudents = 0;
            $lateStudents = 0;
            // $totalstudents = count($faculty->students);
            foreach ($faculty->groups as $group) {
                foreach ($group->groupeducationdays as $groupStats) {
                    // $totalstudents += $groupStats->all_students;
                    $comeStudents += $groupStats->come_students;
                    $lateStudents += $groupStats->late_students;
                }
            }

            return [
                'id' => $faculty->id,
                'faculty' => $faculty->name,
                // 'percent' => $comeStudents/$totalstudents * 100,
                // 'total_students' => $totalstudents,
                'come_students' => $comeStudents,
                'late_students' => $lateStudents,
                'groups' => $faculty->groups->count(),
            ];
        });

        return response()->json([
            'success' => true,
            'total' => $faculties->count(),
            'data' => $results,
            'day' => $date,
        ]);
    }
    public function allFaculties2()
    {
        $day = request('day') ? request('day') : Carbon::today()->format('Y-m-d');

        // Fakultetlarni talabalari va shu kun uchun statistikalar bilan yuklash
        $faculties = Faculty::with([
            'facultyEducationDays' => function ($query) use ($day) {
                $query->where('day', $day);
            }
        ])->get();

        $results = $faculties->map(function ($faculty) use ($day) {
            $total_students = $faculty->students->count();

            $educationDay = $faculty->facultyEducationDays->where('day', $day)->first();
            // $total_students = $educationDay ? $educationDay->all_students : 0;
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
