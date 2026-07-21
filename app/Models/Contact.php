<?php

namespace App\Models;

use Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    /** @use HasFactory<ContactFactory> */
    use HasFactory;

    protected $fillable = ['gym_id', 'address', 'phone', 'whatsapp', 'instagram', 'hours'];

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }
}
