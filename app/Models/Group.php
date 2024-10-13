<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'hemis_id',
        'faculty_id',
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function scheduledays()
    {
        return $this->hasMany(StudentScheduleDay::class);
    }

    public function groupeducationdays()
    {
        return $this->hasMany(GroupEducationdays::class);
    }

    public function attendances(){
        return $this->hasMany(Attendance::class);
    }

}
