<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            "name" => $this->name,
            "email" => $this->email,
            "address" => $this->address,
            "birthdate" => $this->birthdate,
            "birth_place" => $this->birth_place,
            "national_number" => $this->national_number,
            "gender" => $this->gender,
            "phone" => $this->phone,
            "role" => $this->role->name,
        ];
    }
}
