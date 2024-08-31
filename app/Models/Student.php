<?php

namespace App\Models;

use Carbon\Carbon;
use App\Helpers\ErrorAddHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property string $name
 * @property string $firstname
 * @property string $secondname
 * @property string $thirdname
 * @property Group $group_id
 * @property Faculty $faculty_id
 */
class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'hemis_id',
        'name',
        'firstname',
        'secondname',
        'thirdname',
        'group_id',
        'faculty_id'
    ];

    public function attendances()
    {
        return $this->morphMany(Attendance::class, 'attendanceable');
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }


    public function time_in($day)
    {
        $day = $day ? Carbon::parse($day) : Carbon::today();
        try {
            $scheduleDay = StudentScheduleDay::where('group_id', $this->group_id)
                ->where('date', $day->toDateString())
                ->first();

            if (!$scheduleDay) {
                $scheduleDay = StudentScheduleDay::where('group_id', $this->group_id)
                    ->where('day', strtolower($day->format('l')))
                    ->latest('date')
                    ->first();
            }
            return $scheduleDay ? $scheduleDay->time_in : null;
        } catch (\Exception $e) {
            ErrorAddHelper::logException($e);
            return false;
        }
    }
}
