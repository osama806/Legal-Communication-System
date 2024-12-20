<?php

namespace App\Traits;

use App\Models\Agency;
use App\Models\Attachment;
use App\Models\Conversation;
use App\Models\Issue;
use App\Models\Message;
use App\Models\Rate;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

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


    /**
     * Defines a one-to-one polymorphic relationship with the Role model.
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function role(): MorphOne
    {
        return $this->morphOne(Role::class, 'rolable');
    }

    /**
     * Checks if the current model has a role with the specified name.
     * @param string $roleName The name of the role to check.
     * @return bool True if the model's role matches the specified name, false otherwise.
     */
    public function hasRole($roleName): bool
    {
        return $this->role && $this->role->name === $roleName;
    }

    /**
     * Defines a one-to-many polymorphic relationship with the Message model.
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function messages()
    {
        return $this->morphMany(Message::class, 'messagable');
    }

    /**
     * Defines a one-to-many relationship with the Conversation model.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Defines a one-to-many polymorphic relationship with the Attachment model.
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachmentable');
    }

    /**
     * Defines a one-to-many relationship with the Agency model.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function agencies()
    {
        return $this->hasMany(Agency::class);
    }

    /**
     * Defines a one-to-many relationship with the Rate model.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rates()
    {
        return $this->hasMany(Rate::class);
    }

    /**
     * Defines a one-to-many relationship with the Issue model.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function issues()
    {
        return $this->hasMany(Issue::class);
    }
}
