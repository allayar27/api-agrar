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
     * @param int $groupId
     * @param int $scheduleId
     * @param string $day
     */
    public function __construct(int $groupId, int $scheduleId, string $day)
    {
        $this->groupId = $groupId;
        $this->scheduleId = $scheduleId;
        $this->day = $day;
    }

    public function handle(): void
    {
        try {
            $group = Group::findOrFail($this->groupId);

            $day = Carbon::parse($this->day);
            $startTime = $day->startOfDay()->timestamp;
            $endTime = $day->endOfDay()->timestamp;

            $this->fetchScheduleData($group->hemis_id, $startTime, $endTime, $this->scheduleId);
        } catch (Throwable $th) {
            ErrorAddHelper::logException($th);
        }
    }

    /**
     * Dars jadvalini olish va saqlash
     *
     * @param int|null $groupId
     * @param int $startOfDay
     * @param int $endOfDay
     * @param int $scheduleId
     * @return void
     */
    private function fetchScheduleData(?int $groupId, int $startOfDay, int $endOfDay, int $scheduleId): void
    {
        $response = Http::withHeaders([
            'accept' => 'application/json',
            'Authorization' => 'Bearer ' . env('HEMIS_BEARER_TOKEN'),
        ])->get(env('HEMIS_URL') . "schedule-list?page=1&limit=200&_group={$groupId}&lesson_date_from={$startOfDay}&lesson_date_to={$endOfDay}");

        if ($response->successful()) {
            $lessons = $response->json()['data']['items'] ?? [];

            if (!empty($lessons)) {
                $result = $this->getFirstAndLastElement($lessons);
                if (!empty($result)) {
                    $firstLesson = reset($result);
                    $lastLesson = end($result);

                    $lessonDate = $firstLesson['lesson_date'] ?? null;

                    if ($lessonDate) {
                        StudentScheduleDay::updateOrCreate([
                            'student_schedule_id' => $scheduleId,
                            'group_id' => $this->groupId,
                            'time_in' => $firstLesson['lessonPair']['start_time'] ?? null,
                            'time_out' => $lastLesson['lessonPair']['end_time'] ?? null,
                            'day' => Carbon::parse($lessonDate)->format('l'),
                            'date' => Carbon::parse($lessonDate)->format('Y-m-d'),
                        ]);
                        Log::info($lessonDate);
                    } else {
                        Log::warning("lesson_date is missing for group ID: {$groupId}");
                    }
                }
            } else {
                ScheduleService::addNotFoundScheduleById($this->day, $this->groupId);
                Log::info("No lessons found for group ID: {$groupId} on " . Carbon::createFromTimestamp($startOfDay)->format('Y-m-d'));
            }
        } else {
            Log::error("Failed to fetch schedule for group ID: {$groupId}");
        }
    }

    /**
     * Jadvaldagi birinchi va oxirgi elementni olish
     *
     * @param array $data
     * @return array
     */
    private function getFirstAndLastElement(array $data): array
    {
        usort($data, function ($a, $b) {
            return strtotime($a['lessonPair']['start_time']) - strtotime($b['lessonPair']['start_time']);
        });
        return $data;
    }

    /**
     * Xato bo'lganda log yozish
     *
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        Log::error("Job failed for group ID: {$this->groupId}", ['error' => $exception->getMessage()]);
    }
}
