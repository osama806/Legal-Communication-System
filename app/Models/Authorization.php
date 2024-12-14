<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Authorization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    /**
     * Defines a many-to-many relationship with the Agency model.
     * This relationship uses a pivot table named 'agency_authorizations'
     * with 'agency_id' and 'authorization_id' as the foreign keys.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function agencies(): BelongsToMany
    {
        return $this->belongsToMany(Agency::class, "agency_authorizations", "authorization_id", "agency_id");
    }
}
