<?php

namespace App\Models;

use App\Concerns\HasUuidRouteKey;
use Database\Factories\NightSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\NightSessionTask> $nightSessionTasks
 * @property-read int|null $night_session_tasks_count
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\NightSessionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NightSession newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NightSession newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NightSession query()
 * @mixin \Eloquent
 */
#[Fillable(['started_at', 'ended_at'])]
class NightSession extends Model
{
    /** @use HasFactory<NightSessionFactory> */
    use HasFactory, HasUuidRouteKey;

    protected $casts = [
        'started_at' => 'immutable_datetime',
        'ended_at' => 'immutable_datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function nightSessionTasks(): HasMany
    {
        return $this->hasMany(NightSessionTask::class);
    }
}
