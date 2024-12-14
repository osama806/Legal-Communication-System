<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Exception extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    /**
     * Defines a many-to-many relationship with the Agency model.
     * This relationship uses a pivot table named 'agency_exceptions'
     * with 'agency_id' and 'exception_id' as the foreign keys.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function agencies(): BelongsToMany
    {
        return $this->belongsToMany(Agency::class, "agency_exceptions", "exception_id", "agency_id");
    }
}
