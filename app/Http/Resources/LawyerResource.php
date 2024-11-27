<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LawyerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Calculate the rank (average rating) safely
        $rank = $this->rates->isNotEmpty()
            ? $this->rates->pluck('rating')->avg()
            : 0;

        return [
            "id" => $this->id,
            "name" => $this->name,
            "email" => $this->email,
            "address" => $this->address,
            "union_branch" => $this->union_branch,
            "union_number" => $this->union_number,
            "affiliation_date" => $this->affiliation_date,
            "specializations" => $this->specializations->pluck('name'), // Extracts the names of specializations
            "years_of_experience" => $this->years_of_experience,
            "description" => $this->description,
            "phone" => $this->phone,
            "avatar" => $this->avatar,
            'agencies' => AgencyResource::collection($this->agencies),
            'issues' => IssueResource::collection($this->issues),
            'rates' => RateResource::collection($this->rates),
            'rank' => round($rank, 2)
        ];
    }
}
