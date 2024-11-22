<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        "authorizations",
        "exceptions",
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

}
