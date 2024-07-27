<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendancesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'time' => $this->time,
            'type' => $this->type, 
            'date' => $this->date,
        ];
    }
}
