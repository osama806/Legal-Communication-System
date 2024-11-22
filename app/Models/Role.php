<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
    ];

    /**
     * Defines a polymorphic relationship where the current model can belong to multiple other models.
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function rolable(): MorphTo
    {
        return $this->morphTo();
    }

}
