<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Trait para models que pertencem ao usuário autenticado.
 *
 * - Preenche automaticamente o `user_id` quando o registro é criado.
 * - Garante que consultas padrão retornem apenas dados do usuário autenticado.
 */
trait BelongsToAuthenticatedUser
{
    /**
     * Nome da coluna que guarda o usuário.
     */
    protected string $userForeignKey = 'user_id';

    /**
     * Registra os hooks do lifecycle apenas uma vez por classe consumidora.
     */
    protected static function bootBelongsToAuthenticatedUser(): void
    {
        static::creating(function (Model $model): void {
            if (! $model->getAttribute($model->getUserForeignKey()) && Auth::check()) {
                $model->setAttribute($model->getUserForeignKey(), Auth::id());
            }
        });

        static::addGlobalScope('owned_by_authenticated_user', function (Builder $builder): void {
            if (! Auth::check()) {
                return;
            }

            $builder->where(
                $builder->qualifyColumn($builder->getModel()->getUserForeignKey()),
                Auth::id(),
            );
        });
    }

    /**
     * Relacionamento com o usuário dono do registro.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, $this->getUserForeignKey());
    }

    /**
     * Escopo auxiliar para filtrar por usuário explicitamente.
     */
    public function scopeOwnedBy(Builder $builder, ?User $user = null): Builder
    {
        $userId = $user?->getKey() ?? Auth::id();

        if (! $userId) {
            return $builder;
        }

        return $builder->where(
            $builder->qualifyColumn($builder->getModel()->getUserForeignKey()),
            $userId,
        );
    }

    /**
     * Permite customizar o nome da FK em classes filhas.
     */
    public function getUserForeignKey(): string
    {
        return $this->userForeignKey ?? 'user_id';
    }
}
