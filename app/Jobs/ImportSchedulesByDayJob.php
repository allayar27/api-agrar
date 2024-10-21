<?php

namespace App\Jobs;

use App\Helpers\ErrorAddHelper;
use App\Models\Group;
use App\Models\StudentScheduleDay;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ImportSchedulesByDayJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $groupId;
    private $scheduleId;

    private $day;

    /**
     * @param $groupId
     * @param $scheduleId
     * @param $day
     */
    public function __construct(
        $groupId,
        $scheduleId,
        $day,
    )
    {
        $this->groupId = $groupId;
        $this->scheduleId = $scheduleId;
        $this->day = $day;
    }

    public function handle(): void
    {
        try {
            $group = Group::query()->findOrFail($this->groupId);
            $day = $this->day;
            $day = Carbon::parse($day);
            $start = Carbon::parse($day)->startOfDay();
            $end = Carbon::parse($day)->endOfDay();
            $startTime = $start->timestamp;
            $endTime = $end->timestamp;
            $this->fetchScheduleData(groupId: $group->hemis_id, startOfDay: $startTime, endOfDay: $endTime, scheduleId: $this->scheduleId);
        } catch (Throwable $th) {
            ErrorAddHelper::logException($th);
        }
    }

    /**
     * @param int|null $groupId
     * @param string $startOfDay
     * @param string $endOfDay
     * @param int $scheduleId
     * @return void
     */
    private function fetchScheduleData(?int $groupId, string $startOfDay, string $endOfDay, int $scheduleId): void
    {
        $response = Http::withHeaders([
            'accept' => 'application/json',
            'Authorization' => 'Bearer ' . env('HEMIS_BEARER_TOKEN'),
        ])->get(env('HEMIS_URL') . "schedule-list?page=1&limit=200&_group={$groupId}&lesson_date_from={$startOfDay}&lesson_date_to={$endOfDay}");

        if ($response->successful()) {
            $lessons = $response->json()['data']['items'];
            if (count($lessons) > 0) {
                $result = $this->getFirstAndLastElement($lessons);
                if (count($result) > 0) {
                    $last = end($result);
                    StudentScheduleDay::query()->updateOrCreate([
                        'student_schedule_id' => $scheduleId,
                        'time_in' => $result[0]['lessonPair']['start_time'],
                        'time_out' => $last['lessonPair']['end_time'],
                        'group_id' => $this->groupId,
                        'day' => Carbon::parse($startOfDay)->format('l'),
                        'date' => Carbon::parse($startOfDay)->format('Y-m-d'),
                    ]);
                }

            } else {
                ScheduleService::addNotFoundScheduleById(day: $this->day, groupId: $this->groupId);
                Log::info('No lessons found for group ID: ' . $groupId . 'on ' . Carbon::createFromTimestamp($startOfDay)->format('Y-m-d'));
            }
        }
    }

    function getFirstAndLastElement(array $data): array
    {
        usort($data, function ($a, $b) {
            return strtotime($a['lessonPair']['start_time']) - strtotime($b['lessonPair']['start_time']);
        });
        return $data;
    }

    /**
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Job failed for group ID: ' . $this->groupId, ['error' => $exception->getMessage()]);
    }
}
