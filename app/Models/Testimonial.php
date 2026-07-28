<?php

namespace App\Models;

use App\Models\Concerns\HasOrderedSiblings;
use Database\Factories\TestimonialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends Model
{
    /** @use HasFactory<TestimonialFactory> */
    use HasFactory, HasOrderedSiblings;

    protected $fillable = ['gym_id', 'author_name', 'text', 'member_since', 'order'];

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    protected function siblingScopeColumn(): string
    {
        return 'gym_id';
    }
}
