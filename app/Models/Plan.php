<?php

namespace App\Models;

use App\Models\Concerns\HasOrderedSiblings;
use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory, HasOrderedSiblings;

    protected $fillable = ['gym_id', 'name', 'price', 'highlighted', 'order'];

    protected $attributes = [
        'highlighted' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'highlighted' => 'boolean',
        ];
    }

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    public function planFeatures(): HasMany
    {
        return $this->hasMany(PlanFeature::class)->orderBy('order');
    }

    protected function siblingScopeColumn(): string
    {
        return 'gym_id';
    }
}
