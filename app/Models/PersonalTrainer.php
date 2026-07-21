<?php

namespace App\Models;

use Database\Factories\PersonalTrainerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalTrainer extends Model
{
    /** @use HasFactory<PersonalTrainerFactory> */
    use HasFactory;

    protected $fillable = ['gym_id', 'name', 'specialty', 'photo_path', 'order'];

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }
}
