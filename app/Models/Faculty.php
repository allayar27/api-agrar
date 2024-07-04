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
}
