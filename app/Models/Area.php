<?php

namespace App\Models;

use App\Concerns\HasUuidRouteKey;
use Database\Factories\AreaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read Collection<int, Task> $tasks
 * @property-read int|null $tasks_count
 * @property-read User|null $user
 *
 * @method static AreaFactory factory($count = null, $state = [])
 * @method static Builder<static>|Area newModelQuery()
 * @method static Builder<static>|Area newQuery()
 * @method static Builder<static>|Area query()
 *
 * @mixin \Eloquent
 */
#[Fillable(['name'])]
class Area extends Model
{
    /** @use HasFactory<AreaFactory> */
    use HasFactory, HasUuidRouteKey;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
