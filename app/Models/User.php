<?php

namespace App\Models;

use App\Notifications\Auth\VerifyEmail;
use App\Notifications\GenericSystemNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Cashier\Billable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use Billable;
    use HasFactory;
    use Notifiable;
    use TwoFactorAuthenticatable;

    protected static function booted(): void
    {
        static::created(function (self $user) {
            $user->notify(new GenericSystemNotification(
                title: 'Bem-vindo ao Booknotes!',
                message: <<<'MESSAGE'
Estamos muito felizes em ter você aqui!

A partir de agora, você tem um espaço organizado para anotar, aprender, revisar e evoluir nos seus estudos.

Comece criando seu caderno e sua disciplina, ou importe um conteúdo para gerar flashcards e exercícios automaticamente.

Se precisar de ajuda, pode contar com a gente. 🚀

Bons estudos!
Equipe Booknotes
MESSAGE,
                tag: 'Onboarding',
                meta: ['origem' => 'Sistema'],
                level: 'success',
            ));
        });
    }

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

    public function studyPlan(): HasOne
    {
        return $this->hasOne(StudyPlan::class);
    }

    public function studyPlanTasks(): HasMany
    {
        return $this->hasMany(StudyPlanTask::class);
    }

    public function pdfDocuments(): HasMany
    {
        return $this->hasMany(PdfDocument::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
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

    /**
     * Send a custom email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmail());
    }
}
