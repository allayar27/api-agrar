<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentsResource extends JsonResource
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
            'name' => $this->name,
            'group' => [
                'id' => $this->group->id,
                'name' => $this->group->name,
            ],
            'faculty' => [
                'id' => $this->faculty->id,
                'name' => $this->faculty->name,
            ],
           
        ];
    }
}
