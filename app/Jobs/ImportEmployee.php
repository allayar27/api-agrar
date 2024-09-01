<?php

namespace App\Jobs;

use App\Models\Teacher;
use App\Models\TeacherSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImportEmployee implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    private $page;
    public function __construct($page)
    {
        $this->page = $page;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $response = Http::withHeaders([
            'accept' => 'application/json',
            'Authorization' => 'Bearer ' . env('HEMIS_BEARER_TOKEN'),
        ])->get(env('HEMIS_URL')."employee-list?type=employee&page={$this->page}&limit=10");
        if ($response->successful()) {
            $teachers = $response->json()['data']['items'];
            foreach ($teachers as $teacher) {

                DB::beginTransaction();
                try {
                    $teacherschedule = TeacherSchedule::query()->updateOrCreate([
                        'name' => $teacher['employmentStaff']['name'],
                    ]);
                    Teacher::updateOrCreate(
                        [
                            'hemis_id' =>$teacher['employee_id_number'],
                        ],
                        [
                        'name' => $teacher['full_name'],
                        'firstname' => $teacher['first_name'],
                        'secondname' => $teacher['second_name'],
                        'thirdname' => $teacher['third_name'],
                        'teacher_schedule_id' => $teacherschedule->id,
                        'kind' => 'employee',
                    ]);
                    DB::commit();
                } catch (\Throwable $th) {
                    DB::rollBack();
                    Log::error('Failed to import teacher: ' . $teacher['full_name'], [
                        'page' => $this->page,
                        'error' => $th->getMessage()
                    ]);
                }
            }
        } else {
            Log::error('Failed to fetch teachers for page: ' . $this->page, [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Job failed for page: ' . $this->page, ['error' => $exception->getMessage()]);
    }
}
