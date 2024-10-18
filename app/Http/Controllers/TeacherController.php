<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Device;
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

        $teachers = Teacher::query()->where('status','=',1)->whereHas('attendances', function ($query) use ($day, $time_in) {
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
            ->havingRaw('(come_teachers / all_teachers) >= 0.20')
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
            ->havingRaw('(come_teachers / all_teachers) >= 0.20')
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
        $teachers = Teacher::query()->where('status','=',1)->with('attendances')->where('kind', 'teacher')->get();
        $employees = Teacher::query()->where('status','=',1)->with('attendances')->where('kind', 'employee')->get();
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

        $teachers = Teacher::query()->where('status','=',1)->where('kind', 'teacher')->get();
        $employees = Teacher::query()->where('status','=',1)->where('kind', 'employee')->get();
        $employeeIds = $employees->pluck('id');
        $teacherIds = $teachers->pluck('id');

        $teacher_arrivals = Attendance::where('kind', 'teacher')->where('date', $day)
            ->where('type', 'in')
            ->whereIn('attendanceable_id', $teacherIds)
            ->distinct('attendanceable_id')
            ->get();

        $teacher_leaves = Attendance::where('kind', 'teacher')->where('date', $day)
            ->where('type', 'out')
            ->whereIn('attendanceable_id', $teacherIds)
            ->distinct('attendanceable_id')
            ->get();

        $teacherData = [];
        $teachers_time_in = Carbon::parse('8:30');

        foreach ($teacher_arrivals as $attendance) {
            $device = Device::query()->with('building')->findOrFail($attendance->device_id);
            if ($attendance && $device->building['type'] != 'residential') {
                foreach ($teachers as $teacher) {
                    $teacherId = $teacher->id;
                    if ($attendance->attendanceable_id == $teacherId) {
                        if (!isset($teacherData[$teacherId])) {
                            $teacherData[$teacherId] = [
                                'teacher_id' => $teacherId,
                                'teacher_name' => $teacher->name,
                                'arrival_time' => $attendance->time,
                                'device_id' => $attendance->device_id,
                                'leave_time' => null,
                                'late_time' => null,
                            ];
                        }

                        if (Carbon::parse($attendance->time) > $teachers_time_in) {
                            $late = Carbon::parse($attendance->time)->diffInMinutes($teachers_time_in);
                            $hours = intdiv($late, 60);
                            $minutes = $late % 60;
                            $teacherData[$teacherId]['late_time'] = sprintf('%02d:%02d:00', $hours, $minutes);
                        }
                        if ($teacher_leaves->count() > 0) {
                            foreach ($teacher_leaves as $leave) {
                                if ($leave->attendanceable_id == $teacherId) {
                                    $teacherData[$teacherId]['leave_time'] = $leave->time;
                                }
                            }
                        }
                    }
                }
            }
        }

        $arrivals = Attendance::where('kind', 'employee')->where('date', $day)
            ->where('type', 'in')
            ->whereIn('attendanceable_id', $employeeIds)
            ->distinct('attendanceable_id')
            ->get();

        $leaves = Attendance::where('kind', 'employee')->where('date', $day)
            ->where('type', 'out')
            ->whereIn('attendanceable_id', $employeeIds)
            ->distinct('attendanceable_id')
            ->get();

        $time_in = Carbon::parse('8:30');
        $data = [];


        foreach ($arrivals as $attendance) {
            $device = Device::query()->with('building')->findOrFail($attendance->device_id);
            if ($attendance && $device->building['type'] != 'residential') {
                foreach ($employees as $employee) {
                    $employeeId = $employee->id;
                    if ($attendance->attendanceable_id == $employeeId) {
                        if (!isset($data[$employeeId])) {
                            $data[$employeeId] = [
                                'employee_id' => $employeeId,
                                'employee_name' => $employee->name,
                                'arrival_time' => $attendance->time,
                                'device_id' => $attendance->device_id,
                                'leave_time' => null,
                                'late_time' => null,
                            ];
                        }

                        if (Carbon::parse($attendance->time) > $time_in) {
                            $late = Carbon::parse($attendance->time)->diffInMinutes($time_in);
                            $hours = intdiv($late, 60);
                            $minutes = $late % 60;
                            $data[$employeeId]['late_time'] = sprintf('%02d:%02d:00', $hours, $minutes);
                        }
                        if ($leaves->count() > 0) {
                            foreach ($leaves as $leave) {
                                if ($leave->attendanceable_id == $employeeId) {
                                    $data[$employeeId]['leave_time'] = $leave->time;
                                }
                            }
                        }
                    }
                }
            }
        }
        $teachersData = collect(array_values($teacherData));
        $employeesData = collect(array_values($data));

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $paginatedEmployees = new LengthAwarePaginator(
            $employeesData->forPage($currentPage, $perPage)->values(),
            $employeesData->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $paginatedTeachers = new LengthAwarePaginator(
            $teachersData->forPage($currentPage, $perPage)->values(),
            $teachersData->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

         return response()->json([
            'success' => true,
            'teachers_data' => [
                'teachers_count' => $paginatedTeachers->count(),
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
                'emplyee_count' => $paginatedEmployees->count(),
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

    public function monthReport(Request $request) {

        $month = $request->input('month') ?? Carbon::now()->format('Y-m');
        $startOfMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();
        $endOfMonth = Carbon::createFromFormat('Y-m', $month)->endOfMonth()->toDateString();

        $teachers = Teacher::query()->where('status','=',1)->with('attendances')->where('kind', 'teacher')->get();
        $employees = Teacher::query()->where('status','=',1)->with('attendances')->where('kind', 'employee')->get();

        $educationDays = EducationDays::query()
                    ->whereBetween('date', [$startOfMonth, $endOfMonth])
                    ->where('type', 'work_day')->get();

        $employeeEducationDays = EmployeeEducationDays::query()
                    ->whereBetween('date', [$startOfMonth, $endOfMonth])
                    ->where('type', 'work_day')->get();

        $study_teachers_days = 0;
        $late_teachers_comers_count = 0;
        $total_teachers_comers_count = 0;
        foreach ($educationDays as $educationDay) {
            if ($educationDay->come_teachers > 0.2 * $teachers->count()) {
                $study_teachers_days++;
                $total_teachers_comers_count += $educationDay->come_teachers;
                $late_teachers_comers_count += $educationDay->late_teachers;
            }
        }

        $study_employee_days = 0;
        $late_employee_comers_count = 0;
        $total_employee_comers_count = 0;
        foreach ($employeeEducationDays as $educationDay) {
            if ($educationDay->come_teachers > 0.2 * $employees->count()) {
                $study_employee_days++;
                $total_employee_comers_count += $educationDay->come_teachers;
                $late_employee_comers_count += $educationDay->late_teachers;
            }
        }

        $teachersData = [
            'total_teachers' => $teachers->count(),
            'total_study_days' => $study_teachers_days,
            'late' => $late_teachers_comers_count,
            'comers' => $total_teachers_comers_count,
            'late_percent' => $late_teachers_comers_count > 0 ? ($late_teachers_comers_count / $total_teachers_comers_count) * 100 : 0,
            'come_percent' => $study_teachers_days ? ($total_teachers_comers_count / ($study_teachers_days * $teachers->count())) * 100 : 0,
        ];

        $employeesData = [
            'total_employees' => $employees->count(),
            'total_study_days' => $study_employee_days,
            'late' => $late_employee_comers_count,
            'comers' => $total_employee_comers_count,
            'late_percent' => $late_employee_comers_count > 0 ? ($late_employee_comers_count / $total_employee_comers_count) * 100 : 0,
            'come_percent' => $study_employee_days ? ($total_employee_comers_count / ($study_employee_days * $employees->count())) * 100 : 0,
        ];

        return response()->json([
            'teachers' => $teachersData,
            'employees' => $employeesData
        ]);
    }

    public function monthStudyDays(Request $request)
    {
        $month = $request->input('month') ?? Carbon::now()->format('Y-m');
        $startOfMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();
        $endOfMonth = Carbon::createFromFormat('Y-m', $month)->endOfMonth()->toDateString();

        $teachers = Teacher::query()->where('status','=',1)->where('kind', 'teacher')->get();
        $employees = Teacher::query()->where('status','=',1)->where('kind', 'employee')->get();

        $educationDays = EducationDays::query()
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('type', 'work_day')->get();

        $employeeEducationDays = EmployeeEducationDays::query()
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('type', 'work_day')->get();



        $study_teachers_days = 0;
        $late_teachers_comers_count = 0;
        $total_teachers_comers_count = 0;
        $teachers_late_percent = 0;
        $teachers_come_percent = 0;
        $days = [];
        $data = [];

        $study_employees_days = 0;
        $late_employees_comers_count = 0;
        $total_employees_comers_count = 0;
        $employees_late_percent = 0;
        $employees_come_percent = 0;
        $employee_days = [];
        $employee_data = [];

        foreach ($educationDays as $educationDay) {
            if ($educationDay->come_teachers > 0.2 * $teachers->count()) {
                $days = $educationDay->date;
                $study_teachers_days++;
                $total_teachers_comers_count = $educationDay->come_teachers;
                $late_teachers_comers_count = $educationDay->late_teachers;

                $teachers_come_percent = ($total_teachers_comers_count / $teachers->count()) * 100;
                $teachers_late_percent = ($late_teachers_comers_count / $total_teachers_comers_count) * 100;

                $data[] = [
                    'days' => $days,
                    'total_comers' => $total_teachers_comers_count,
                    'late_comers' => $late_teachers_comers_count,
                    'come_percent' => $teachers_come_percent,
                    'late_percent' => $teachers_late_percent
                ];
            }


        }

        foreach ($employeeEducationDays as $educationDay) {
            if ($educationDay->come_teachers > 0.2 * $employees->count()) {
                $employee_days = $educationDay->date;
                $study_employees_days++;
                $total_employees_comers_count = $educationDay->come_teachers;
                $late_employees_comers_count = $educationDay->late_teachers;

                $employees_come_percent = ($total_employees_comers_count / $employees->count()) * 100;
                $employees_late_percent = ($late_employees_comers_count / $total_employees_comers_count) * 100;

                $employee_data[] = [
                    'days' => $employee_days,
                    'total_comers' => $total_employees_comers_count,
                    'late_comers' => $late_employees_comers_count,
                    'come_percent' => $employees_come_percent,
                    'late_percent' => $employees_late_percent
                ];
            }


        }

        $teacherItems = [
            'total_teachers' => $teachers->count(),
            'study_days' => $study_teachers_days,
            'data' => $data
        ];

        $employeeItems = [
            'total_employees' => $employees->count(),
            'study_days' => $study_employees_days,
            'data' => $employee_data
        ];

        return response()->json([
            'teacher' => $teacherItems,
            'employee' => $employeeItems
        ], 200);

    }

    public function monthReportByTeachers(Request $request)
    {
        $month = $request->input('month') ?? Carbon::now()->format('Y-m');
        $startOfMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();
        $endOfMonth = Carbon::createFromFormat('Y-m', $month)->endOfMonth()->toDateString();

        $perPage = request('per_page', 20);
        $teachers = Teacher::query()->where('status','=',1)->with('attendances')->where('kind', 'teacher')->get();
        $employees = Teacher::query()->where('status','=',1)->with('attendances')->where('kind', 'employee')->get();

        $teachers_count = $teachers->count();
        $teacherIds = $teachers->pluck('id');

        $employees_count = $employees->count();
        $emplyeeIds = $employees->pluck('id');

        $educationDays = EducationDays::query()
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('type', 'work_day')->pluck('date')->toArray();

        $employeeEducationDays = EmployeeEducationDays::query()
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('type', 'work_day')->pluck('date')->toArray();

            $study_days = 0;
            $teachersReport = [];

            $employee_study_days = 0;
            $employeesReport = [];

            $attendances = Attendance::whereBetween('date', [$startOfMonth, $endOfMonth])
                                    ->where('kind', 'teacher')
                                    ->where('type', 'in')
                                    ->whereNotIn('device_id', [21, 22, 23, 24])
                                    ->get();

        $employeeAttendances = Attendance::whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('kind', 'employee')
            ->where('type', 'in')
            ->whereNotIn('device_id', [21, 22, 23, 24])
            ->get();

            foreach ($attendances->groupBy('date') as $date => $dailyAttendances) {
                $uniqueAttendances = $dailyAttendances->whereIn('attendanceable_id', $teacherIds)
                    ->unique('attendanceable_id');

                if (in_array($date, $educationDays) && $uniqueAttendances->count() > 0.2 * $teachers_count) {
                    $study_days++;

                    foreach ($teachers as $teacher) {
                        $teacherId = $teacher->id;
                        if (!isset($teachersReport[$teacherId])) {
                            $teachersReport[$teacherId] = [
                                'total_teachers' => $teachers_count,
                                'id' => $teacher->id,
                                'name' => $teacher->name,
                                'attended_count' => 0,
                                'absents_count' => 0,
                                'late_days' => [],
                                'absent_days' => []
                            ];
                        }

                        $attendance = $teacher->attendances->where('date', $date)
                                    ->where('type', 'in')
                                    ->where('kind', 'teacher')
                                    ->first();

                        if ($attendance) {
                            $time_in = Carbon::parse('8:30');
                            $teachersReport[$teacherId]['attended_count']++;

                            if (Carbon::parse($attendance->time) > $time_in) {
                                $teachersReport[$teacherId]['late_days'][] = [
                                    'date' => $date,
                                    'late_time' => $attendance->time,
                                ];
                            }
                            else {
                                $teachersReport[$teacherId]['late_days'];
                            }

                        } else {
                            $teachersReport[$teacherId]['absents_count']++;
                            $teachersReport[$teacherId]['absent_days'][] = $date;
                        }
                    }
                }

            }

        foreach ($employeeAttendances->groupBy('date') as $date => $dailyAttendances) {
            $uniqueAttendances = $dailyAttendances->whereIn('attendanceable_id', $emplyeeIds)
                ->unique('attendanceable_id');

            if (in_array($date, $employeeEducationDays) && $uniqueAttendances->count() > 0.2 * $employees_count) {
                $employee_study_days++;

                foreach ($employees as $employee) {
                    $employeeId = $employee->id;
                    if (!isset($employeesReport[$employeeId])) {
                        $employeesReport[$employeeId] = [
                            'total_employees' => $employees_count,
                            'id' => $employee->id,
                            'name' => $employee->name,
                            'attended_count' => 0,
                            'absents_count' => 0,
                            'late_days' => [],
                            'absent_days' => []
                        ];
                    }

                    $attendance = $employee->attendances->where('date', $date)
                        ->where('type', 'in')
                        ->where('kind', 'employee')
                        ->first();

                    if ($attendance) {
                        $time_in = Carbon::parse('8:30');
                        $employeesReport[$employeeId]['attended_count']++;

                        if (Carbon::parse($attendance->time) > $time_in) {
                            $employeesReport[$employeeId]['late_days'][] = [
                                'date' => $date,
                                'late_time' => $attendance->time,
                            ];
                        } else {
                            $employeesReport[$employeeId]['late_days'];
                        }

                    } else {
                        $employeesReport[$employeeId]['absents_count']++;
                        $employeesReport[$employeeId]['absent_days'][] = $date;
                    }
                }
            }

        }


        $teachersData = collect(array_values($teachersReport));
        $employeesData = collect( array_values($employeesReport));

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $paginatedEmployees = new LengthAwarePaginator(
            $employeesData->forPage($currentPage, $perPage)->values(),
            $employeesData->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $paginatedTeachers = new LengthAwarePaginator(
            $teachersData->forPage($currentPage, $perPage)->values(),
            $teachersData->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return response()->json([
            'success' => true,
            'teachers_data' => [
                'teacher_study_days' => $study_days,
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
                'employee_study_days' => $employee_study_days,
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

}
