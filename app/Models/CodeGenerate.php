<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodeGenerate extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'code',
        'expiration_date',
        'is_verify'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lawyer(): BelongsTo
    {
        return $this->belongsTo(Lawyer::class);
    }

    public function representative(): BelongsTo
    {
        return $this->belongsTo(Representative::class);
    }
}
