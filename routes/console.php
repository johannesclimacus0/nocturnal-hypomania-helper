<?php

use App\Console\Commands\ClearOutdatedSessionsCommand;
use Illuminate\Support\Facades\Schedule;

Schedule::command(ClearOutdatedSessionsCommand::class)->everyMinute();
