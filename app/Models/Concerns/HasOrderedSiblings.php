<?php

namespace App\Models\Concerns;

/**
 * Riordino manuale tramite colonna `order`, scoperto per una FK del genitore
 * (es. gym_id, plan_id). Usato dai model con reorder su/giù nel pannello admin.
 */
trait HasOrderedSiblings
{
    /**
     * Nome della colonna FK che delimita il gruppo di elementi riordinabili (es. 'gym_id').
     */
    abstract protected function siblingScopeColumn(): string;

    public function previousSibling(): ?static
    {
        $column = $this->siblingScopeColumn();

        return static::where($column, $this->{$column})
            ->where('order', '<', $this->order)
            ->orderByDesc('order')
            ->first();
    }

    public function nextSibling(): ?static
    {
        $column = $this->siblingScopeColumn();

        return static::where($column, $this->{$column})
            ->where('order', '>', $this->order)
            ->orderBy('order')
            ->first();
    }
}
