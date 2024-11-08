<?php

namespace App\Traits;

use App\Models\Agency;
use App\Models\Attachment;
use App\Models\Chat;
use App\Models\Issue;
use App\Models\Message;
use App\Models\Rate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

trait AuthenticatableUser
{
    use HasApiTokens, Notifiable, HasFactory;

    /**
     * Get the identifier that will be stored in the JWT claim.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key-value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function messages()
    {
        return $this->morphMany(Message::class, 'messagable');
    }

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachmentable');
    }

    public function agencies()
    {
        return $this->hasMany(Agency::class);
    }

    public function rates()
    {
        return $this->hasMany(Rate::class);
    }

    public function issues()
    {
        return $this->hasMany(Issue::class);
    }
}
