<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        "attachmentable_type",
        "attachmentable_id",
        "file_name",
        "file_type",
        "relatedable_type",
        "relatedable_id",
    ];

    public function attachmentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function message(): MorphOne
    {
        return $this->morphOne(Message::class, 'messagable');
    }
}
