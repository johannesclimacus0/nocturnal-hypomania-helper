<?php

namespace App\Enums;

enum TaskSelectionMode: string
{
    case Shortest = 'shortest';
    case LeastRecentlySelected = 'least_recently_selected';
    case MostSkipped = 'most_skipped';
}
