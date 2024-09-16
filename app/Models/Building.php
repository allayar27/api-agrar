<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Building extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function devices():HasMany
    {
        return $this->hasMany(Device::class);
    }
}
