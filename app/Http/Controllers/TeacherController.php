<?php

namespace App\Http\Controllers;

use App\Models\EducationDays;
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
        $day = $request->input('day') ?? Carbon::today()->format('Y-m-d');
        $employees = Teacher::query()->with('attendances')->where('kind', 'teacher')->get();
        $employees_count = $employees->count();
        
        $educationDay = EducationDays::query()
                                    ->where('date', $day)
                                    ->where('type', 'work_day')->first();

        $late_comers = $educationDay->late_teachers ?? null;
        $comers  = $educationDay->come_teachers ?? null;
        $come_percent =  $comers ? ($comers/$employees_count) * 100 : 0;
        $late_percent = $comers ? ($late_comers/$comers) * 100 : 0;
            
        return response()->json([
                'total_employees' => $employees_count,
                'total_comers' => $comers,
                'late_comers' => $late_comers,
                'late_percent' => $late_percent,
                'come_percent' => $come_percent
            ]);
    }

    public function dayliReport(Request $request): JsonResponse
    {
        $day = $request->input('day') ?? Carbon::today()->format('Y-m-d');
        $perPage = request('per_page', 20);

        $teachers = Teacher::query()->with('attendances')->where('kind', 'teacher')->get();

        $data = $teachers->map(function ($teacher) use ($day) {
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
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $paginatedLateComers = new LengthAwarePaginator(
            $data->forPage($currentPage, $perPage)->values(),
            $data->count(),
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

    


}
