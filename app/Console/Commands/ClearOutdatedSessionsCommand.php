<?php

namespace App\Console\Commands;

use App\Models\NightSession;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Str;

#[Signature('sessions:clear
    {--hours=24 : Older than this number of hours}
    {--userUuid= : Clear for user}
')]
#[Description('Clears sessions older than given time in hours')]
class ClearOutdatedSessionsCommand extends Command
{
    public function handle(): int
    {
        $hours = $this->option('hours');

        if($hours < 1) {
            $this->error('Hours must be greater than 0');

            return self::FAILURE;
        }
        $userUuid = $this->option('userUuid');

        $outdatedTime = now()->subHours($hours);

        $query = NightSession::query()
            ->whereNull('ended_at')
            ->where('started_at', '<=', $outdatedTime);

        if($userUuid !== null) {
            $query->whereHas('user', function($query) use ($userUuid) {
                $query->where('uuid', $userUuid);
            });
        }

        $count = 0;

        $query->chunkById(100, function($sessions) use (&$count) {
            foreach ($sessions as $session) {
                $session->delete();

                $count++;
            }
        });

        $this->info('Cleared ' . $count . ' ' . Str::plural('session', $count). ' records');

        return self::SUCCESS;
    }
}
