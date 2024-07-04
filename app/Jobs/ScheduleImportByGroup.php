<?php

namespace App\Jobs;

use App\Models\Building;
use App\Models\AcademicYear;
use App\Models\StudentLesson;
use App\Models\StudentSchedule;
use App\Models\StudentScheduleDay;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ScheduleImportByGroup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

   
    protected $page;

    public function __construct($page )
    {
        $this->page = $page;
    }

    public function handle(): void
    {
        $response = $this->fetchScheduleData();

        if ($response->successful()) {
            $lessons = $response->json()['data']['items'];
            foreach ($lessons as $item) {
                $this->importLesson($item);
            }
        }
    }

    private function fetchScheduleData()
    {
        return Http::withHeaders([
            'accept' => 'application/json',
            'Authorization' => 'Bearer ' . env('HEMIS_BEARER_TOKEN'),
        ])->get("https://student.karsu.uz/rest/v1/data/schedule-list?page={$this->page}&limit=200");
    }

    private function importLesson(array $item): void
    {
        DB::beginTransaction();
        try {
            $academicYear = $this->getOrCreateAcademicYear($item['educationYear']);
            $group = $this->getGroup($item['group']['id']);
            $building = $this->getOrCreateBuilding($item['auditorium']['name']);
            $studentSchedule = $this->getOrCreateStudentSchedule($item, $group->id, $academicYear->id);
            $studentScheduleDay = $this->getOrCreateStudentScheduleDay($item, $studentSchedule->id);
            $this->getOrCreateStudentLesson($item, $studentScheduleDay->id, $building->id);

            DB::commit();

            Log::info('Imported lesson: ' . $item['group']['id']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
        }
    }

    private function getOrCreateAcademicYear(array $yearData): AcademicYear
    {
        return AcademicYear::firstOrCreate(['code' => $yearData['code'], 'year' => $yearData['name']], $yearData);
    }

    private function getGroup(int $groupId)
    {
        return DB::table('groups')->where('hemis_id', $groupId)->first();
    }

    private function getOrCreateBuilding(string $name): Building
    {
        return Building::firstOrCreate(['name' => $name,'address' => $name],);
    }

    private function getOrCreateStudentSchedule(array $item, int $groupId, int $academicYearId): StudentSchedule
    {
        return StudentSchedule::updateOrCreate(
            ['startweektime' => date('Y-m-d',$item['weekStartTime']), 'endweektime' => date('Y-m-d',$item['weekEndTime']), 'group_id' => $groupId],
            ['academic_year_id' => $academicYearId]
        );
    }

    private function getOrCreateStudentScheduleDay(array $item, int $scheduleId): StudentScheduleDay
    {
        return StudentScheduleDay::updateOrCreate(
            ['day' => date('l',$item['lesson_date']), 'student_schedule_id' => $scheduleId],
            ['date' => date('Y-m-d', $item['lesson_date'])]
        );
    }

    private function getOrCreateStudentLesson(array $item, int $scheduleDayId, int $buildingId): StudentLesson
    {
        return StudentLesson::updateOrCreate(
            ['name' => $item['subject']['name'], 'student_schedule_day_id' => $scheduleDayId],
            [
                'time_in' => $item['lessonPair']['start_time'],
                'time_out' => $item['lessonPair']['end_time'],
                'building_id' => $buildingId,
            ]
        );
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Job failed for page: ' . $this->page, ['error' => $exception->getMessage()]);
    }
}