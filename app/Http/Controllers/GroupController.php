<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Group;
use App\Models\Faculty;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\StudentScheduleDay;
use App\Http\Requests\GroupsGetRequest;
use App\Models\Attendance;
use App\Models\Building;
use App\Models\Device;
use Illuminate\Pagination\LengthAwarePaginator;

class GroupController extends Controller
{
    public function allGroups(GroupsGetRequest $request):JsonResponse
    {
        $data = $request->validated();
        $day = request('day') ? request('day') : Carbon::today()->format('Y-m-d');
        $perPage = request('per_page', 10);

        $faculty = Faculty::with(['groups' => function ($query) use ($day) {
            $query->with(['groupEducationDays' => function ($query) use ($day) {
                $query->where('day', $day);
            }])->withCount('students');
        }])->findOrFail($data['faculty_id']);

        $groupsData = $faculty->groups->map(function ($group) use ($day) {
            $groupEducationDay = $group->groupEducationDays->where('day', $day)->first();
            return [
                'group_id' => $group->id,
                'group_name' => $group->name,
                'total_students' => $group->students_count,
                'percent' => $groupEducationDay ? $groupEducationDay->come_students / $groupEducationDay->all_students * 100 : 0,
                'come_students' => $groupEducationDay ? $groupEducationDay->come_students : 0,
                'late_students' => $groupEducationDay ? $groupEducationDay->late_students : 0,
            ];
        })->sortByDesc('percent');
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $paginatedGroups = new LengthAwarePaginator(
            $groupsData->forPage($currentPage, $perPage)->values(),
            $groupsData->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return response()->json([
            'total' => $paginatedGroups->total(),
            'per_page' => $paginatedGroups->perPage(),
            'current_page' => $paginatedGroups->currentPage(),
            'last_page' => $paginatedGroups->lastPage(),
            'data' => $paginatedGroups->items(),
        ]);
    }

    public function getGroupById($id)
    {
        $today = request()->input('day', Carbon::today()->format('Y-m-d'));
        $group = Group::query()->with([
            'students' => function ($query) use ($today) {
                $query->with(['attendances' => function ($query) use ($today) {
                    $query->whereDate('date', $today);
                }]);
            }
        ])->withCount('students')->findOrFail($id);
        
        $data = $group->students->map(function ($student) use ($group, $today) {

            $attendance = $student->attendances()->whereDate('date', $today)->first();

            $result = [
                'group_id' => $group->id,
                'group_name' => $group->name,
                'hemis_id' => $group->hemis_id,
                'faculty_id' => $group->faculty_id,
                'total_students' => $group->students_count,
                'student_id' => $student->id,
                'student_name' => $student->name,
                'arrival_time' => null,
                'late_time' => null,
                'device_id' => null
            ];
            
            if ($attendance) {
                if ($attendance->device_id != 4 && $attendance->device_id != 5) {
                    if ($attendance->type == 'in') {
                        $result['arrival_time'] = $attendance->time;
                            if ($attendance && $attendance->time > $attendance->user->time_in($today)) {
                                $late = Carbon::parse($attendance->time)->diffInMinutes(Carbon::parse($attendance->user->time_in($today)));
                                    $result['late_time'] = Carbon::parse($late)->format('i:s');
                            }
                        }
                }
                $result['leave_time'] = $attendance->time;
            }
            return $result;
            
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
        
    }

    public function monthReport(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
            'faculty_id' => 'required|exists:faculties,id',
        ]);

        $month = $request->input('month') ?? Carbon::now()->format('Y-m');
        $startOfMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();
        $endOfMonth = Carbon::createFromFormat('Y-m', $month)->endOfMonth()->toDateString();

        $perPage = $request->input('per_page', 10);

        $groups = Group::query()->with(['groupeducationdays' => function ($query) use ($startOfMonth, $endOfMonth) {
                        $query->whereBetween('day', [$startOfMonth, $endOfMonth]);
                    },
                    'attendances' => function ($query) use ($startOfMonth, $endOfMonth) {
                        $query->whereBetween('date', [$startOfMonth, $endOfMonth]);
                    }
        ])->withCount('students')->where('faculty_id', $request['faculty_id'])->get();

        $groupsData = $groups->map(function ($group) use ($startOfMonth, $endOfMonth) {
            $groupEducationDays = $group->groupeducationdays->whereBetween('day', [$startOfMonth, $endOfMonth]);
            $studentIds = $group->students()->pluck('id');
            
            $attendances = $group->attendances->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->where('kind', 'student')
                ->where('type', 'in')
                ->groupBy('date');

            $totalStudents = $group->students_count;
            $study_days = 0;
            $late_comers_count = 0;
            $total_comes_count = null;
            $studentAttendancePerDay = [];
        foreach ($attendances as $date => $dailyAttendances) {

            $uniqueAttendances = $dailyAttendances->whereIn('attendanceable_id', $studentIds)
                ->unique('attendanceable_id');

            if ($uniqueAttendances->count() > 0.2 * $totalStudents) {
                $study_days++;
                $late_comers = 0;
                $total_comes_count += $uniqueAttendances->count();
                // $count_comers = $uniqueAttendances->count();
                foreach ($groupEducationDays as $groupEducationDay) {
                    if ($groupEducationDay->day == $date) {
                        $late_comers = $groupEducationDay->late_students;
                        $late_comers_count += $late_comers; 
                        break;
                    }
                }
                
            }
        }

            
            return [
                'group_id' => $group->id,
                'group_name' => $group->name,
                'total_students' => $group->students_count,
                'total_study_days' => $study_days,
                'late_percent' => $late_comers_count > 0 ? ($late_comers_count / $total_comes_count) * 100 : 0,
                'come_percent' => $study_days ? ($total_comes_count / ($study_days * $group->students_count)) * 100 : 0,
                //'report' => $studentAttendancePerDay,
            ];
    });
 
     $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $paginatedGroups = new LengthAwarePaginator(
            $groupsData->forPage($currentPage, $perPage)->values(),
            $groupsData->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return response()->json([
            'total' => $paginatedGroups->total(),
            'per_page' => $paginatedGroups->perPage(),
            'current_page' => $paginatedGroups->currentPage(),
            'last_page' => $paginatedGroups->lastPage(),
            'data' => $paginatedGroups->items(),
        ]);
    }

    public function getMonthStudyDays(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
            'group_id' => 'required|exists:groups,id'
        ]);

        $startOfMonth = Carbon::createFromFormat('Y-m', $request['month'])->startOfMonth()->toDateString();
        $endOfMonth = Carbon::createFromFormat('Y-m', $request['month'])->endOfMonth()->toDateString();

        $groups = Group::query()
                ->with(['groupeducationdays', 'attendances', 'students'])
                ->withCount('students')
                ->where('id', $request['group_id'])->get();

        $data = $groups->map( function ($group) use ($startOfMonth, $endOfMonth) {
            $groupEducationDays = $group->groupeducationdays->whereBetween('day', [$startOfMonth, $endOfMonth]);
            $studentIds = $group->students->pluck('id');
            
            $attendances = $group->attendances->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->where('kind', 'student')
                ->where('type', 'in')
                ->groupBy('date');

            $totalStudents = $group->students_count;
            $study_days = 0;
            $late_comers_count = 0;
            $studentAttendancePerDay = [];
            foreach ($attendances as $date => $dailyAttendances) {
                $uniqueAttendances = $dailyAttendances->whereIn('attendanceable_id', $studentIds)
                    ->unique('attendanceable_id');

                if ($uniqueAttendances->count() > 0.2 * $totalStudents) {
                    $study_days++;
                    $late_comers = 0;
                    //$total_comes_count += $uniqueAttendances->count();
                    $count_comers = $uniqueAttendances->count();
                    foreach ($groupEducationDays as $groupEducationDay) {
                        if ($groupEducationDay->day == $date) {
                            $late_comers = $groupEducationDay->late_students;
                            $late_comers_count += $late_comers;
                            break;
                        }
                    }

                    $studentAttendancePerDay[] = [
                        'date' => $date,
                        'total_comers' => $count_comers,
                        'late_comers' => $late_comers,
                        'come_percent' => ($count_comers / $totalStudents) * 100,
                        'late_percent' => ($late_comers / $uniqueAttendances->count()) * 100
                    ];
                }
            }

            return [
                'group_id' => $group->id,
                'group_name' => $group->name,
                'total_students' => $totalStudents,
                'study_days' => $studentAttendancePerDay
            ];
            
        });

        return response()->json([
            'message' => 'data for total study days',
            'data' => $data
        ], 200);
    }


