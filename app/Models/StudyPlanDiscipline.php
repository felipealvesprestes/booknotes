<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudyPlanDiscipline extends Model
{
    use HasFactory;

    protected $fillable = [
        'study_plan_id',
        'discipline_id',
        'sessions_per_week',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(StudyPlan::class, 'study_plan_id');
    }

    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    public function dailyTarget(int $studyDays): int
    {
        $days = max(1, $studyDays);

        return max(1, (int) ceil($this->sessions_per_week / $days));
    }
}
