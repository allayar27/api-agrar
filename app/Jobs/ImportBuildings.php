<?php

namespace App\Jobs;

use App\Helpers\ErrorAddHelper;
use App\Models\Auditorum;
use App\Models\Building;
use App\Models\Faculty;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ImportBuildings implements ShouldQueue
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
        Log::log('time : ' ,$this->page);
//        $response = Http::withHeaders([
//            'accept' => 'application/json',
//            'Authorization' => 'Bearer ' . env('HEMIS_BEARER_TOKEN'),
//        ])->get(env('HEMIS_URL')."auditorium-list?page={$this->page}&limit=200");
//        if ($response->successful()) {
//            $buildings = $response->json()['data']['items'];
//            foreach ($buildings as $building) {
//                DB::beginTransaction();
//                try {
//                    $build = Building::updateOrCreate([
//                        'name' => $building['building']['name']
//                    ]);
//                    Auditorum::createOrFirst([
//                        'name' => $building['name'],
//                        'building_id' => $build->id,
//                    ]);
//                    DB::commit();
//                } catch (\Throwable $th) {
//                    DB::rollBack();
//                    ErrorAddHelper::logException($th);
//                }
//            }
//        } else {
//            Log::error('Failed to fetch buildings for page: ' . $this->page, [
//                'status' => $response->status(),
//                'body' => $response->body()
//            ]);
//        }
    }

//    public function failed(\Throwable $exception)
//    {
//        Log::error('Job failed for page: ' . $this->page, ['error' => $exception->getMessage()]);
//    }
}
