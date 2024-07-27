<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Teacher;
use Illuminate\Http\Request;
use App\Http\Resources\TeachersResource;
use Illuminate\Pagination\LengthAwarePaginator;

class TeacherController extends Controller
{
    public function allTeachers(Request $request)
    {
        $day = request('day') ? request('day') : Carbon::today()->format('Y-m-d');
        $perPage = request('per_page', 20);
        $teachers = Teacher::whereHas('attendances' , function ($query) use  ($day) {
            $query->where('date', $day)->where('type', 'in');
        })->distinct()->get();
        $lateComers = $teachers->filter(function ($teacher) use ($day) {
            $attendance = $teacher->attendances()->where('date', $day)->where('type', 'in')->first();
            $time_in = Carbon::parse("9:00");
            $attendances = $teacher->attendances()->where('date', $day)->where('type', 'in')->first();
            return Carbon::parse($attendance->time) > $time_in;
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
            'data' => [
                'items' =>TeachersResource::collection($paginatedStudents),
                'pagination' => [
                    'total' => $paginatedStudents->total(),
                    'current_page' => $paginatedStudents->currentPage(),
                    'last_page' => $paginatedStudents->lastPage(),
                    'per_page' => $paginatedStudents->perPage(),
                    'total_pages' => $paginatedStudents->lastPage(),
                ],
            ],
           
        ]);
    }
}
