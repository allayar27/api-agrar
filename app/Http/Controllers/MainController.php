<?php

namespace App\Http\Controllers;

use App\Models\EmployeeEducationDays;
use Carbon\Carbon;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Attendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\EducationDays;

class MainController extends Controller
{
    private int $all_comers = 0;
    private int $late_comers = 0;

    public function index():JsonResponse
    {
        $day = request('day') ? request('day') : Carbon::today()->format('Y-m-d');
        $students = Student::count();
        $faculties = Faculty::with([
            'facultyEducationDays' => function ($query) use ($day) {
                $query->where('day', $day);
            }
        ])->get();
        $this->calculatestudents($faculties, $day);
        $all_teachers = Teacher::query()->where('kind','teacher')->count();
        $all_employees = Teacher::query()->where('kind','employee')->count();
        $teachersStatistics = EducationDays::where('date', $day) ? EducationDays::query()->where('date', $day)->first() : null;
        $employeesStatistics = EmployeeEducationDays::query()->where('date',$day) ? EmployeeEducationDays::query()->where('date', $day)->first() : null;
        return $this->data([
            'all_students' => $students,
            'all_comers' => $this->all_comers,
            'late_comers' => $this->late_comers,
            'all_teachers' => $all_teachers,
            'all_come_teachers' => $teachersStatistics ? $teachersStatistics->come_teachers : 0,
            'late_come_teachers' => $teachersStatistics ? $teachersStatistics->late_teachers : 0,
            'all_employees' => $all_employees,
            'all_come_employees' => $employeesStatistics ? $employeesStatistics->come_teachers : 0,
            'late_come_employees' => $employeesStatistics ? $employeesStatistics->late_teachers : 0,
        ]);
    }

    private function calculatestudents($faculties, $day)
    {
        $faculties->map(function ($faculty) use ($day) {
            $educationDay = $faculty->facultyEducationDays->where('day', $day)->first();
            $this->all_comers += $educationDay ? $educationDay->come_students : 0;
            $this->late_comers += $educationDay ? $educationDay->late_students : 0;
        });
    }

     public function calculate($attendanceable_id, $time_in, $time_out,$day)
      {
         $now = Carbon::now();

         // if ($now->lessThan($time_out)) {
         //     $time_out = $now;
         // }
         $attendances = Attendance::where('attendanceable_id', $attendanceable_id)
                                   ->where('date', $day)
                                   ->where('time', '>=', $time_in)
                                   ->where('time', '<=', $time_out)
                                   ->orderBy('date_time', 'asc')
                                   ->get();

         $totalTime = 0;
         $entryTime = null;
         foreach ($attendances as $attendance) {
             $attendancetime = Carbon::parse($attendance->time);
             if ($attendance->type == 'in') {
                 if ($entryTime = null) {
                     $entryTime = $attendancetime;
                 }
             }elseif($attendance->type == 'out') {
                 $totalTime += $attendancetime->diffInMinutes($entryTime);
                 $entryTime = null;
             }
         }
         // if ($entryTime !== null) {
         //     $totalTime += $now->diffInMinutes($entryTime);
         // }

         $expectedTimeInMinutes = $time_out->diffInMinutes($time_in);
         $timeDifference = $expectedTimeInMinutes - $totalTime;
         // return  count($attendances);
         return $totalTime;

     }

     public function student() {
         $student = Student::find(1);
         $timeIn = Carbon::parse("11:30");
         $timeOut = Carbon::parse("19:00");
         $day =   request('day') ?? Carbon::today()->subDay()->format('Y-m-d');
         $totalTime = $this->calculate($student->id, $timeIn, $timeOut,$day);
         // $attendances = Attendance::where('attendanceable_id', $student->id)
         // ->where('date', $day)
         // ->where('time', '>=', $timeIn)
         // ->where('time', '<=', $timeOut)
         // ->orderBy('date_time', 'asc')
         // ->get();
         // return count($attendances);
         return response()->json([
             'success' => true,
             'data' => [
                 'time_in' => $timeIn->format('H:i:s'),
                 'time_out' => $timeOut->format('H:i:s'),
                 'total_time' => date('H:i:s', $totalTime),
             ]
         ]);
     }


}
