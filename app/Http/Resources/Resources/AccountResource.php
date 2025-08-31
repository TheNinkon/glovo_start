<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'courier_id' => $this->courier_id,
            'status' => $this->status,
            'date_of_delivery' => $this->date_of_delivery?->format('Y-m-d'),
            'date_of_return' => $this->date_of_return?->format('Y-m-d'),
            'active_assignment' => new AssignmentResource($this->whenLoaded('activeAssignment')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
