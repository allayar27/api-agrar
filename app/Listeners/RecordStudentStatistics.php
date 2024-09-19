<?php

namespace App\Listeners;

use App\Models\Device;
use Carbon\Carbon;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\GroupDaily;
use App\Helpers\ErrorAddHelper;
use App\Models\GroupEducationdays;
use App\Events\StudentAttendanceCreated;
use App\Models\FacultyEducationDays;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
//            dd($device->building['type']);
            DB::beginTransaction();
            $time_in = "08:30:00";
            if ($time_in !== null && $device !== null && $device->building['type'] != 'residential')
            {
                $attendances = Attendance::where('kind', 'student')
                    ->where('date', $today)
                    ->where('type', 'in')
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

                $late_students_count_faculty = $attendances->where('faculty_id', $facultyId)
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

                FacultyEducationDays::updateOrCreate(
                    [
                        'faculty_id' => $facultyId,
                        'day' => $today,
                    ],
                    [
                        'all_students' => Student::where('faculty_id', $facultyId)->count(),
                        'come_students' => $come_students_count_faculty,
                        'late_students' => $late_students_count_faculty,
                    ]
                );
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            ErrorAddHelper::logException($th);
        }
    }



}
