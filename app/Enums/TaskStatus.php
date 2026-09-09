<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Archived = 'archived';
}
