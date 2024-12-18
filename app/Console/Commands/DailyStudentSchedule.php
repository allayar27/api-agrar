<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Jobs\ScheduleImportByGroup;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class DailyStudentSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:daily-student-schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kunlik guruhlarni dars jadvallarini yuklash';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response = $this->fetchScheduleData();

        $totalPages = $response->json()['data']['pagination']['pageCount'];

        for ($page = 1; $page <= $totalPages; $page++) {
            ScheduleImportByGroup::dispatch($page);
        }
        Log::info("All groups have been scheduled:". Carbon::now());
        $this->info('All groups have been scheduled');
    }

    /**
     * @return \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
     */
    private function fetchScheduleData()
    {
        return Http::withHeaders([
            'accept' => 'application/json',
            'Authorization' => 'Bearer ' . env('HEMIS_BEARER_TOKEN'),
        ])->get(env('HEMIS_URL').'schedule-list?page=1&limit=200');
    }
}
