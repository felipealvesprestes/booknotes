<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAuthenticatedUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class StudyPlanTask extends Model
{
    use BelongsToAuthenticatedUser;
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'study_plan_id',
        'study_plan_discipline_id',
        'discipline_id',
        'study_mode',
        'quantity',
        'unit_label',
        'scheduled_for',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'scheduled_for' => 'date',
        'completed_at' => 'datetime',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(StudyPlan::class, 'study_plan_id');
    }

    public function planDiscipline(): BelongsTo
    {
        return $this->belongsTo(StudyPlanDiscipline::class, 'study_plan_discipline_id');
    }

    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    public function scopeScheduledFor(Builder $query, Carbon|string $date): Builder
    {
        $dateValue = $date instanceof Carbon ? $date : Carbon::parse($date);

        return $query->whereDate('scheduled_for', $dateValue);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function getIsOverdueAttribute(): bool
    {
        if ($this->scheduled_for === null) {
            return false;
        }

        return $this->status === self::STATUS_PENDING
            && $this->scheduled_for->isBefore(now()->startOfDay());
    }

    public function markCompleted(): void
    {
        $this->status = self::STATUS_COMPLETED;
        $this->completed_at = now();
        $this->save();
    }

    public function reopen(): void
    {
        $this->status = self::STATUS_PENDING;
        $this->completed_at = null;
        $this->save();
    }

    public function cancel(): void
    {
        $this->status = self::STATUS_CANCELLED;
        $this->completed_at = null;
        $this->save();
    }

    public function actionUrl(): ?string
    {
        $disciplineId = $this->discipline_id;

        return match ($this->study_mode) {
            'flashcards' => route('study.flashcards', array_filter([
                'discipline' => $disciplineId,
            ])),
            'simulated' => route('study.simulated', array_filter([
                'disciplineId' => $disciplineId,
                'scopeType' => $disciplineId ? 'discipline' : null,
            ])),
            'true_false', 'fill_blank', 'multiple_choice' => route('study.exercises', array_filter([
                'disciplineId' => $disciplineId,
                'mode' => $this->study_mode,
            ])),
            default => route('study.flashcards'),
        };
    }
}
