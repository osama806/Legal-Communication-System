<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RepresentativeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'address' => $this->address,
            'union_branch' => $this->union_branch,
            'union_number' => $this->union_number,
            "avatar" => $this->avatar,
            'agencies' => AgencyResource::collection($this->agencies),
        ];
    }
}
