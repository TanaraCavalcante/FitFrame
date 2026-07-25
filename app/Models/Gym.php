<?php

namespace App\Models;

use Database\Factories\GymFactory;
use Igaster\LaravelTheme\Facades\Theme;
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

    /**
     * Utenti gym_admin assegnati a questa struttura.
     */
    public function admins(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Recupera un testo libero da `contents` per questa palestra (es. hero_headline).
     * Se la chiave non esiste per la palestra, ritorna il default del tema base.
     */
    public function content(string $key, string $default = ''): string
    {
        return $this->contents->firstWhere('key', $key)?->value ?? $default;
    }

    /**
     * Nomi dei temi installati (cartelle con theme.json), esclusa `base`
     * che non ha asset propri e non è mai assegnabile a una Gym reale.
     *
     * @return array<int, string>
     */
    public static function availableThemes(): array
    {
        return collect(Theme::all())
            ->pluck('name')
            ->reject(fn (string $name) => $name === 'base')
            ->values()
            ->all();
    }
}
