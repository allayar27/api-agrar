<?php
namespace App\Console\Commands;

use App\Helpers\ErrorAddHelper;
use App\Jobs\ImportSchedulesByDayJob;
use App\Models\Group;
use App\Models\StudentSchedule;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ImportDays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-days';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle():void
    {
        try {
            Artisan::call('app:daily-student-schedule');
            $groups = Group::all();
            $today = now()->format('Y-m-d');
            $schedules = StudentSchedule::query()->whereDate('startweektime' ,'<' , $today)
                ->whereDate('endweektime' ,'>' , $today)->first();
            if ($schedules){
                foreach ($groups as $group) {
                    ImportSchedulesByDayJob::dispatch($group->id,$schedules->id);
                }
                Log::info('Dispatched ImportSchedulesByDayJob for all groups.');
            }
            Log::info('Schedules not found.');
        }catch (\Throwable $th){
            ErrorAddHelper::logException($th);
        }
    }
}
