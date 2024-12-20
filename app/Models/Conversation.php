<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "lawyer_id"
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
     * Defines a one-to-many polymorphic relationship with the Message model.
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function messages(): MorphMany
    {
        return $this->morphMany(Message::class, 'messagable');
    }

}
