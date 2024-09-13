<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function attendanceable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        if ($this->attendanceable_type === 'App\Models\Student') {
            return $this->belongsTo(Student::class, 'attendanceable_id');
        } elseif ($this->attendanceable_type === 'App\Models\Teacher') {
            return $this->belongsTo(Teacher::class, 'attendanceable_id');
        }
    }

    public function group()
    {
        return $this->belongsTo(Group::class);

    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function building (){
        return $this->belongsTo(Building::class);
    }


    public function device():BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

}
