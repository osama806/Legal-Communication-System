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
        return [
            "name" => $this->name,
            "email" => $this->email,
            "address" => $this->address,
            "union_branch" => $this->union_branch,
            "union_number" => $this->union_number,
            "affiliation_date" => $this->affiliation_date,
            "years_of_experience" => $this->years_of_experience,
            "phone" => $this->phone,
        ];
    }
}
