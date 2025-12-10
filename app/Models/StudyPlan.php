<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAuthenticatedUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudyPlan extends Model
{
    use BelongsToAuthenticatedUser;
    use HasFactory;

    protected $fillable = [
        'study_days_per_week',
        'daily_sessions_target',
        'last_mode_index',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function disciplines(): HasMany
    {
        return $this->hasMany(StudyPlanDiscipline::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(StudyPlanTask::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
