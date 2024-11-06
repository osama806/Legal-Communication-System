<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        "chat_id",
        "messagable_type",
        "messagable_id",
        "body",
        "seen",
    ];

    public function messagable(): MorphTo
    {
        return $this->morphTo();
    }

    public function attachment(): MorphOne
    {
        return $this->morphOne(Attachment::class, 'relatedable');
    }
}
