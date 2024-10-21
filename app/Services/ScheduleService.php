<?php

namespace App\Services;

use App\Models\ScheduleGroupNotFound;

class ScheduleService
{

    /**
     * @param string $day
     * @param int $groupId
     * @return void
     */
    public static function addNotFoundScheduleById(string $day, int $groupId): void
    {
        ScheduleGroupNotFound::query()->firstOrCreate([
            'day' => $day,
            'group_id' => $groupId,
            'counter' => 0
        ]);
    }

    public static function addNotFoundScheduleByStudentId(string $day, int $groupId, int $studentId): void
    {
        $scheduleGroup = ScheduleGroupNotFound::query()->firstOrCreate(
            ['day' => $day, 'group_id' => $groupId]
        );

        $students = $scheduleGroup->students ? $scheduleGroup->students : [];

        if (!in_array($studentId, $students)) {
            $students[] = $studentId;
        }
        $scheduleGroup->students = $students;
        $scheduleGroup->counter  += 1;
        $scheduleGroup->save();
    }


}
