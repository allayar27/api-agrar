<?php

namespace App\Jobs;

use App\Helpers\ErrorAddHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Promise\settle;
use GuzzleHttp\Client;
use App\Models\Building;
use App\Models\Auditorum;
use App\Models\AcademicYear;
use Illuminate\Bus\Queueable;
use App\Models\StudentSchedule;
use App\Models\StudentScheduleDay;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ScheduleImportByGroup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $page;

    public function __construct($page)
    {
        $this->page = $page;
    }

    public function handle(): void
    {
        $response = $this->fetchScheduleData();
        if ($response->getStatusCode() === 200) {
            $lessons = $response['data']['items'];
            foreach ($lessons as $item) {
                $this->importLesson($item);
            }
        }
    }

    private function fetchScheduleData()
    {
        $response = Http::withHeaders([
            'accept' => 'application/json',
            'Authorization' => 'Bearer ' . env('HEMIS_BEARER_TOKEN'),
        ])->get(env('HEMIS_URL') . 'schedule-list', [
                    'page' => $this->page,
                    'limit' => 200,
                ]);

        return $response;

    }

    private function importLesson(array $item): void
    {
        DB::beginTransaction();
        try {
            $academicYear = $this->getOrCreateAcademicYear($item['educationYear']);
            $this->getOrCreateStudentSchedule($item, $academicYear->id);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            ErrorAddHelper::logException($e);
            Artisan::call('queue:restart');
        }
    }

    private function getOrCreateAcademicYear(array $yearData): AcademicYear
    {
        return AcademicYear::firstOrCreate(
            ['code' => $yearData['code'], 'year' => $yearData['name']],
            $yearData
        );
    }

    private function getOrCreateStudentSchedule(array $item, int $academicYearId): StudentSchedule
    {
        return StudentSchedule::updateOrCreate(
            ['startweektime' => date('Y-m-d', $item['weekStartTime']), 'endweektime' => date('Y-m-d', $item['weekEndTime']),
            'academic_year_id' => $academicYearId]
        );
    }
    public function failed(\Throwable $exception)
    {
        Log::error('Job failed for page: ' . $this->page, ['error' => $exception->getMessage()]);
    }
}
