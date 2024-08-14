<?php

namespace App\Http\Controllers;

use App\Events\StudentAttendanceCreated;
use App\Events\TeacherAttendanceCreated;
use App\Helpers\ErrorAddHelper;
use App\Http\Requests\Attendance\StoreAttendanceRequest;
use App\Http\Resources\LastAttendanceResource;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function create(StoreAttendanceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $id = $data['id'];
        DB::beginTransaction();
        try {
            if ($data['kind'] == 'student') {
                $student = Student::query()->findOrFail($id);
                $attendance = $this->createAttendance($student, $data, 'student');
                event(new StudentAttendanceCreated($attendance));
            } elseif ($data['kind'] == 'teacher') {
                $teacher = Teacher::query()->findOrFail($id);
                $attendance = $this->createAttendance($teacher, $data, 'teacher');
                event(new TeacherAttendanceCreated($attendance));
            }
            DB::commit();
        } catch (Exception $e) {
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


    public function lastComers(Request $request):JsonResponse
    {
        $day = $request->get('day') ?? Carbon::today();
        $query = Attendance::query()->where('date',$day)->orderBy('time','DESC');
        if($request->has('group_id')){
            $query->where('group_id',$request->get('group_id'));
        }
        if($request->has('faculty_id')){
            $query->where('faculty_id',$request->get('faculty_id'));
        }
        $query = $query->get();
        return response()->json([
            'total' => $query->count(),
            'data' => LastAttendanceResource::collection($query)
        ]);
    }
}
