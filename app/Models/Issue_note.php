<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Issue_note extends Model
{
    use HasFactory;

    protected $fillable = [
        "lawyer_id",
        "user_id",
        "notes",
    ];

    public function lawyer(): BelongsTo
    {
        return $this->belongsTo(Lawyer::class);
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }
}
