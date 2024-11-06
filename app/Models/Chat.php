<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "lawyer_id"
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lawyer(): BelongsTo
    {
        return $this->belongsTo(Lawyer::class);
    }

    public function messages(): MorphMany
    {
        return $this->morphMany(Message::class, 'messagable');
    }
}
