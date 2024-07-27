<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faculty extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'hemis_id',
    ];

    public function groups():HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function  groupeducationdays()
    {
        return $this->hasMany(GroupEducationDays::class);
    }

    public function facultyEducationDays()
    {
        return $this->hasMany(FacultyEducationDays::class);
    }
}
