<?php
namespace App\Console\Commands;

use App\Jobs\ImportSchedulesByDayJob;
use App\Models\Group;
use App\Models\StudentSchedule;
use Illuminate\Console\Command;
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
    public function handle()
    {
        $groups = Group::all();
        $schedules = StudentSchedule::findOrFail(27); 
        foreach ($groups as $group) {
            ImportSchedulesByDayJob::dispatch($group->id,$schedules->id);
        }
        Log::info('Dispatched ImportSchedulesByDayJob for all groups.');
    }
}
