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
     * Etichetta, icona FontAwesome e titolo di default per la UI admin, per chiave di
     * sezione. "contact-cta" non ha `title_key`: il suo titolo si gestisce nella pagina
     * CTA (che include già sottotitolo e pulsante nello stesso contesto).
     *
     * @var array<string, array{label: string, icon: string, title_key: ?string, default_title: ?string}>
     */
    private const META = [
        'classes' => ['label' => 'Corsi', 'icon' => 'fa-dumbbell', 'title_key' => 'classes_title', 'default_title' => 'Le nostre modalità'],
        'plans' => ['label' => 'Piani', 'icon' => 'fa-tags', 'title_key' => 'plans_title', 'default_title' => 'Scegli il tuo piano'],
        'gallery' => ['label' => 'Galleria', 'icon' => 'fa-images', 'title_key' => 'gallery_title', 'default_title' => 'La nostra struttura'],
        'team' => ['label' => 'Team', 'icon' => 'fa-people-group', 'title_key' => 'team_title', 'default_title' => 'Il nostro team'],
        'testimonials' => ['label' => 'Testimonianze', 'icon' => 'fa-quote-left', 'title_key' => 'testimonials_title', 'default_title' => 'Cosa dicono di noi'],
        'contact-cta' => ['label' => 'CTA finale', 'icon' => 'fa-bullhorn', 'title_key' => null, 'default_title' => null],
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

    public function titleContentKey(): ?string
    {
        return self::titleContentKeyFor($this->section);
    }

    public function defaultTitle(): ?string
    {
        return self::META[$this->section]['default_title'] ?? null;
    }

    public static function titleContentKeyFor(string $section): ?string
    {
        return self::META[$section]['title_key'] ?? null;
    }

    protected function siblingScopeColumn(): string
    {
        return 'gym_id';
    }
}
