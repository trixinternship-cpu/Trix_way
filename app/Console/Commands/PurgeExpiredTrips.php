<?php

namespace App\Console\Commands;

use App\Models\Trip;
use Illuminate\Console\Command;

class PurgeExpiredTrips extends Command
{
    protected $signature = 'trips:purge-expired';
    protected $description = 'Permanently delete trips that have been in Recently Deleted for more than 7 days.';

    public function handle(): int
    {
        $deleted = 0;

        Trip::onlyTrashed()
            ->where('deleted_at', '<=', now()->subDays(7))
            ->chunkById(100, function ($trips) use (&$deleted) {
                foreach ($trips as $trip) {
                    $trip->forceDelete();
                    $deleted++;
                }
            });

        $this->info("{$deleted} expired trip(s) permanently deleted.");
        return self::SUCCESS;
    }
}
