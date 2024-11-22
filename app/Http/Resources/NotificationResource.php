<?php

namespace App\Http\Resources;

use App\Models\Lawyer;
use App\Models\Representative;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "agency_id" => $this->data['agency_number'],
            "from" => $this->data['from'],
            "msg" => $this->data['message'],
        ];
    }
}
