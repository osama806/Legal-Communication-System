<?php

namespace App\Models;

use App\Traits\AuthenticatableUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Lawyer extends Authenticatable implements JWTSubject
{
    use AuthenticatableUser;

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
        'description',
        'avatar',
    ];

    /**
     * Defines a many-to-many relationship with the Specialization model.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function specializations(): BelongsToMany
    {
        return $this->belongsToMany(Specialization::class, 'lawyer_specializations', 'lawyer_id', 'specialization_id');
    }

    /**
     * Defines a one-to-many relationship with the Issue_note model.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function issue_notes(): HasMany
    {
        return $this->hasMany(Issue_note::class);
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

}
