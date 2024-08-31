<?php

namespace App\Http\Controllers;

use App\Events\StudentAttendanceCreated;
use App\Events\TeacherAttendanceCreated;
use App\Helpers\ErrorAddHelper;
use App\Http\Requests\Attendance\StoreAttendanceRequest;
use App\Models\Attendance;
use App\Models\Device;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    public function create(StoreAttendanceRequest $request)
    {
        $result = $request->validated();

        Log::info('info' , $result['data']);

        DB::beginTransaction();

        foreach ($result['data'] as $data) {
            $id = $data['EmployeeID'];
            try {
                if ($data['PersonGroup'] == 'student') {
                    Log::info('info' , $data['PersonGroup']);
//                    $student = Student::query()->where('hemis_id', '=', $id)->first();
//                    $attendance = $this->createAttendance($student, $data, 'student');
//                    event(new StudentAttendanceCreated($attendance));
                } elseif ($data['PersonGroup'] == 'teacher' || $data['PersonGroup'] == 'employee') {
                    Log::info('info' , $data['PersonGroup']);
//                    $teacher = Teacher::query()->where('hemis_id', $id)->first();
//                    $attendance = $this->createAttendance($teacher, $data, 'teacher');
//                    event(new TeacherAttendanceCreated($attendance));
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
        }

        return $this->success('Attendance created successfully', 201);
    }

    private function createAttendance($entity, $data, $kind)
    {
        $device = Device::query()->where('name', '=', $data['DeviceName'])->first();
        $attendanceData = [
            'date' => $data['AccessDate'],
            'time' => $data['AccessTime'],
            'type' => $device->type,
            'date_time' => $data['AccessDate'] . ' ' . $data['AccessTime'],
            'kind' => $kind,
            'device_id' => $device->id,
        ];

        if ($kind === 'student' && $entity->group && $entity->group->faculty) {
            $attendanceData['group_id'] = $entity->group->id;
            $attendanceData['faculty_id'] = $entity->group->faculty->id;
        }

        return $entity->attendances()->create($attendanceData);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function lastComers(Request $request): JsonResponse
    {
        $day = $request->get('day') ?? Carbon::today();
        $query = Attendance::with(['group', 'faculty'])
            ->where('date', $day)
            ->latest()->take(20);
        if ($request->has('group_id')) {
            $query->where('group_id', $request->get('group_id'));
        }
        if ($request->has('faculty_id')) {
            $query->where('faculty_id', $request->get('faculty_id'));
        }
        $attendances = $query->get();
        $comers = $attendances->map(function ($item) {
            $user = $item->user;
            $result = [
                'id' => $user->id,
                'name' => $user->name,
                'date' => $item->date,
                'time' => $item->time,
                'type' => $item->type,
            ];
            if ($item->kind == 'student') {
                $result['group'] = [
                    'id' => $item->group->id ?? null,
                    'name' => $item->group->name ?? null,
                ];
                $result['faculty'] = [
                    'id' => $item->faculty->id ?? null,
                    'name' => $item->faculty->name ?? null,
                ];
            }

            return $result;
        });
        return response()->json([
            'total' => $comers->count(),
            'data' => $comers,
        ]);
    }

    public function latest(): JsonResponse
    {
        $latest = Attendance::query()->orderBy('id', 'desc')->take(1)->first();
        return response()->json([
            'data' => $latest['date_time']
        ]);
    }
}
