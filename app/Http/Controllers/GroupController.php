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
        $group = Group::query()->findOrFail($id);

        $data = [
            'group_id' => $group->id,
            'group_name' => $group->name,
            'hemis_id' => $group->hemis_id,
            'faculty_id' => $group->faculty_id,
            'total_students' => $group->students()->count(),
            'students' => $group->students
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ],200);

    }

    // public function reports()
    // {
    //     $day = request('day') ? request('day') : Carbon::today()->format('Y-m-d');
    //     $faculties = Faculty::with([
    //         'facultyEducationDays' => function ($query) use ($day) {
    //             $query->where('day', $day);
    //         }
    //     ])->withCount('students')->get();

    //     $results = $faculties->map(function($faculty) use ($day) {
    //         $total_students = $faculty->students_count;
    //         $educationDay = $faculty->facultyEducationDays->where('day', $day)->first();
    //         $come_students = $educationDay ? $educationDay->come_students : 0;
    //         $late_students = $educationDay ? $educationDay->late_students : 0;

    //         return [
    //             'id' => $faculty->id,
    //             'faculty' => $faculty->name,
    //             'total_students' => $total_students,
    //             'come_students' => $come_students,
    //             'late_students' => $late_students,
    //         ];
    //     });

    //     return response()->json([
    //         'success' => true,
    //         'total' => $faculties->count(),
    //         'data' => $results,
    //         'day' => $day,
    //     ]);
    // }

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
