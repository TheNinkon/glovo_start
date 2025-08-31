<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RiderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'contract_hours_per_week' => (int) ($this->contract_hours_per_week ?? 0),
            'city_code' => $this->city_code,
            'supervisor_id' => $this->supervisor_id,
            'supervisor_name' => $this->whenLoaded('supervisor', fn() => $this->supervisor?->name),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
