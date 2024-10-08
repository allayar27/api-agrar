<?php

namespace App\Http\Controllers;

use App\Actions\TelegramCallMethod;
use App\Events\StudentAttendanceCreated;
use App\Events\TeacherAttendanceCreated;
use App\Http\Requests\Attendance\StoreAttendanceRequest;
use App\Models\Attendance;
use App\Models\Building;
use App\Models\Device;
use App\Models\Doktarant;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\UsersLog;
use App\Services\TelegramUserLogs;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class AttendanceController extends Controller
{
    /**
     * @param StoreAttendanceRequest $request
     * @return JsonResponse
     */
    public function create(StoreAttendanceRequest $request)
    {
        $result = $request->validated();
        $filtered = collect($result['data'])
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
                } else {
                    $this->addNotFound(hemis_id: $id, name: $data['FirstName'] . " " . $data['LastName'], PersonGroup: $data['PersonGroup'], date_time: $data['AccessDateandTime'], device_name: $data['DeviceName']);
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
                } else {
                    $this->addNotFound(hemis_id: $id, name: $data['FirstName'] . " " . $data['LastName'], PersonGroup: $data['PersonGroup'], date_time: $data['AccessDateandTime'], device_name: $data['DeviceName']);
                }
            }
            if ($data['PersonGroup'] == 'doctoront') {
                $doctorant = Doktarant::query()->where('hemis_id', '=', $id)->first();
                if ($doctorant) {
                    $attendance = $this->createAttendance($doctorant, $data, 'other');
                } else {
                    $this->addNotFound(hemis_id: $id, name: $data['FirstName'] . " " . $data['LastName'], PersonGroup: $data['PersonGroup'], date_time: $data['AccessDateandTime'], device_name: $data['DeviceName']);
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
            Log::info($data['DeviceName'] . " " . $data['DeviceID'] . " " . $data['AccessDate']);
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
        $limit = $request->input('limit') ?? 20;
        $query = Attendance::with(['group', 'faculty', 'device.building'])
            ->where('date', $day)
            ->orderBy('date_time', 'Desc')->take($limit);
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

    public function latest(Request $request): JsonResponse
    {
        $device_name = $request->get('device_name');

        $device = Device::query()->where('name', $device_name)->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found'
            ], 404);
        }

        $building = Building::query()->find($device->building_id);

        if (!$building) {
            return response()->json([
                'success' => false,
                'message' => 'Building not found'
            ], 404);
        }

        $device_ids = $building->devices()->pluck('id')->toArray();

        $query = Attendance::query();

        if ($building->id != 3 && $building->name != 'Korpus_3') {
            $third = Building::query()->where('name', 'Korpus_3')->first();
            $devices = $third->devices()->pluck('id')->toArray();
            $latest = $query->whereDoesntHave('device', function ($q) use ($devices) {
                $q->whereIn('id', $devices);
            })->orderBy('date_time', 'desc')->first();
            if ($latest) {
                return response()->json([
                    'data' => $latest['date_time'],
                ]);
            }
        } else {
            $latest = $query->whereIn('device_id', $device_ids)->orderBy('date_time', 'desc')->first();
            if ($latest) {
                return response()->json([
                    'data' => $latest['date_time'],
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Attendance not found'
        ], 404);
    }


    public function addNotFound(?int $hemis_id, ?string $name, ?string $PersonGroup, ?string $date_time, ?string $device_name)
    {
//        $message = "Ismi: $name\n" .
//            "Hemis ID: $hemis_id\n" .
//            "Person Group: $PersonGroup\n" .
//            "Date and Time: $date_time\n" .
//            "Device Name: $device_name";
//
//        Telegram::sendMessage([
//            'chat_id' => '906372350',
//            'text'    => $message
//        ]);
        $userLog = UsersLog::query()->create([
            'hemis_id' => $hemis_id,
            'full_name' => $name,
            'PersonGroup' => $PersonGroup,
            'date_time' => $date_time, 
            'device_name' => $device_name,
        ]);

        $formattedData = $this->formatUsersLogData($userLog);
        $chat_id = config('services.telegram.chat_id');

        $url = "https://api.telegram.org/bot" . config('services.telegram.second_api_key') . "/sendMessage";

        Http::post($url, [
            'chat_id' => $chat_id,
            'text' => $formattedData,
            'parse_mode' => 'Markdown'
        ]);
    }

    private function formatUsersLogData($usersLogs)
    {
        $data = "*UsersLog Data:*\n";

        foreach ($usersLogs as $log) {
            $data .= "\n*Full Name:* {$log->full_name}\n";
            $data .= "*Hemis ID:* {$log->hemis_id}\n";
            $data .= "*Person Group:* {$log->PersonGroup}\n";
            $data .= "*Date:* {$log->date_time}\n";
            $data .= "*Device Name:* {$log->device_name}\n";
            $data .= "*Device ID:* {$log->device_id}\n";
            $data .= "------------------------\n";
        }
        return $data;
    }




    public function residential(Request $request): JsonResponse
    {
        $today = $request->input('day', now()->subDay()->format('Y-m-d'));
        $to = "22:00:00";
        $from = "04:00:00";
        $buildings = Building::query()->where('type', 'residential')->pluck('id')->toArray();
        $devices = Device::query()->whereIn('building_id', $buildings)->pluck('id')->toArray();
        $query = Attendance::query()
            ->where('kind', 'student')
            ->whereIn('device_id', $devices)
            ->where(function ($q) use ($to, $from, $today) {
                $q->where('time', '>', $to)
                    ->where('date', $today);
            })
            ->orWhere(function ($q) use ($from, $today) {
                $q->where('time', '<', $from)
                    ->where('date', $today);
            })
            ->orderBy('date_time', 'desc')
            ->take(20);
        if ($request->has('group_id')) {
            $query->where('group_id', $request->get('group_id'));
        }
        if ($request->has('faculty_id')) {
            $query->where('faculty_id', $request->get('faculty_id'));
        }
        $attendances = $query->paginate($request->input('per_page', 20));
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
            'success' => true,
            'pagination' => [
                'current_page' => $attendances->currentPage(),
                'per_page' => $attendances->perPage(),
                'last_page' => $attendances->lastPage(),
            ],
            'total' => $attendances->total(),
            'data' => $comers,
        ]);
    }
}
