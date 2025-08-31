<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'rider_id' => $this->rider_id,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'rider' => [
                'id' => $this->whenLoaded('rider', fn() => $this->rider->id),
                'name' => $this->whenLoaded('rider', fn() => $this->rider->name),
                'email' => $this->whenLoaded('rider', fn() => $this->rider->email),
            ],
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
