<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\Trial;
use Illuminate\Console\Command;

class ExpireLines extends Command
{
    protected $signature = 'lines:expire';

    protected $description = 'Marks active subscriptions and trials past their end date as expired';

    public function handle(): int
    {
        // One by one so each change reaches the audit log.
        $expire = fn ($query): int => $query->where('status', 'active')->where('expires_at', '<', now())->get()
            ->each(fn ($record) => $record->update(['status' => 'expired']))
            ->count();

        $subscriptions = $expire(Subscription::query());
        $trials = $expire(Trial::query());

        $this->info("Expired {$subscriptions} subscriptions and {$trials} trials.");

        return self::SUCCESS;
    }
}
