<?php

namespace App\Listeners;

use App\Models\Teacher;
use App\Models\Attendance;
use App\Models\EducationDays;
use App\Helpers\ErrorAddHelper;
use Illuminate\Support\Facades\DB;
use App\Events\TeacherAttendanceCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class RecordTeacherStatistics
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TeacherAttendanceCreated $event): void
    {
        DB::beginTransaction();
        try {
            $attendance = $event->attendance;

            if (!$attendance) {
                return;
            }
            $time_in = "09:00";
            $teachers = Teacher::pluck('id');

            $come_teachers_count = Attendance::where('kind', 'teacher')->where('date', $attendance->date)
                ->where('type', 'in')
                ->whereIn('attendanceable_id', $teachers)
                ->distinct('attendanceable_id')
                ->count('attendanceable_id');

            $late_teachers_count = Attendance::where('kind', 'teacher')->where('date', $attendance->date)
                ->where('type', 'in')
                ->whereIn('attendanceable_id', $teachers)
                ->where('time', '>=', $time_in)
                ->distinct('attendanceable_id')
                ->count('attendanceable_id');

            EducationDays::updateOrCreate(
                ['date' => $attendance->date],
                [
                    'all_teachers' => Teacher::count(),
                    'come_teachers' => $come_teachers_count,
                    'late_teachers' => $late_teachers_count,
                ]
            );
            DB::commit();
        } catch (\Throwable $th) {
            Db::rollBack();
            ErrorAddHelper::logException($th);
        }
    }

}
