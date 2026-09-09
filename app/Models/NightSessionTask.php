<?php

namespace App\Models;

use App\Concerns\HasUuidRouteKey;
use App\Enums\NightSessionTaskStatus;
use Database\Factories\NightSessionTaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['status', 'position', 'selected_at', 'completed_at', 'skipped_at'])]
class NightSessionTask extends Model
{
    /** @use HasFactory<NightSessionTaskFactory> */
    use HasFactory, HasUuidRouteKey;

    protected $casts = [
        'status' => NightSessionTaskStatus::class,
        'position' => 'integer',
        'selected_at' => 'immutable_datetime',
        'completed_at' => 'immutable_datetime',
        'skipped_at' => 'immutable_datetime',
    ];

    public function nightSession(): BelongsTo
    {
        return $this->belongsTo(NightSession::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
