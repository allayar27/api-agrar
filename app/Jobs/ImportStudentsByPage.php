<?php

namespace App\Jobs;

use App\Models\Faculty;
use App\Models\Group;
use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ImportStudentsByPage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $page;

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
        ])->get(env('HEMIS_URL') . "student-list?page={$this->page}&limit=200");
        if ($response->successful()) {
            $students = $response->json()['data']['items'];
            foreach ($students as $student) {
                DB::beginTransaction();
                try {
                    $faculty = Faculty::updateOrCreate([
                        'hemis_id' => $student['department']['id'],
                        'name' => $student['department']['name'],
                    ]);
                    $group = Group::updateOrCreate([
                        'hemis_id' => $student['group']['id'],
                        'faculty_id' => $faculty->id,
                    ],
                        [
                            'name' => $student['level']['name'] . ' ' . $student['group']['name'],
                        ]);

                    Student::updateOrCreate([
                        'hemis_id' => $student['student_id_number'],
                        'name' => $student['full_name'],
                        'firstname' => $student['first_name'],
                        'secondname' => $student['second_name'],
                        'thirdname' => $student['third_name'],
                        'group_id' => $group->id,
                        'faculty_id' => $faculty->id,
                    ]);
                    DB::commit();
                } catch (Throwable $th) {
                    DB::rollBack();
                    Log::error('Failed to import student: ' . $student['full_name'], [
                        'page' => $this->page,
                        'error' => $th->getMessage()
                    ]);
                }
            }
        } else {
            Log::error('Failed to fetch students for page: ' . $this->page, [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
        }
    }

    public function failed(Throwable $exception)
    {
        Log::error('Job failed for page: ' . $this->page, ['error' => $exception->getMessage()]);
    }
}
