<?php

namespace App\Http\Controllers;

use App\Http\Requests\NoteComersRequest;
use App\Http\Requests\Student\LateStudentsRequest;
use App\Http\Requests\Student\StudentMonthlyRequest;
use App\Imports\StudentImport;
use App\Models\Device;
use App\Models\Group;
use App\Models\GroupEducationdays;
use App\Models\Student;
use App\Models\StudentScheduleDay;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    public function allStudents(Request $request): JsonResponse
    {
        $day = $request->input('day') ?? Carbon::today();
        $query = Student::query()->where('status','=',1);
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
                'hemis_id' => $student->hemis_id,
                'faculty' => [
                    'id' => $student->faculty->id,
                    'name' => $student->faculty->name,

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
            },'scheduleDays' => function ($query) use ($day) {
            $query->where('date', $day);
            },
            'students.attendances' => function ($query) use ($day) {
                $query->where('date', $day);
            }
        ])->withCount('students')->where('faculty_id', $request->faculty_id)->take(1)->get();
//        dd($groups);
        $result = $groups->map(function ($group) use ($day) {
            $scheduleDay = $group->scheduleDays->first();
            if (!$scheduleDay) {
                return null;
            }
            dd($scheduleDay);
            $educationDay = $group->groupEducationDays->first();
            $totalStudents = $group->students_count ?? 0;
            $presentStudents = $educationDay->come_students ?? 0;

            $lateComers = [];

            foreach ($group->students as $student) {
                $attendance = $student->attendances->first();
                if ($attendance && $attendance->time > $scheduleDay->time_in) {
                    $late = Carbon::parse($attendance->time)->diffInMinutes(Carbon::parse($educationDay->time_in));
                    $lateComers[] = [
                        'id' => $student->id,
                        'name' => $student->name,
                        'time' => $attendance->time,
                        'late' => Carbon::parse($late)->format('H:i:s')
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

        $scheduledGroups = StudentScheduleDay::query()->where('date', $day)->pluck('group_id')->toArray();

        $query = Group::query()->whereIn('id',$scheduledGroups)->with([
            'students' => function ($query) use ($day) {
                $query->where('status', 1)
                    ->with(['attendances' => function ($query) use ($day) {
                        $query->where('date', $day);
                    }]);
            }
        ])->withCount('students');

        if (!empty($request->faculty_id)) {
            $query->where('faculty_id', $request->faculty_id);
        }

        $groups = $query->get();

        $result = $groups->map(function ($group) {
            $totalStudents = $group->students_count ?? 0;
            $absentStudents = [];

            foreach ($group->students as $student) {
                $attendance = $student->attendances->first();
                if (!$attendance) {
                    $absentStudents[] = [
                        'id' => $student->id,
                        'name' => $student->name,
                        'hemis_id' => $student->hemis_id,
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
        $today = request()->input('day') ?? Carbon::today()->toDateString();
        $student = Student::query()
            ->with([
                'attendances' => function ($query) {
                    $query->orderBy('date_time', 'DESC'); //->take(1);
                },
                'group',
                'faculty'
            ])
            ->where('status','=',1)->findOrFail($id);

        // $lastAttendance = $student->attendances->first();
        // if(!$lastAttendance){
        //     return response()->json([
        //         'success' => false,
        //     ],404);
        // }
        $data = [
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'group' => [
                    'id' => $student->group ? $student->group->id : null,
                    'name' => $student->group ? $student->group->name : null,
                ],
                'faculty' => $student->faculty ? $student->faculty->name : null,
                'attendances' => [

                ]
            ]
        ];

        $attendances = $student->attendances()->where('date', $today)->get();
        foreach ($attendances as $attendance) {
            $device = Device::query()->with('building')->findOrFail($attendance->device_id);
            $data['student']['attendances'][] = [
                'date' => $attendance->date,
                'time' => $attendance->time,
                'type' => $attendance->type,
                'building' => [
                    'name' => $device->building->name
                ]
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);

    }

    public function monthly(Request $request)
    {
        $month = request('month', Carbon::now()->format('Y-m'));
        $daysInMonth = Carbon::parse($month)->daysInMonth;
        $startOfMonth = Carbon::parse($month)->startOfMonth();
        $endOfMonth = Carbon::parse($month)->endOfMonth();
        // so mna jerde kosp kettim status 1 ge teng bogandi
        $allStudents = Student::where('status','=',1)->count();

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

    public function mothlyNotComers(StudentMonthlyRequest $request)
    {
        $data = $request->validated();
        $perPage = $request->input('per_page', 10);
        $month = $data['month'] ?? Carbon::now()->format('Y-m');
        $startOfMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();
        $endOfMonth = Carbon::createFromFormat('Y-m', $month)->endOfMonth()->toDateString();

        $groups = Group::with([
            'students.attendances' => function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('date_time', [$startOfMonth, $endOfMonth]);
            }
        ])->withCount('students')->where('faculty_id', $data['faculty_id'])->get();

        $results = $groups->map(function ($group) {
            $total_students = $group->students_count ?? 0;
            $absent_students = [];

            foreach ($group->students as $student) {
                $attendances = $student->attendances;
                if ($attendances->isEmpty()) {
                    $absent_students[] = [
                        'id' => $student->id,
                        'name' => $student->name,
                    ];
                }

                return [
                    'id' => $group->id,
                    'group_name' => $group->name,
                    'total_students' => $total_students,
                    'absent_students_count' => count($absent_students),
                    'absent_students' => $absent_students
                ];
            }
        })->sortBy('absent_students_count');

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $pagedResult = new LengthAwarePaginator(
            $results->forPage($currentPage, $perPage)->values(),
            $results->count(),
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

    public function mothlyLateComers(StudentMonthlyRequest $request)
    {
        $data = $request->validated();

        $month = $data['month'] ?? Carbon::now()->format('Y-m');
        $startOfMonth = Carbon::parse($month)->startOfMonth();

        $endOfMonth = Carbon::parse($month)->endOfMonth();
        $perPage = $request->input('per_page', 10);

        $groups = Group::with(['students'])->withCount('students')->where('faculty_id', $data['faculty_id'])->get();

        $result = $groups->map(function ($group) use ($startOfMonth, $endOfMonth) {
            $totalStudents = $group->students_count ?? 0;
            $lateComers = [];

            for ($date = $startOfMonth; $date->lte($endOfMonth); $date->addDay()) {
                $day = $date->format('Y-m-d');
                foreach ($group->students as $student) {
                    $attendance = $student->attendances->where('date', $day)->first();

                    if ($attendance && $attendance->time > $attendance->user->time_in($day)) {
                        $late = Carbon::parse($attendance->time)->diffInMinutes(Carbon::parse($attendance->user->time_in($day)));

                        $lateComers[] = [
                            'student_id' => $student->id,
                            'student_name' => $student->name,
                            'date' => $date->toDateString(),
                            'actual_time_in' => $attendance->time,
                            'late' => Carbon::parse($late)->format('H:i:s'),
                        ];
                    }
                }
            }

            return [
                'group_id' => $group->id,
                'group_name' => $group->name,
                'total_students' => $totalStudents,
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

    public function import(Request $request)
    {
        $file = $request->file('file');
        Excel::import(new StudentImport, $file);
        return response()->json([
            'success' => true,
        ]);
    }
}
