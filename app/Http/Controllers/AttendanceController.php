<?php

namespace App\Http\Controllers;

use App\Events\StudentAttendanceCreated;
use App\Events\TeacherAttendanceCreated;
use App\Http\Requests\Attendance\StoreAttendanceRequest;
use App\Models\Attendance;
use App\Models\Device;
use App\Models\Doktarant;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    /**
     * @param StoreAttendanceRequest $request
     * @return JsonResponse
     */
    public function create(StoreAttendanceRequest $request)
    {
        $result = $request->validated();
        $filtered =  collect($result['data'])
            ->groupBy(function ($item) {
                return $item['EmployeeID'] . '_' . $item['AccessDate'] . '_' . $item['PersonGroup'] . '_' . $item['DeviceName'];
            })
            ->map(function ($group) {
                return $group->sortByDesc('AccessTime')->first(); // AccessTime bo'yicha oxirgi yozuvni olish
            })->values();
        foreach ($filtered as $data) {
            $id = $data['EmployeeID'];
            if ($data['PersonGroup'] != 'teacher' && $data['PersonGroup'] != 'employee') {
                $student = Student::query()->where('hemis_id', '=', $id)->first();
                if ($student) {
                    $attendance = $this->createAttendance($student, $data, 'student');
                    event(new StudentAttendanceCreated($attendance));
                }else{
                    Log::info("Student Not Found ". $data['EmployeeID']." ".$data['PersonGroup']);
                }
            } elseif ($data['PersonGroup'] == 'teacher' || $data['PersonGroup'] == 'employee') {
                $teacher = Teacher::query()->where('hemis_id', $id)->first();

                if ($teacher) {
                    if ($data['PersonGroup'] == 'teacher') {
                        $kind = 'teacher';
                    }
                    if ($data['PersonGroup'] == 'employee') {
                        $kind = 'employee';
                    }
                    $attendance = $this->createAttendance($teacher, $data, $kind);
                    event(new TeacherAttendanceCreated($attendance));
                }else{
                    Log::info("Employee Not Found ". $data['EmployeeID']." ".$data['PersonGroup']);
                }
            }
            if ($data['PersonGroup'] == 'doctoront') {
                $doctorant = Doktarant::query()->where('hemis_id', '=', $id)->first();
                if ($doctorant) {
                    $attendance = $this->createAttendance($doctorant, $data, 'other');
                }else{
                    Log::info("Doctorant  ". $data['PersonGroup']." ".$data['EmployeeID']);
                }
            }
        }
        return $this->success('Attendance created successfully', 201);
    }

    /**
     * @param $entity
     * @param $data
     * @param $kind
     * @return mixed
     */
    private function createAttendance($entity, $data, $kind)
    {
        $device = Device::query()->where('name', '=', $data['DeviceName'])->first();
        if (!$device) {
            Log::info($data['DeviceName']." ".$data['DeviceID']." ".$data['AccessDate']);
        }
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
        $query = Attendance::with(['group', 'faculty','device.building'])
            ->where('date', $day)
            ->orderBy('date_time','Desc')->take(20);
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
                'kind' => $item->kind,
                'building' => $item->device->building->name,

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
