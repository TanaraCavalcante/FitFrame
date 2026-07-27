<?php

namespace App\Models;

use App\Models\Concerns\HasOrderedSiblings;
use Database\Factories\GymClassFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GymClass extends Model
{
    /** @use HasFactory<GymClassFactory> */
    use HasFactory, HasOrderedSiblings;

    protected $fillable = ['gym_id', 'name', 'description', 'icon', 'order'];

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    protected function siblingScopeColumn(): string
    {
        return 'gym_id';
    }
}
