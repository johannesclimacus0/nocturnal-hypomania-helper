<?php

namespace App\Models;

use App\Concerns\HasUuidRouteKey;
use App\Enums\TaskDifficulty;
use App\Enums\TaskStatus;
use Database\Factories\TaskFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property TaskDifficulty $difficulty
 * @property TaskStatus $status
 * @property-read Area|null $area
 * @property-read Category|null $category
 * @property-read Collection<int, NightSessionTask> $nightSessionTasks
 * @property-read int|null $night_session_tasks_count
 * @property-read TaskType|null $taskType
 * @property-read User|null $user
 *
 * @method static TaskFactory factory($count = null, $state = [])
 * @method static Builder<static>|Task newModelQuery()
 * @method static Builder<static>|Task newQuery()
 * @method static Builder<static>|Task query()
 *
 * @mixin Eloquent
 */
#[Fillable([
    'title',
    'description',
    'estimated_time_minutes',
    'difficulty',
    'status',
])]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory, HasUuidRouteKey;

    protected $casts = [
        'difficulty' => TaskDifficulty::class,
        'status' => TaskStatus::class,
        'last_selected_at' => 'datetime',
        'last_skipped_at' => 'datetime',
        'last_completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function taskType(): BelongsTo
    {
        return $this->belongsTo(TaskType::class);
    }

    public function nightSessionTasks(): HasMany
    {
        return $this->hasMany(NightSessionTask::class);
    }
}
