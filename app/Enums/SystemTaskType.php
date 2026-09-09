<?php

namespace App\Enums;

enum SystemTaskType: string
{
    case Learn = 'learn';
    case Build = 'build';
    case Fix = 'fix';
    case Experiment = 'experiment';
    case Practice = 'practice';
    case Refactor = 'refactor';
    case Read = 'read';
    case Design = 'design';
}
