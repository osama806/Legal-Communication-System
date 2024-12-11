<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Court_room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'court_id'
    ];

    /**
     * Defines a many-to-one relationship with the Court model.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
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
