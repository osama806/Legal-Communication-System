<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Issue extends Model
{
    use HasFactory;

    protected $fillable = [
        "base_number",
        "record_number",
        "lawyer_id",
        "agency_id",
        "court_name",
        "type",
        "start_date",
        "end_date",
        "status",
        "estimated_cost",
        "is_active",
        "success_rate",
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
     * Defines a one-to-many relationship with the Issue_note model.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function issue_notes(): HasMany
    {
        return $this->hasMany(Issue_note::class);
    }

    /**
     * Defines a one-to-many polymorphic relationship with the Attachment model.
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'relatedable');
    }

    /**
     * Defines a one-to-one relationship with the Agency model.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * Filter issue
     * @param mixed $query
     * @param mixed $filters
     * @return \Illuminate\Contracts\Database\Eloquent\Builder
     */
    public function scopeFilter($query, $filters): Builder
    {
        if (isset($filters['base_number'])) {
            $query->where('base_number', $filters['base_number']);
        }

        if (isset($filters['record_number'])) {
            $query->where('record_number', $filters['record_number']);
        }

        if (isset($filters['court_name'])) {
            $query->where('court_name', $filters['court_name']);
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
