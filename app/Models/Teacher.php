<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function attendances() {
        return $this->morphMany(Attendance::class, 'attendanceable');
    }

    public function schedule() {
        return $this->belongsTo(TeacherSchedule::class, 'teacher_schedule_id');
    }
}
