<?php

namespace App\Models;

use App\Concerns\HasUuidRouteKey;
use App\Enums\TaskDifficulty;
use Database\Factories\NightSessionFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read Collection<int, NightSessionTask> $nightSessionTasks
 * @property-read int|null $night_session_tasks_count
 * @property-read User|null $user
 *
 * @method static NightSessionFactory factory($count = null, $state = [])
 * @method static Builder<static>|NightSession newModelQuery()
 * @method static Builder<static>|NightSession newQuery()
 * @method static Builder<static>|NightSession query()
 *
 * @mixin Eloquent
 */
#[Fillable([
    'started_at',
    'ended_at',
    'available_time_minutes',
    'difficulty',
    'area_id',
    'category_id',
    'task_type_id',
])]
class NightSession extends Model
{
    /** @use HasFactory<NightSessionFactory> */
    use HasFactory, HasUuidRouteKey;

    protected $casts = [
        'started_at' => 'immutable_datetime',
        'ended_at' => 'immutable_datetime',
        'difficulty' => TaskDifficulty::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function nightSessionTasks(): HasMany
    {
        return $this->hasMany(NightSessionTask::class);
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
}
