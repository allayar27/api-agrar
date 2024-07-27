<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\Attendance;
use App\Helpers\ErrorAddHelper;
use Illuminate\Support\Facades\DB;
use App\Events\StudentAttendanceCreated;
use App\Events\TeacherAttendanceCreated;
use App\Http\Requests\Attendance\StoreAttendanceRequest;

class AttendanceController extends Controller
{
    public function create(StoreAttendanceRequest $request)
    {
        $data = $request->validated();
        $id = $data['id'];
        DB::beginTransaction();
        try {
            if ($data['kind'] == 'student') {
                $student = Student::findOrFail($id);
                $attendance = $this->createAttendance($student, $data, 'student');
                event(new StudentAttendanceCreated($attendance));
            } elseif ($data['kind'] == 'teacher') {
                $teacher = Teacher::findOrFail($id);
                $attendance = $this->createAttendance($teacher, $data, 'teacher');
                event(new TeacherAttendanceCreated($attendance));
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            ErrorAddHelper::logException($e);
            return response()->json([
                'error' => 'An error occurred while recording attendance.',
                'details' => $e->getMessage(),
                'line' => $e->getLine(),
            ], $e->getCode() ?: 500);
        }
        return $this->success('Attendance created successfully', 201);
    }

    private function createAttendance($entity, $data, $kind)
    {
        $attendanceData = [
            'date' => $data['date'],
            'time' => $data['time'],
            'type' => $data['type'],
            'date_time' => $data['date'] . ' ' . $data['time'],
            'kind' => $kind,
            'device_id' => $data['device_id'],
        ];

        if ($kind === 'student' && $entity->group && $entity->group->faculty) {
            $attendanceData['group_id'] = $entity->group->id;
            $attendanceData['faculty_id'] = $entity->group->faculty->id;
        }

        return $entity->attendances()->create($attendanceData);
    }



}
