<?php

namespace App\Models;

use App\Traits\AuthenticatableUser;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
     * Filter lawyer
     * @param mixed $query
     * @param mixed $filters
     * @return \Illuminate\Contracts\Database\Eloquent\Builder
     */
    public function scopeFilter($query, $filters): Builder
    {
        if (isset($filters['name'])) {
            $query->where('name', $filters['name']);
        }

        if (isset($filters['union_branch'])) {
            $query->where('union_branch', $filters['union_branch']);
        }
        return $query;
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
