<?php

namespace App\Services;

use App\Models\DailySendCount;
use App\Models\Tenant;
use Carbon\Carbon;

class WarmupLimiter
{
    public function dailyCap(Tenant $tenant): int
    {
        return $tenant->daily_limit ?: (int) config('mail-system.default_daily_cap', 15);
    }

    public function todayCount(Tenant $tenant): int
    {
        return (int) DailySendCount::query()
            ->where('tenant_id', $tenant->id)
            ->whereDate('date', Carbon::today())
            ->value('count');
    }

    public function remaining(Tenant $tenant): int
    {
        return max(0, $this->dailyCap($tenant) - $this->todayCount($tenant));
    }

    public function canSend(Tenant $tenant, int $count = 1): bool
    {
        if (! $tenant->isActive()) {
            return false;
        }

        return $this->remaining($tenant) >= $count;
    }

    public function increment(Tenant $tenant, int $count = 1): void
    {
        $record = DailySendCount::query()->firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'date' => Carbon::today()->toDateString(),
            ],
            ['count' => 0]
        );

        $record->increment('count', $count);
    }
}
