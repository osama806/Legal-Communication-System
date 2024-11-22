<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Specialization extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
    ];

    /**
     * Defines a many-to-many relationship with the Lawyer model.
     * This relationship uses a pivot table named 'lawyer_specializations'
     * with 'specialization_id' and 'lawyer_id' as the foreign keys.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function lawyers(): BelongsToMany
    {
        return $this->belongsToMany(Lawyer::class, "lawyer_specializations", "specialization_id", "lawyer_id");
    }

}
