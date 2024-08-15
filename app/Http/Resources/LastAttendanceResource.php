<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LastAttendanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name
            ],
            'faculty' => [
                'id' => $this->faculty->id,
                'name' => $this->faculty->name
            ],
            'time' => $this->time,
            'type' => $this->type,
            'kind' => $this->kind
        ];
    }
}
