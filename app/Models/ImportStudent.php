<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportStudent extends Model
{
    use HasFactory;
    protected $fillable = [
        'hemis_id',
        'name',
        'surname',
        'PersonGroup'
    ];
}
