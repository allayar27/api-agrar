<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupEducationdays extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
