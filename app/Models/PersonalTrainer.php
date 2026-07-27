<?php

namespace App\Models;

use App\Models\Concerns\HasOrderedSiblings;
use Database\Factories\PersonalTrainerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PersonalTrainer extends Model implements HasMedia
{
    /** @use HasFactory<PersonalTrainerFactory> */
    use HasFactory, HasOrderedSiblings, InteractsWithMedia;

    protected $fillable = ['gym_id', 'name', 'specialty', 'order'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')->singleFile();
    }

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    protected function siblingScopeColumn(): string
    {
        return 'gym_id';
    }
}
