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
        "user_id",
        "court_name",
        "type",
        "start_date",
        "end_date",
        "status",
        "estimated_cost",
        "is_active",
        "success_rate",
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lawyer(): BelongsTo
    {
        return $this->belongsTo(Lawyer::class);
    }

    public function issue_notes(): HasMany
    {
        return $this->hasMany(Issue_note::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class,"relatedable");
    }
}
