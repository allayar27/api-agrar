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
use App\Models\Building;
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
        $today = Carbon::today()->format('Y-m-d');
        $group = Group::query()->with([
            'students' => function ($query) use ($today) {
                $query->with(['attendances' => function ($query) use ($today) {
                    $query->whereDate('date', $today);
                }]);
            }
        ])->withCount('students')->find($id);
        
        
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
                'leave_time' => null
            ];
            
            if ($attendance && ($attendance->device_id != 4 && $attendance->device_id != 5)) {
                if ($attendance->type == 'in') {
                $result['arrival_time'] = $attendance->time;
                if ($attendance && $attendance->time > $attendance->user->time_in($today)) {
                    $late = Carbon::parse($attendance->time)->diffInMinutes(Carbon::parse($attendance->user->time_in($today)));
                        $result['late_time'] = Carbon::parse($late)->format('i:s');
                }
                }
                elseif ($attendance->type == 'out') {
                    $result['leave_time'] = $attendance->time;
                } 
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
                    }
                ])->withCount('students')->where('faculty_id', $request['faculty_id'])->get();
        
        $groupsData = $groups->map(function ($group) use ($startOfMonth, $endOfMonth) {
            $groupEducationDays = $group->groupeducationdays->whereBetween('day', [$startOfMonth, $endOfMonth]);

           if (count($groupEducationDays) > 0) {
                foreach ($groupEducationDays as $groupEducationDay) {
                     $comeStudents = $groupEducationDay->come_students;
                     $lateStudents = $groupEducationDay->late_students;
                     $come_percent = $group->students_count > 0 ? ($comeStudents / $group->students_count) * 100 : 0;
                     $late_percent = $group->students_count > 0 ? ($lateStudents / $group->students_count) * 100 : 0;
                }

                return [
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'total_students' => $group->students_count,
                    'come_students' => $comeStudents,
                    'late_students' => $lateStudents,
                    'come_percent' => $come_percent,
                    'late_percent' => $late_percent,

                ];
            }
            else {
                return [
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'total_students' => $group->students_count,
                    'come_students' => 0,
                    'late_students' => 0,
                    'come_percent' => 0,
                    'late_percent' => 0,
                ];
            }

            
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
