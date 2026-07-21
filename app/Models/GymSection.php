<?php

namespace App\Models;

use Database\Factories\GymSectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GymSection extends Model
{
    /** @use HasFactory<GymSectionFactory> */
    use HasFactory;

    protected $fillable = ['gym_id', 'section', 'order'];

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }
}
