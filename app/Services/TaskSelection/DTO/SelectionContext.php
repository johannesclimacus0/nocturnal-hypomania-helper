<?php

namespace App\Services\TaskSelection\DTO;

use App\Models\NightSession;
use Illuminate\Database\Eloquent\Builder;

final readonly class SelectionContext
{
    public function __construct(
        public NightSession $session,
        public Builder $candidates,
    ) {}
}
