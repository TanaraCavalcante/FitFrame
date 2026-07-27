<?php

namespace App\Models;

use Database\Factories\GymClassFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GymClass extends Model
{
    /** @use HasFactory<GymClassFactory> */
    use HasFactory;

    protected $fillable = ['gym_id', 'name', 'description', 'icon', 'order'];

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    /**
     * Corso precedente della stessa palestra, per ordine.
     */
    public function previousSibling(): ?self
    {
        return static::where('gym_id', $this->gym_id)
            ->where('order', '<', $this->order)
            ->orderByDesc('order')
            ->first();
    }

    /**
     * Corso successivo della stessa palestra, per ordine.
     */
    public function nextSibling(): ?self
    {
        return static::where('gym_id', $this->gym_id)
            ->where('order', '>', $this->order)
            ->orderBy('order')
            ->first();
    }
}
