<?php

namespace App\Models;

use Database\Factories\GymFactory;
use Igaster\LaravelTheme\Facades\Theme;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Gym extends Model implements HasMedia
{
    /** @use HasFactory<GymFactory> */
    use HasFactory, InteractsWithMedia;

    protected $fillable = ['name', 'slug'];

    /**
     * Visual dell'hero (3 immagini oppure un video, mai entrambi) e le 5 foto della galleria.
     * Ogni slot è una collection indipendente: se vuota, il frontend ricade sulla foto di default del tema.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('hero_image_1')->singleFile();
        $this->addMediaCollection('hero_image_2')->singleFile();
        $this->addMediaCollection('hero_image_3')->singleFile();
        $this->addMediaCollection('hero_video')->singleFile();

        $this->addMediaCollection('gallery_image_1')->singleFile();
        $this->addMediaCollection('gallery_image_2')->singleFile();
        $this->addMediaCollection('gallery_image_3')->singleFile();
        $this->addMediaCollection('gallery_image_4')->singleFile();
        $this->addMediaCollection('gallery_image_5')->singleFile();
    }

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }

    public function gymSections(): HasMany
    {
        return $this->hasMany(GymSection::class)->orderBy('order');
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
        return $this->hasMany(GymClass::class)->orderBy('order');
    }

    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class)->orderBy('order');
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class)->orderBy('order');
    }

    public function personalTrainers(): HasMany
    {
        return $this->hasMany(PersonalTrainer::class)->orderBy('order');
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
