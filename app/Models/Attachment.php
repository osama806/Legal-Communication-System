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

    /**
     * Defines a polymorphic relationship where the current model can belong to more than one type of model.
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function attachmentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Defines a one-to-one polymorphic relationship with the Message model.
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function message(): MorphOne
    {
        return $this->morphOne(Message::class, 'messagable');
    }

}
