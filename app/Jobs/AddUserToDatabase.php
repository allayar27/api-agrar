<?php

namespace App\Jobs;

use App\Models\Doktarant;
use App\Models\ImportStudent;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddUserToDatabase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $users=  ImportStudent::query()
            ->where('id', '>=', $this->id)
            ->orderBy('id', 'asc')
            ->limit(20)
            ->get();
        foreach ($users as $user) {
            $parts = explode('/', $user->PersonGroup);
            if ($parts[1] == 'student') {
                    $student = Student::query()->where('hemis_id',$user->hemis_id )->first();
                    if ($student) {
                        $user->delete();
                    }
            }
            if ($parts[1] == 'employee' ||  $parts[1] == 'teacher')
            {
                $teacher = Teacher::query()->where('hemis_id',$user->hemis_id )->first();
                if (!$teacher){
                    Teacher::query()->create([
                        'hemis_id' => $user->hemis_id,
                        'name' => $user->name.' '.$user->surname,
                        'firstname' => $user->name,
                        'secondname' => $user->secondname,
                        'teacher_schedule_id' => 1,
                        'kind' => $parts[1],
                    ]);
                }
                $user->delete();
            }
            if ($parts[1] == 'doctorant') {
                    Doktarant::query()->create([
                        'hemis_id' => $user->hemis_id,
                        'name' => $user->name.' '.$user->surname,
                        'firstname' => $user->name,
                        'secondname' => $user->secondname,
                    ]);
                    $user->delete();
            }
        }
    }
}
