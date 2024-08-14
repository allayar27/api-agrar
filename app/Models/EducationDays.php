<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EducationDays extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public static function where(string $string, mixed $day)
    {
    }
}
