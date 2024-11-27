<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"=> $this->id,
            "sequential_number" => $this->sequential_number,
            "record_number" => $this->record_number,
            "lawyer_id" => $this->lawyer_id,
            "user_id" => $this->user_id,
            "place_of_issue" => $this->place_of_issue,
            "type" => $this->type,
            "status"    =>  $this->status,
            "authorizations" => $this->authorizations,
            "exceptions" => $this->exceptions,
        ];
    }
}
