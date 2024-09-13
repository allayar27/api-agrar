<?php

namespace App\Console\Commands;

use App\Jobs\ImportBuildings as ImportBuildingsJob;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ImportBuildings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-buildings';

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
        $time = Carbon::now()->timezone('Asia/Tashkent')->format('Y-m-d H:i:s');
            ImportBuildingsJob::dispatch($time);
//        $response = Http::withHeaders([
//            'accept' => 'application/json',
//            'Authorization' => 'Bearer ' . env('HEMIS_BEARER_TOKEN'),
//        ])->get(env('HEMIS_URL').'auditorium-list?page=1&limit=200');
//
//        $totalPages = $response->json()['data']['pagination']['pageCount'];
//        Log::info('Total pages: '. $totalPages);
//        for ($page = 1; $page <= $totalPages; $page++) {
//        }
    }
}
