<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agency extends Model
{
    use HasFactory;

    protected $fillable = [
        "sequential_number",
        "record_number",
        "lawyer_id",
        "user_id",
        "representative_id",
        "place_of_issue",
        "type",
        "cause",
        "status",
    ];

    /**
     * Defines a many-to-one relationship with the User model.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Defines a many-to-one relationship with the Lawyer model.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lawyer(): BelongsTo
    {
        return $this->belongsTo(Lawyer::class);
    }

    /**
     * Defines a many-to-one relationship with the Representative model.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function representative(): BelongsTo
    {
        return $this->belongsTo(Representative::class);
    }

    /**
     * Defines a one-to-many relationship with the Issue model.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function issue(): HasMany
    {
        return $this->hasMany(Issue::class);
    }

    /**
     * Defines a many-to-many relationship with the Authorization model.
     * This relationship uses a pivot table named 'agency_authorizations'
     * with 'authorization_id' and 'agency_id' as the foreign keys.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function authorizations(): BelongsToMany
    {
        return $this->belongsToMany(Authorization::class, "agency_authorizations", "agency_id", "authorization_id");
    }

    /**
     * Filter agency
     * @param mixed $query
     * @param mixed $filters
     * @return \Illuminate\Contracts\Database\Eloquent\Builder
     */
    public function scopeFilter($query, $filters): Builder
    {
        if (isset($filters['sequential_number'])) {
            $query->where('sequential_number', $filters['sequential_number']);
        }

        if (isset($filters['record_number'])) {
            $query->where('record_number', $filters['record_number']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        return $query;
    }
}
