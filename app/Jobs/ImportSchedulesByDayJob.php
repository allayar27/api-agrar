<?php
namespace App\Jobs;

use App\Helpers\ErrorAddHelper;
use Carbon\Carbon;
use App\Models\Group;
use App\Models\Building;
use App\Models\Auditorum;
use App\Models\AcademicYear;
use Illuminate\Bus\Queueable;
use App\Models\StudentSchedule;
use App\Models\StudentScheduleDay;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ImportSchedulesByDayJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $groupId;
    private $scheduleId;

    public function __construct($groupId, $scheduleId)
    {
        $this->groupId = $groupId;
        $this->scheduleId = $scheduleId;
    }

    public function handle(): void
    {
        try {
            $group = Group::findOrFail($this->groupId);
            $schedule = StudentSchedule::findOrFail($this->scheduleId);
            $dates = $this->scheduletodays($schedule);
            foreach ($dates as $date) {
                $day = $date['day'];
                $day = Carbon::parse($day);
                $start = Carbon::parse($day)->startOfDay();
                $end = Carbon::parse($day)->endOfDay();
                $starttime = $start->timestamp;
                $endtime = $end->timestamp;
                $this->fetchScheduleData($group->hemis_id, $starttime, $endtime, $schedule);
            }
        } catch (\Throwable $th) {
            ErrorAddHelper::logException($th);
        }
    }

    private function fetchScheduleData($groupId, $startOfDay, $endOfDay, $schedule)
    {
        $response = Http::withHeaders([
            'accept' => 'application/json',
            'Authorization' => 'Bearer ' . env('HEMIS_BEARER_TOKEN'),
        ])->get(env('HEMIS_URL')."schedule-list?page=1&limit=200&_group={$groupId}&lesson_date_from={$startOfDay}&lesson_date_to={$endOfDay}");

        if ($response->successful()) {
            $lessons = $response->json()['data']['items'];
            if (count($lessons) > 0) {
                $lessons = collect($lessons)->sortBy('lessonPair.start_time')->toArray();
                $last = end($lessons);
                StudentScheduleDay::updateOrCreate([
                    'student_schedule_id' => $schedule->id,
                    'time_in' => $lessons[0]['lessonPair']['start_time'],
                    'time_out' => $last['lessonPair']['end_time'],
                    'group_id' => $this->groupId,
                    'day' => Carbon::createFromTimestamp($startOfDay)->format('l'),
                    'date' => Carbon::createFromTimestamp($startOfDay)->format('Y-m-d'),
                    'enter_building_id' => $this->getOrCreateBuilding($lessons[0]['auditorium']['name'])->id,
                ]);
            } else {
                Log::info('No lessons found for group ID: ' . $groupId . 'on ' . Carbon::createFromTimestamp($startOfDay)->format('Y-m-d'));
            }
        }
    }

    private function scheduletodays($schedule): array
    {
        $weekstarttime = $schedule->startweektime;
        $weekendtime = $schedule->endweektime;

        $startDate = Carbon::parse($weekstarttime)->startOfDay();
        $endDate = Carbon::parse($weekendtime)->endOfDay();
        $dates = [];
        while ($startDate->lessThanOrEqualTo($endDate)) {
            $dates[] = [
                'day' => $startDate->copy()->toDateString(),
            ];
            $startDate->addDay();
        }
        Log::info('DAtes: ' . count($dates) . 'days');
        return $dates;
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Job failed for group ID: ' . $this->groupId, ['error' => $exception->getMessage()]);
    }
    private function getOrCreateBuilding(string $name): Building
    {
        $buildId = Auditorum::firstOrCreate(['name' => $name]);
        return Building::find($buildId->building_id);
    }
}
