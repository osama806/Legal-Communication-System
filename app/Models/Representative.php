<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Representative extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'address',
        'union_branch',
        'union_number',
    ];

    /**
     * Defines a one-to-one polymorphic relationship with the Role model.
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function role(): MorphOne
    {
        return $this->morphOne(Role::class, 'rolable');
    }

    /**
     * Defines a one-to-many relationship with the Agency model.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
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
