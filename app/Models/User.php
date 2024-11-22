<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\AuthenticatableUser;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use AuthenticatableUser;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'address',
        'birthdate',
        'birth_place',
        'national_number',
        'gender',
        'phone',
    ];

    /**
     * Bootstrap any application services.
     * This method listens for the `deleted` event on the model and deletes
     * the related `Role` model whenever the current model is deleted.
     * @return void
     */
    protected static function booted()
    {
        static::deleted(function ($user) {
            $user->role()->delete();
        });
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
