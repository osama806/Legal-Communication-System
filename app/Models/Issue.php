<?php

namespace App\Models;

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

}
