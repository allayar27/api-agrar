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
            Artisan::call('app:daily-student-schedule');
            $groups = Group::all();
            sleep(15);
            $today = '2024-09-16';
            $schedule = StudentSchedule::query()->whereDate('startweektime' ,'<=' , $today)
                ->whereDate('endweektime' ,'>=' , $today)->first();
            if ($schedule){
                foreach ($groups as $group) {
                    ImportSchedulesByDayJob::dispatch($group->id, $schedule->id, $today);
                }
                Log::info('Dispatched ImportSchedulesByDayJob for all groups.');
            }else{
                Log::info("Schedules not found: $today");
            }
    }
}
