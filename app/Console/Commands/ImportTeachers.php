<?php

namespace App\Console\Commands;

use App\Jobs\ImportTeachers as ImportTeachersJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportTeachers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-teachers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import teachers from Hemis Api';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response = Http::withHeaders([
            'accept' => 'application/json',
            'Authorization' => 'Bearer ' . env('HEMIS_BEARER_TOKEN'),
        ])->get(env('HEMIS_URL').'employee-list?type=all&page=1&limit=10');

        $totalPages = $response->json()['data']['pagination']['pageCount'];

        for ($page = 1; $page <= $totalPages; $page++) {
            ImportTeachersJob::dispatch($page);
        }
        $this->info('Teachers imported');

    }
    public function failed()
    {
        $this->info('');
    }

}

