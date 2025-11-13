<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Cashier\Billable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use Billable;
    use HasFactory;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'cpf',
        'cep',
        'address_street',
        'address_number',
        'address_neighborhood',
        'address_city',
        'address_state',
        'address_country',
        'password',
        'locale',
        'trial_starts_at',
        'trial_ends_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
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
            'trial_starts_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'is_lifetime' => 'boolean',
        ];
    }

    /**
     * Cadernos do usuário.
     */
    public function notebooks(): HasMany
    {
        return $this->hasMany(Notebook::class);
    }

    public function disciplines(): HasMany
    {
        return $this->hasMany(Discipline::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function flashcardSessions(): HasMany
    {
        return $this->hasMany(FlashcardSession::class);
    }

    public function pdfDocuments(): HasMany
    {
        return $this->hasMany(PdfDocument::class);
    }

    public function hasLifetimeAccess(): bool
    {
        return (bool) $this->is_lifetime;
    }

    public function hasActiveSubscriptionOrTrial(): bool
    {
        if ($this->hasLifetimeAccess()) {
            return true;
        }

        if ($this->subscribed('default')) {
            return true;
        }

        return $this->onGenericTrial();
    }

    public function subscriptionPlanName(): string
    {
        return (string) config('services.stripe.plan_name', config('app.name'));
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}
