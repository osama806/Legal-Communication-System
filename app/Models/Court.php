<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Court extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    /**
     * Defines a one-to-many relationship with the Court_room model.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function court_rooms(): HasMany
    {
        return $this->hasMany(Court_room::class);
    }

    /**
     * Defines a one-to-many relationship with the Issue model.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }
}
