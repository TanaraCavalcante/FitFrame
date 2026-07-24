<?php

namespace App\Models;

use Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    /** @use HasFactory<ContactFactory> */
    use HasFactory;

    protected $fillable = ['gym_id', 'address', 'email', 'phone', 'whatsapp', 'instagram', 'hours'];

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    /**
     * URL diretto per il profilo Instagram, a partire dall'handle salvato (es. "@pulse.gym").
     */
    public function instagramUrl(): string
    {
        return 'https://instagram.com/'.ltrim($this->instagram, '@');
    }

    /**
     * URL diretto per aprire una chat WhatsApp, a partire dal numero salvato.
     */
    public function whatsappUrl(): string
    {
        return 'https://wa.me/'.preg_replace('/\D/', '', $this->whatsapp);
    }

    /**
     * URL "mailto:" con oggetto precompilato, per il CTA finale (identifica che il lead viene da lì).
     */
    public function mailtoUrl(string $subject): string
    {
        return 'mailto:'.$this->email.'?subject='.rawurlencode($subject);
    }
}
