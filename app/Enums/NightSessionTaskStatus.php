<?php

namespace App\Enums;

enum NightSessionTaskStatus: string
{
    case Selected = 'selected';
    case Completed = 'completed';
    case Skipped = 'skipped';
}