    public function monthReportByStudents(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
            'group_id' => 'required|exists:groups,id'
        ]);

        $startOfMonth = Carbon::createFromFormat('Y-m', $request['month'])->startOfMonth()->toDateString();
        $endOfMonth = Carbon::createFromFormat('Y-m', $request['month'])->endOfMonth()->toDateString();

        $groups = Group::query()
            ->with(['groupeducationdays', 'attendances', 'students'])
            ->withCount('students')
            ->where('id', $request['group_id'])->get();

        $result = $groups->map(function ($group) use ($startOfMonth, $endOfMonth) {
            $studentIds = $group->students->pluck('id');
            $educationDays = $group->groupeducationdays->whereBetween('day', [$startOfMonth, $endOfMonth])->pluck('day')->toArray();
            
            $totalStudents = $group->students_count;
            $study_days = 0;

            $studentReports = [];

            $attendances = $group->attendances->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->where('kind', 'student')
                ->where('type', 'in')
                ->whereNotIn('device_id', [21, 22, 23, 24])
                ->groupBy('date');

            foreach ($attendances as $date => $dailyAttendances) {
                $uniqueAttendances = $dailyAttendances->whereIn('attendanceable_id', $studentIds)
                    ->unique('attendanceable_id');
            
                if (in_array($date, $educationDays) && $uniqueAttendances->count() > 0.2 * $totalStudents) {
                    $study_days++;

                    foreach ($group->students as $student) {
                        $studentId = $student->id;

                        if (!isset($studentReports[$studentId])) {
                            $studentReports[$studentId] = [
                                'id' => $studentId,
                                'name' => $student->name,
                                'attended_count' => 0,
                                'absents_count' => 0,
                                'late_days' => [],
                                'absent_days' => []
                            ];
                        }

                        $attendance = $student->attendances->where('date', $date)->where('type', 'in')->first();
                        
                        if ($attendance) {
                            $studentReports[$studentId]['attended_count']++;
    
                            if ($attendance->time > $attendance->user->time_in($date)) {
                                //$late_minutes = Carbon::parse($attendance->time)->diffInMinutes(Carbon::parse($attendance->user->time_in($date)));
                 
                                $studentReports[$studentId]['late_days'][] = [
                                    'date' => $date,
                                    'late_time' => $attendance->time,
                                    //'late_formatted' => Carbon::parse($late_minutes)->format('i:s'),
                                ];
                            }
                        }
                        else {
                            $studentReports[$studentId]['absents_count']++;
                            $studentReports[$studentId]['absent_days'][] = $date;
                        }
                    }
                }
            }
    
            return [
                'group_name' => $group->name,
                'total_students' => $totalStudents,
                'total_study_days' => $study_days, 
                'student_reports' => array_values($studentReports),
            ];

        });

        return response()->json([
            'message' => 'month report by students',
            'data' => $result
        ], 200);
    }

