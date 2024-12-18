<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherScheduleDay extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function days ()
    {
        return $this->hasMany(TeacherScheduleDay::class);
    }
}
