<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleGroupNotFound extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $table = 'schedule_group_not_founds';

    protected $casts = [
        'students' => 'json'
    ];
}
