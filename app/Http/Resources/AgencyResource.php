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
            "lawyer_name" => $this->lawyer->name,
            "user_name" => $this->user->name,
            "place_of_issue" => $this->place_of_issue,
            "type" => $this->type,
            "authorizations" => $this->authorizations,
            "exceptions" => $this->exceptions,
        ];
    }
}
