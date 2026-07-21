<?php

namespace App\Models;

use Database\Factories\GymFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Gym extends Model
{
    /** @use HasFactory<GymFactory> */
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }

    public function gymSections(): HasMany
    {
        return $this->hasMany(GymSection::class);
    }

    public function contact(): HasOne
    {
        return $this->hasOne(Contact::class);
    }

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class);
    }

    public function gymClasses(): HasMany
    {
        return $this->hasMany(GymClass::class);
    }

    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class);
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    public function personalTrainers(): HasMany
    {
        return $this->hasMany(PersonalTrainer::class);
    }
}
