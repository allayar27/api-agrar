<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LateComersResource extends StudentsResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $day = request('day') ?? Carbon::today()->format('Y-m-d');
        $attendance = $this->attendances()->where('date', $day)->where('type','in')->first();
        $time_in = $this->time_in($day);
        $late_time = $attendance && $time_in ?  Carbon::parse($attendance->time)->diff(Carbon::parse($time_in))->format('%H:%I:%s') : null;
        return parent::toArray($request) + [
            'time_in' => $this->time_in($day),
            'attendance_time' => $attendance? $attendance->time : null,
            'late_time' => $late_time ? $late_time : null,
        ];
    }
}
