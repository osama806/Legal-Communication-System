<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\AuthenticatableUser;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'avatar',
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

    public function code(): BelongsTo
    {
        return $this->belongsTo(CodeGenerate::class);
    }

    /**
     * Filter user
     * @param mixed $query
     * @param mixed $filters
     * @return \Illuminate\Contracts\Database\Eloquent\Builder
     */
    public function scopeFilter($query, $filters): Builder
    {
        if (isset($filters['name'])) {
            $query->where('name', $filters['name']);
        }

        if (isset($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (isset($filters['national_number'])) {
            $query->where('national_number', $filters['national_number']);
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
