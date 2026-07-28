<?php

namespace App\Models;

use App\Models\Concerns\HasOrderedSiblings;
use Database\Factories\GymSectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GymSection extends Model
{
    /** @use HasFactory<GymSectionFactory> */
    use HasFactory, HasOrderedSiblings;

    protected $fillable = ['gym_id', 'section', 'order'];

    /**
     * Ordine di default delle sezioni riordinabili per una nuova struttura
     * (vedi `resources/views/base/home.blade.php` e docs/6-temas.md sezione H).
     * Hero, header e footer sono fissi e non fanno parte di questo elenco.
     *
     * @var list<string>
     */
    public const DEFAULT_ORDER = ['classes', 'plans', 'gallery', 'team', 'testimonials', 'contact-cta'];

    /**
     * Etichetta e icona FontAwesome per la UI admin, per chiave di sezione.
     *
     * @var array<string, array{label: string, icon: string}>
     */
    private const META = [
        'classes' => ['label' => 'Corsi', 'icon' => 'fa-dumbbell'],
        'plans' => ['label' => 'Piani', 'icon' => 'fa-tags'],
        'gallery' => ['label' => 'Galleria', 'icon' => 'fa-images'],
        'team' => ['label' => 'Team', 'icon' => 'fa-people-group'],
        'testimonials' => ['label' => 'Testimonianze', 'icon' => 'fa-quote-left'],
        'contact-cta' => ['label' => 'CTA finale', 'icon' => 'fa-bullhorn'],
    ];

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    public function label(): string
    {
        return self::META[$this->section]['label'] ?? ucfirst($this->section);
    }

    public function icon(): string
    {
        return self::META[$this->section]['icon'] ?? 'fa-circle';
    }

    protected function siblingScopeColumn(): string
    {
        return 'gym_id';
    }
}
