<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'business_name' => $this->business_name,
            'bio' => $this->bio,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'rejection_reason' => $this->when($this->isRejected(), $this->rejection_reason),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'applicant' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
