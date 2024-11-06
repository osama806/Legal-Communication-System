<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Lawyer extends Model implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'address',
        'union_branch',
        'union_number',
        'affiliation_date',
        'years_of_experience',
        'phone',
    ];

    public function specializations(): BelongsToMany
    {
        return $this->belongsToMany(Specialization::class, 'lawyer_specializations', 'lawyer_id', 'specialization_id');
    }

    public function role(): MorphOne
    {
        return $this->morphOne(Role::class, 'rolable');
    }

    public function rates(): HasMany
    {
        return $this->hasMany(Rate::class);
    }

    public function messages(): MorphMany
    {
        return $this->morphMany(Message::class, 'messagable');
    }

    public function chats(): HasMany
    {
        return $this->hasMany(Chat::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachmentable');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }

    public function issue_notes(): HasMany
    {
        return $this->hasMany(Issue_note::class);
    }

    public function agencies(): HasMany
    {
        return $this->hasMany(Agency::class);
    }

     /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

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
}
