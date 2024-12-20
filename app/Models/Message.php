<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Crypt;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        "conversation_id",
        "messagable_type",
        "messagable_id",
        "content",
    ];

    /**
     * Defines a polymorphic relationship where the current model can belong to multiple other models.
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function messagable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Defines a one-to-one polymorphic relationship with the Attachment model.
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function attachment(): MorphOne
    {
        return $this->morphOne(Attachment::class, 'relatedable');
    }

     /**
      * Encryption messages before save in storage
      * @param mixed $value
      * @return void
      */
     public function setContentAttribute($value)
     {
         $this->attributes['content'] = Crypt::encryptString($value);
     }

     /**
      * Decryption messages when display in conversation
      * @param mixed $value
      * @return string
      */
     public function getContentAttribute($value)
     {
         return Crypt::decryptString($value);
     }

}
