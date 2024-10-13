<?php

namespace App\Listeners;

use App\Events\StudentAttendanceCreated;
use App\Helpers\ErrorAddHelper;
use App\Models\Attendance;
use App\Models\Device;
use App\Models\FacultyEducationDays;
use App\Models\GroupEducationdays;
use App\Models\Student;
use App\Models\StudentScheduleDay;
use App\Services\ScheduleService;
use Illuminate\Support\Facades\DB;
use Throwable;

class RecordStudentStatistics
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
    public function handle(StudentAttendanceCreated $event): void
    {
        try {
            $attendance = $event->attendance;
            $student = Student::findOrFail($attendance->user->id);
            $group = $attendance->group;
            $groupIds = $group->students()->pluck('id');
            $facultyId = $attendance->faculty_id;
            $today = $attendance->date;
            $device = Device::query()->with('building')->findOrFail($attendance->device_id);
            if ($device !== null && $device->building['type'] != 'residential') {
                return;
            }
            $schedule_day = StudentScheduleDay::query()->where('group_id', '=', $group->id)->where('date', '=', $today)->first();
            DB::beginTransaction();
            if ($schedule_day !== null) {
                $time_in = $schedule_day->time_in;
                $attendances = Attendance::query()->where('kind', '=', 'student')
                    ->where('date', '=', $today)
                    ->where('type', '=', 'in')
                    ->where(function ($query) use ($facultyId, $groupIds) {
                        $query->where('faculty_id', $facultyId)
                            ->orWhereIn('attendanceable_id', $groupIds);
                    })
                    ->get();
                $come_students_count_faculty = $attendances->where('faculty_id', $facultyId)
                    ->unique('attendanceable_id')
                    ->count();

                $come_students_count_group = $attendances->whereIn('attendanceable_id', $groupIds)
                    ->unique('attendanceable_id')
                    ->count();

                $late_students_count_faculty = $attendances->where('faculty_id', '=', $facultyId)
                    ->where('time', '>', $time_in)
                    ->unique('attendanceable_id')
                    ->count();
                $late_students_count_group = $attendances->whereIn('attendanceable_id', $groupIds)
                    ->where('time', '>', $time_in)
                    ->unique('attendanceable_id')
                    ->count();
                GroupEducationdays::updateOrCreate(
                    [
                        'group_id' => $attendance->group_id,
                        'faculty_id' => $facultyId,
                        'day' => $today,
                    ],
                    [
                        'all_students' => $groupIds->count(),
                        'come_students' => $come_students_count_group,
                        'late_students' => $late_students_count_group,
                    ]
                );
                FacultyEducationDays::query()->updateOrCreate(
                    [
                        'faculty_id' => $facultyId,
                        'day' => $today,
                    ],
                    [
                        'all_students' => Student::query()->where('faculty_id', '=', $facultyId)->count(),
                        'come_students' => $come_students_count_faculty,
                        'late_students' => $late_students_count_faculty,
                    ]
                );
            } else {
                $schedules = StudentScheduleDay::query()->where('date', $today)->count();
                if ($schedules > 10) {
                    ScheduleService::addNotFoundScheduleByStudentId(day: $today, groupId: $group->id, studentId: $student->id);
                }
            }
            DB::commit();
        } catch (Throwable $th) {
            DB::rollBack();
            ErrorAddHelper::logException($th);
        }
    }


}
