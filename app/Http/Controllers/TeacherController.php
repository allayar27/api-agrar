<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

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
                'late' => Carbon::parse($attendance->time)->diffInMinutes($time_in)
            ];
        });

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $paginatedLateComers = new LengthAwarePaginator(
            $lateComers->forPage($currentPage, $perPage),
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

        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

//        if ($month < 1 || $month > 12) {
//            return response()->json(['error' => 'Invalid month provided.'], 400);
//        }

//        if ($year < 1900 || $year > Carbon::now()->year) {
//            return response()->json(['error' => 'Invalid year provided.'], 400);
//        }

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
}
