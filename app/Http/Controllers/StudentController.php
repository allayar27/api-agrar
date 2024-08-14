<?php

namespace App\Http\Controllers;

use App\Http\Requests\NoteComersRequest;
use App\Http\Resources\LateComersResource;
use App\Http\Resources\StudentsResource;
use App\Models\Attendance;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class StudentController extends Controller
{
    public function allStudents(Request $request): JsonResponse
    {
        $query = Student::query();
        if ($request->has("search")) {
            $search = $request->input('search');
            $query->whereAny([
                'name',
                'firstname',
                'secondname',
                'thirdname',
            ], 'LIKE', "%$search%")->paginate($request->input('per_page', 20));
        }
        if ($request->has('faculty_id')) {
            $query->where('faculty_id', $request->input('faculty_id'));
        }

        if ($request->has('group_id')) {
            $query->where('group_id', $request->input('group_id'));
        }
        $students = $query->paginate($request->input('per_page', 20));
        return response()->json([
            'success' => true,
            'total' => $students->total(),
            'total_pages' => $students->lastPage(),
            'curr_page' => $students->currentPage(),
            'per_page' => $students->perPage(),
            'students' => StudentsResource::collection($students)
        ]);
    }

    public function lateComers(Request $request): JsonResponse
    {
        $day = $request->input('day', Carbon::today()->format('Y-m-d'));
        $perPage = $request->input('per_page', 10);

        $students = Student::whereHas('attendances', function ($query) use ($day) {
            $query->where('date', $day)->where('type', 'in');
        })->distinct()->get();
        if ($request->has('faculty_id')) {
            $students = $students->where('faculty_id', $request->input('faculty_id'));
        }
        if ($request->has('group_id')) {
            $students = $students->where('group_id', $request->input('group_id'));
        }
        $lateComers = $students->filter(function ($student) use ($day) {
            $time_in = $student->time_in($day);
            if ($time_in) {
                $attendance = Attendance::where('attendanceable_id', $student->id)
                    ->where('date', $day)
                    ->where('type', 'in')
                    ->first();
                return $attendance->time > $time_in;
            }
            return false;
        });

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $paginatedStudents = new LengthAwarePaginator(
            $lateComers->forPage($currentPage, $perPage),
            $lateComers->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return response()->json([
            'success' => true,
            'total' => $paginatedStudents->total(),
            'total_pages' => $paginatedStudents->lastPage(),
            'curr_page' => $paginatedStudents->currentPage(),
            'per_page' => $paginatedStudents->perPage(),
            'data' => LateComersResource::collection($paginatedStudents)
        ]);
    }

    public function noteComers(NoteComersRequest $request): JsonResponse
    {
        $day = $request->input('day', Carbon::today()->format('Y-m-d'));

        $studentsQuery = Student::query();

        if ($request->has('faculty_id')) {
            $studentsQuery->where('faculty_id', $request->input('faculty_id'));
        }

        if ($request->has('group_id')) {
            $studentsQuery->where('group_id', $request->input('group_id'));
        }

        $students = $studentsQuery->pluck('id');

        $comestudents = Attendance::where('date', $day)
            ->where('type', 'in')
            ->where('kind', 'student')
            ->distinct()
            ->pluck('attendanceable_id');

        $absentstuden = $students->diff($comestudents);

        $absentstudens = Student::query()->whereIn('id', $absentstuden)
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'total' => $absentstudens->total(),
            'total_pages' => $absentstudens->lastPage(),
            'curr_page' => $absentstudens->currentPage(),
            'per_page' => $absentstudens->perPage(),
            'data' => StudentsResource::collection($absentstudens)
        ]);
    }
}
