<?php

namespace App\Http\Controllers;

use App\Http\Requests\NoteComersRequest;
use App\Http\Requests\Student\LateStudentsRequest;
use App\Models\Building;
use App\Models\Group;
use App\Models\GroupEducationdays;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class StudentController extends Controller
{
    public function allStudents(Request $request): JsonResponse
    {
        $day = $request->input('day') ?? Carbon::today();
        $query = Student::query();
        if ($request->has("search")) {
            $search = $request->input('search');
            $query->whereAny([
                'name',
                'firstname',
                'secondname',
                'thirdname',
            ], 'LIKE', "%$search%");
        }
        if ($request->has('faculty_id')) {
            $query->where('faculty_id', $request->input('faculty_id'));
        }

        if ($request->has('group_id')) {
            $query->where('group_id', $request->input('group_id'));
        }
        $students = $query->with('group', 'faculty')->get();
        $students = $students->map(function ($student) use ($day) {
            return [
                'id' => $student->id,
                'name' => $student->name,
                'faculty' => [
                    'id' => $student->faculty->id,
                    'name' => $student->faculty->name
                ],
                'group' => [
                    'id' => $student->group->id,
                    'name' => $student->group->name
                ],
            ];
        });
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $perPage = $request->input('per_page', 20);
        $pagedResult = new LengthAwarePaginator(
            $students->forPage($currentPage, $perPage)->values(),
            $students->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return response()->json([
            'success' => true,
            'pagination' => [
                'total' => $pagedResult->total(),
                'current_page' => $pagedResult->currentPage(),
                'last_page' => $pagedResult->lastPage(),
                'per_page' => $pagedResult->perPage(),
                'total_pages' => $pagedResult->lastPage(),
            ],
            'data' => $pagedResult->items(),

        ]);
    }

    public function lateComers(LateStudentsRequest $request): JsonResponse
    {

        $day = $request->input('day', Carbon::today()->format('Y-m-d'));
        $perPage = $request->input('per_page', 10);

        $groups = Group::with([
            'groupEducationDays' => function ($query) use ($day) {
                $query->where('day', $day);
            },
            'students.attendances' => function ($query) use ($day) {
                $query->where('date', $day);
            }
        ])->withCount('students')->where('faculty_id', $request->faculty_id)->get();

        $result = $groups->map(function ($group) use ($day) {
            $educationDay = $group->groupEducationDays->first();
            $totalStudents = $group->students_count ?? 0;
            $presentStudents = $educationDay->come_students ?? 0;

            $lateComers = [];

            foreach ($group->students as $student) {
                $attendance = $student->attendances->first();
                if ($attendance && $attendance->time > $attendance->user->time_in($day)) {
                    $late = Carbon::parse($attendance->time)->diffInMinutes(Carbon::parse($attendance->user->time_in($day)));
                    $lateComers[] = [
                        'id' => $student->id,
                        'name' => $student->name,
                        'time' => $attendance->time,
                        'late' => Carbon::parse($late)->format('i:s')
                    ];
                }
            }

            return [
                'group_id' => $group->id,
                'group_name' => $group->name,
                'total_students' => $totalStudents,
                'present_students' => $presentStudents,
                'late_comers_count' => count($lateComers),
                'late_comers' => $lateComers,
            ];
        })->sortByDesc('late_comers');

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $pagedResult = new LengthAwarePaginator(
            $result->forPage($currentPage, $perPage)->values(),
            $result->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return response()->json([
            'success' => true,
            'pagination' => [
                'total' => $pagedResult->total(),
                'current_page' => $pagedResult->currentPage(),
                'last_page' => $pagedResult->lastPage(),
                'per_page' => $pagedResult->perPage(),
                'total_pages' => $pagedResult->lastPage(),
            ],
            'data' => $pagedResult->items(),

        ]);

    }

    public function noteComers(NoteComersRequest $request): JsonResponse
    {
        $day = $request->input('day', Carbon::today()->format('Y-m-d'));
        $perPage = $request->input('per_page', 10);

        $groups = Group::with([
            'students.attendances' => function ($query) use ($day) {
                $query->where('date', $day);
            }
        ])->withCount('students')->where('faculty_id', $request->faculty_id)->get();

        $result = $groups->map(function ($group) use ($day) {
            $totalStudents = $group->students_count ?? 0;
            $absentStudents = [];

            foreach ($group->students as $student) {
                $attendance = $student->attendances->first();
                if (!$attendance) {
                    $absentStudents[] = [
                        'id' => $student->id,
                        'name' => $student->name,
                    ];
                }
            }

            return [
                'group_id' => $group->id,
                'group_name' => $group->name,
                'total_students' => $totalStudents,
                'absent_students_count' => count($absentStudents),
                'absent_students' => $absentStudents,
            ];
        })->sortBy('absent_students_count');

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $pagedResult = new LengthAwarePaginator(
            $result->forPage($currentPage, $perPage)->values(),
            $result->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return response()->json([
            'success' => true,
            'pagination' => [
                'total' => $pagedResult->total(),
                'current_page' => $pagedResult->currentPage(),
                'last_page' => $pagedResult->lastPage(),
                'per_page' => $pagedResult->perPage(),
                'total_pages' => $pagedResult->lastPage(),
            ],
            'data' => $pagedResult->items(),
        ]);

    }

    public function studentAttendance(int $id): JsonResponse
    {
        $student = Student::query()
            ->with([
                'attendances' => function ($query) {
                    $query->orderBy('id', 'DESC')->take(1);
                },
                'group',
                'faculty'
            ])
            ->findOrFail($id);
        $lastAttendance = $student->attendances->first();
        $building = Building::query()->findOrFail($lastAttendance->device_id);
        return response()->json([
            'success' => true,
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'group' => [
                        'id' => $student->group ? $student->group->id : null,
                        'name' => $student->group ? $student->group->name : null,
                    ],
                    'faculty' => $student->faculty ? $student->faculty->name : null,
                    'last_attendance' => $lastAttendance ? [
                        'date' => $lastAttendance->date,
                        'time' => $lastAttendance->time,
                        'type' => $lastAttendance->type,
                        'building' => [
                            'name' => $building->name,
                        ]
                    ] : null,
                ]
            ]
        ]);


    }

    public function monthly(Request $request)
    {
        $month = request('month', Carbon::now()->format('Y-m'));
        $daysInMonth = Carbon::parse($month)->daysInMonth;
        $startOfMonth = Carbon::parse($month)->startOfMonth();
        $endOfMonth = Carbon::parse($month)->endOfMonth();

        $allStudents = Student::count();

        $statistics = collect();

        for ($day = $startOfMonth; $day->lte($endOfMonth); $day->addDay()) {
            $dayString = $day->format('Y-m-d');

            $comeStudents = GroupEducationdays::where('day', $dayString)->sum('come_students');
            $lateStudents = GroupEducationdays::where('day', $dayString)->sum('late_students');

            $statistics->push([
                'day' => $dayString,
                'all_students' => $allStudents,
                'come_students' => $comeStudents,
                'late_students' => $lateStudents,
                'come_percentage' => $allStudents > 0 ? ($comeStudents / $allStudents) * 100 : 0,
                'late_percentage' => $allStudents > 0 ? ($lateStudents / $allStudents) * 100 : 0,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);


    }
}
