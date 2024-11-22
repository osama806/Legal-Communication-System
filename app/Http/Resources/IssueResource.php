<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IssueResource extends JsonResource
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
            "base_number" => $this->base_number,
            "record_number" => $this->record_number,
            "agency_id" =>  $this->agency_id,
            "court_name" => $this->court_name,
            "type" => $this->type,
            "start_date" => $this->start_date,
            "end_date" => $this->end_date,
            "status" => $this->status,
            "estimated_cost" => $this->estimated_cost,
            "issue activity" => $this->is_active === 1 ? 'active' : 'in-active',
        ];
    }
}
