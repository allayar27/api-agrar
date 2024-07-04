<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ImportStudentsByPage;
use Illuminate\Support\Facades\Http;

class ImportStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-students';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response = Http::withHeaders([
            'accept' => 'application/json',
            'Authorization' => 'Bearer ' . env('HEMIS_BEARER_TOKEN'),
        ])->get('https://student.karsu.uz/rest/v1/data/student-list?page=1&limit=200');

        $totalPages = $response->json()['data']['pagination']['pageCount'];

        for ($page = 1; $page <= $totalPages; $page++) {
            ImportStudentsByPage::dispatch($page)->onQueue('import-students');
        }
        $this->info('Students imported');
    }
}
