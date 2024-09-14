<?php

namespace App\Console\Commands;

use App\Models\ImportStudent;
use Illuminate\Console\Command;

class AddUserToDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-user-to-database';

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
        $employeeFirst = ImportStudent::query()->orderBy('id', 'asc')->first();
        $employeeLast = ImportStudent::query()->orderBy('id', 'desc')->first();
        for ($i = $employeeFirst->id; $i <= $employeeLast->id; $i += 20) {
            \App\Jobs\AddUserToDatabase::dispatch($i);
        }
    }
}