//     public function dailyGroupReport(Request $request)
//     {
//         $request->validate([
//             'day' => "date_format:Y-m-d",
//             'faculty_id' =>'required|exists:faculties,id',
//         ]);

//         $day = $request->input('day') ? $request->input('day') : Carbon::today()->format('Y-m-d');
//         //return $day;
//         $faculty = Faculty::with(['groups' => function ($query) use ($day) {
//             $query->select(['id', 'name', 'hemis_id', 'faculty_id'])
//                 ->with(['groupEducationDays' => function ($query) use ($day) {
//                 $query->where('day', $day);
//             }])->withCount('students');
//         }])->findOrFail($request->faculty_id);

//         $groupsData = $faculty->groups->map(function ($group) use ($day) {
//             $groupEducationDay = $group->groupEducationDays->where('day', $day)->first();
//             return [
//                 'group_id' => $group->id,
//                 'group_name' => $group->name,
//                 'total_students' => $group->students_count,
//                 'percent' => $groupEducationDay ? $groupEducationDay->come_students / $groupEducationDay->all_students * 100 : 0,
//                 'come_students' => $groupEducationDay ? $groupEducationDay->come_students : 0,
//                 'late_students' => $groupEducationDay ? $groupEducationDay->late_students : 0,
//             ];
//         })->sortByDesc('percent');
//         return $groupsData;
// //        $currentPage = LengthAwarePaginator::resolveCurrentPage();
// //        $paginatedGroups = new LengthAwarePaginator(
// //            $groupsData->forPage($currentPage, $perPage)->values(),
// //            $groupsData->count(),
// //            $perPage,
// //            $currentPage,
// //            ['path' => $request->url(), 'query' => $request->query()]
// //        );
// //
// //        return response()->json([
// //            'total' => $paginatedGroups->total(),
// //            'per_page' => $paginatedGroups->perPage(),
// //            'current_page' => $paginatedGroups->currentPage(),
// //            'last_page' => $paginatedGroups->lastPage(),
// //            'data' => $paginatedGroups->items(),
// //        ]);




//         //$groupDetails = Group::query()->where('faculty_id', $id)->get();


//         //$query = Group::where('academic_year_id',$academic_year_id)->orderByRaw("LENGTH(name), name");
//     }

}
