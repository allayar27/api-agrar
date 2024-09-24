<?php

namespace App\Http\Controllers;

use App\Models\EducationDays;
use App\Models\EmployeeEducationDays;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\DependencyInjection\ControllerArgumentValueResolverPass;

class TeacherController extends Controller
{
    public function allTeachers(Request $request): JsonResponse
    {
        $day = request('day') ? request('day') : Carbon::today()->format('Y-m-d');
        $perPage = request('per_page', 20);
        $time_in = Carbon::parse("9:00");

        $teachers = Teacher::whereHas('attendances', function ($query) use ($day, $time_in) {
            $query->where('date', $day)
                ->where('type', 'in')
                ->whereTime('time', '>', $time_in)->orderBy('time', 'DESC');
        })->with(['attendances' => function ($query) use ($day, $time_in) {
            $query->where('date', $day)
                ->where('type', 'in')
                ->whereTime('time', '>', $time_in);
        }])->distinct()->get();

        $lateComers = $teachers->map(function ($teacher) use ($time_in) {
            $attendance = $teacher->attendances->first();
            return [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'time' => $attendance->time,
                'late' => Carbon::parse($attendance->time)->diffInMinutes($time_in) . 'min'
            ];
        });
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $paginatedLateComers = new LengthAwarePaginator(
            $lateComers->forPage($currentPage, $perPage)->values(),
            $lateComers->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $paginatedLateComers->items(),
                'pagination' => [
                    'total' => $paginatedLateComers->total(),
                    'current_page' => $paginatedLateComers->currentPage(),
                    'last_page' => $paginatedLateComers->lastPage(),
                    'per_page' => $paginatedLateComers->perPage(),
                    'total_pages' => $paginatedLateComers->lastPage(),
                ],
            ],
        ]);

    }


    public function getMonthlyStatistics(Request $request): JsonResponse
    {
        $monthInput = $request->input('month', Carbon::now()->format('Y-m'));
        [$year, $month] = explode('-', $monthInput);
        $year = (int) $year;
        $month = (int) $month;

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $dailyStatistics = DB::table('education_days')
            ->select(
                'date',
                'all_teachers',
                'come_teachers',
                'late_teachers',
                DB::raw('((come_teachers / all_teachers) * 100) as come_percentage'),
                DB::raw('(((all_teachers - come_teachers) / all_teachers) * 100) as not_come_percentage'),
                DB::raw('((late_teachers / all_teachers) * 100) as late_percentage')
            )
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'Desc')
            ->get();

        return response()->json([
            'month' => $month,
            'daily_statistics' => $dailyStatistics,
        ]);

    }

    public function getEmployeesMonthly(Request $request): JsonResponse
    {
        $monthIn = $request->input('month') ?? Carbon::now()->format('Y-m');
        [$year, $month] = explode('-', $monthIn);
        $year = (int) $year;
        $month = (int) $month;

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $dailyStatistics = DB::table('employee_education_days')
            ->select(
                'date',
                'all_teachers',
                'come_teachers',
                'late_teachers',
                DB::raw('((come_teachers / all_teachers) * 100) as come_percentage'),
                DB::raw('(((all_teachers - come_teachers) / all_teachers) * 100) as not_come_percentage'),
                DB::raw('((late_teachers / all_teachers) * 100) as late_percentage')
            )
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'Desc')
            ->get();

        return response()->json([
            'month' => $month,
            'daily_statistics' => $dailyStatistics,
        ]);
    }

    public function getAllTeachers(Request $request)
    {
        $valid = $request->validate([
            'kind' => 'required|exists:teachers,kind',
        ]);

        $day = $request->input('day') ?? Carbon::today()->format('Y-m-d');
        $teachers = Teacher::query()->with('attendances')->where('kind', 'teacher')->get();
        $employees = Teacher::query()->with('attendances')->where('kind', 'employee')->get();
        $employees_count = $employees->count();
        $teachers_count = $teachers->count();
    
            $educationDay = EducationDays::query()
                ->where('date', $day)
                ->where('type', 'work_day')->first();
  
            $employeeEducationDay = EmployeeEducationDays::query()
                ->where('date', $day)
                ->where('type', 'work_day')->first();
        

        $late_teacher_comers = $educationDay->late_teachers ?? null;
        $comers_teacher  = $educationDay->come_teachers ?? null;
        $come_teacher_percent =  $comers_teacher ? ($comers_teacher/$teachers_count) * 100 : 0;
        $late_teacher_percent = $comers_teacher ? ($late_teacher_comers/$comers_teacher) * 100 : 0;

        $late_employee_comers = $employeeEducationDay->late_teachers ?? null;
        $comers_employee = $employeeEducationDay->come_teachers ?? null;
        $come_employee_percent = $comers_employee ? ($comers_employee / $employees_count) * 100 : 0;
        $late_employee_percent = $comers_employee ? ($late_employee_comers/$comers_employee) * 100 : 0;
        
        $employeeData = [
            'total_count' => $employees_count,
            'total_comers' => $comers_employee,
            'late_comers' => $late_employee_comers,
            'late_percent' => $late_employee_percent,
            'come_percent' => $come_employee_percent
        ];

        $teacherData = [
            'total_count' => $teachers_count,
            'total_comers' => $comers_teacher,
            'late_comers' => $late_teacher_comers,
            'late_percent' => $late_teacher_percent,
            'come_percent' => $come_teacher_percent
        ];

        return response()->json([
            'employee' => $employeeData,
            'teacher' => $teacherData
        ]);
    }

    public function dayliReport(Request $request): JsonResponse
    {
        $day = $request->input('day') ?? Carbon::today()->format('Y-m-d');
        $perPage = request('per_page', 20);

        $teachers = Teacher::query()->with('attendances')->where('kind', 'teacher')->get();
        $employee = Teacher::query()->with('attendances')->where('kind', 'employee')->get();

        $teacherData = $teachers->map(function ($teacher) use ($day) {
            $attendance = $teacher->attendances->where('date', $day)
                ->where('kind', 'teacher')
                ->whereNotIn('device_id', [21, 22, 23, 24])
                ->first();

            $result = [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'arrival_time' => null,
                'leave_time' => null,
                'late_time' => null,
            ];
            
            if ($attendance) {
                if ($attendance->type == 'in') {
                    $time_in = Carbon::parse('9:00');
                    $result['arrival_time'] = $attendance->time;

                    if (Carbon::parse($attendance->time) > $time_in) {
                        $late = Carbon::parse($attendance->time)->diffInMinutes($time_in);
                        $hours = intdiv($late, 60);
                        $minutes = $late % 60;
                        $result['late_time'] = sprintf('%02d:%02d:00', $hours, $minutes);
                    }

                } elseif ($attendance->type == 'out') {
                    $result['leave_time'] = $attendance->time;
                }
            }
            return $result;
        });

        $employeeData = $employee->map(function ($employee) use ($day) {
            $attendance = $employee->attendances->where('date', $day)
                ->where('kind', 'employee')
                ->whereNotIn('device_id', [21, 22, 23, 24])
                ->first();

            $result = [
                'id' => $employee->id,
                'name' => $employee->name,
                'arrival_time' => null,
                'leave_time' => null,
                'late_time' => null,
            ];

            if ($attendance) {
                if ($attendance->type == 'in') {
                    $time_in = Carbon::parse('9:00');
                    $result['arrival_time'] = $attendance->time;

                    if (Carbon::parse($attendance->time) > $time_in) {
                        $late = Carbon::parse($attendance->time)->diffInMinutes($time_in);
                        $hours = intdiv($late, 60);
                        $minutes = $late % 60;
                        $result['late_time'] = sprintf('%02d:%02d:00', $hours, $minutes);
                    }

                } elseif ($attendance->type == 'out') {
                    $result['leave_time'] = $attendance->time;
                }
            }
            return $result;
        });

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $paginatedEmployees = new LengthAwarePaginator(
            $employeeData->forPage($currentPage, $perPage)->values(),
            $employeeData->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $paginatedTeachers = new LengthAwarePaginator(
            $teacherData->forPage($currentPage, $perPage)->values(),
            $teacherData->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return response()->json([
            'success' => true,
            'teachers_data' => [
                'items' => $paginatedTeachers->items(),
                'pagination' => [
                    'total' => $paginatedTeachers->total(),
                    'current_page' => $paginatedTeachers->currentPage(),
                    'last_page' => $paginatedTeachers->lastPage(),
                    'per_page' => $paginatedTeachers->perPage(),
                    'total_pages' => $paginatedTeachers->lastPage(),
                ],
            ],
            'employees_data' => [
                'items' => $paginatedEmployees->items(),
                'pagination' => [
                    'total' => $paginatedEmployees->total(),
                    'current_page' => $paginatedEmployees->currentPage(),
                    'last_page' => $paginatedEmployees->lastPage(),
                    'per_page' => $paginatedEmployees->perPage(),
                    'total_pages' => $paginatedEmployees->lastPage(),
                ],
            ],
        ]);
    }

    // public function monthReport(Request $request) {
    //     // $valid = $request->validate([
    //     //     'kind' => 'required|exists:teachers,kind',
    //     // ]);

    //     $month = $request->input('month') ?? Carbon::now()->format('Y-m');
    //     $startOfMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();
    //     $endOfMonth = Carbon::createFromFormat('Y-m', $month)->endOfMonth()->toDateString();
    //     $perPage = $request->input('per_page', 10);

    //     $teachers = Teacher::query()->with('attendances')->where('kind', 'teacher')->get();
    //     $employee_count = $teachers->count();
    //     $employeeIds = $teachers->pluck('id');
    //     $data = $teachers->map(function ($teacher) use ($startOfMonth, $endOfMonth, $employee_count, $employeeIds) {

    //         // if ($valid['kind'] == 'teacher') {
    //             $educationDays = EducationDays::query()
    //                 ->whereBetween('date', [$startOfMonth, $endOfMonth])
    //                 ->where('type', 'work_day')->get();

    //         $attendances = $teacher->attendances->whereBetween('date', [$startOfMonth, $endOfMonth])
    //             ->where('kind', 'teacher')
    //             ->where('type', 'in')
    //             ->whereNotIn('device_id', [21, 22, 23, 24])
    //             ->groupBy('date');

    //         $attendances = $attendances->filter(function ($attendance) {
    //             return $attendance->isNotEmpty();
    //         });

    //         if ($attendances->isEmpty()) {
    //             return null;
    //         }
           

    //         $totalTeachers = $employee_count;
    //         $study_days = 0;
    //         $late_comers_count = 0;
    //         $total_comes_count = 0;
    //         $studentAttendancePerDay = [];
            
    //         foreach ($attendances as $date => $dailyAttendances) {
    //             //return $dailyAttendances;
    //             $uniqueAttendances = $dailyAttendances->whereIn('attendanceable_id', $employeeIds)
    //                 ->where('kind', 'teacher')
    //                 ->flatten();
    //                 //->unique('attendanceable_id');

    //             $totalUniqueAttendances = $uniqueAttendances->unique('attendanceable_id');
    //             $count = $totalUniqueAttendances->count();
    //             return $count;

    //             if ($uniqueAttendances->count() > 0.1 * $totalTeachers) {
    //                 $study_days++;
    //                 $late_comers = 0;
    //                 $total_comes_count += $uniqueAttendances->count();
    //                 // $count_comers = $uniqueAttendances->count();
    //                 foreach ($educationDays as $educationDay) {
    
    //                     if ($educationDay->date == $date) {
    //                         $late_comers = $educationDay->late_teachers;
    //                         $late_comers_count += $late_comers;
    //                         break;
    //                     }
    //                 }

    //             }
    //         }
            
    //         //return $study_days;
    //         return [
    //             'total_teachers' => $totalTeachers,
    //             'total_study_days' => $study_days,
    //             'late_percent' => $late_comers_count > 0 ? ($late_comers_count / $total_comes_count) * 100 : 0,
    //             'come_percent' => $study_days ? ($total_comes_count / ($study_days * $totalTeachers)) * 100 : 0,
    //         ];
    //     });

    //     return $data->filter()->values();

    // }

    


}
