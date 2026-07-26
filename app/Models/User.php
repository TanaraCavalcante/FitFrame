<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Notifications\ResetPasswordNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lab404\Impersonate\Models\Impersonate;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Impersonate, Notifiable;

    /**
     * Rispecchia il default della colonna `surname` in migration.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'surname' => '',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'surname',
        'email',
        'password',
        'role',
        'gym_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    /**
     * Verifica se l'utente è un super_admin (usato per i controlli lato view).
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    /**
     * Iniziali per l'avatar (prima lettera di nome e cognome, es. "Tanara Cavalcante" → "TC").
     */
    public function initials(): string
    {
        $first = mb_substr($this->name, 0, 1);
        $last = $this->surname !== '' ? mb_substr($this->surname, 0, 1) : '';

        return mb_strtoupper($first.$last);
    }

    /**
     * Solo un super_admin può impersonare un altro utente.
     */
    public function canImpersonate(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    /**
     * Solo un gym_admin può essere impersonato (mai un altro super_admin).
     */
    public function canBeImpersonated(): bool
    {
        return $this->role === UserRole::GymAdmin;
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
