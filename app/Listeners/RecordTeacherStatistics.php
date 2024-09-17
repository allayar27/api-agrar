<?php

namespace App\Listeners;

use App\Events\TeacherAttendanceCreated;
use App\Helpers\ErrorAddHelper;
use App\Models\Attendance;
use App\Models\Device;
use App\Models\EducationDays;
use App\Models\EmployeeEducationDays;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

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
            $device = Device::query()->with('building')->findOrFail($attendance->device_id);
            if (!$attendance && $device !== null && $device->building['type'] != 'residential') {
                return;
            }
            $teachers = Teacher::pluck('id');

            $come_teachers_count = $this->comers('teacher', $teachers,$attendance);
            $come_employees_count = $this->comers('employee', $teachers,$attendance);

            $late_teachers_count = $this->laters('teacher', $teachers, $attendance);
            $late_employees_count = $this->laters('employee', $teachers, $attendance);

            EducationDays::query()->updateOrCreate(
                ['date' => $attendance->date],
                [
                    'all_teachers' => Teacher::query()->where('kind', 'teacher')->count(),
                    'come_teachers' => $come_teachers_count,
                    'late_teachers' => $late_teachers_count,
                ]
            );
            EmployeeEducationDays::query()->updateOrCreate(
                ['date' => $attendance->date],
                [
                    'all_teachers' => Teacher::where('kind', 'employee')->count(),
                    'come_teachers' => $come_employees_count,
                    'late_teachers' => $late_employees_count,
                ]
            );
            DB::commit();
        } catch (Throwable $th) {
            Db::rollBack();
            ErrorAddHelper::logException($th);
        }
    }

    public function laters(string $kind, $teachers, $attendance): int
    {
        $time_in = "08:30";
        $late_teachers_count = Attendance::where('kind', $kind)->where('date', $attendance->date)
            ->where('type', 'in')
            ->whereIn('attendanceable_id', $teachers)
            ->where('time', '>=', $time_in)
            ->distinct('attendanceable_id')
            ->count('attendanceable_id');
        return $late_teachers_count;
    }

    public function comers(string $kind, $teachers, $attendance): int
    {
        $come_teachers_count = Attendance::where('kind', $kind)->where('date', $attendance->date)
            ->where('type', 'in')
            ->whereIn('attendanceable_id', $teachers)
            ->distinct('attendanceable_id')
            ->count('attendanceable_id');
        return $come_teachers_count;
    }

}
