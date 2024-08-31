<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property int $building_id
 * @property string $type
 */
class Device extends Model
{
    use HasFactory;
    protected $fillable  = [
        'name',
        'building_id',
        'type'
    ];
}
