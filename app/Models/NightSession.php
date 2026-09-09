<?php

namespace App\Models;

use App\Concerns\HasUuidRouteKey;
use Database\Factories\NightSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
