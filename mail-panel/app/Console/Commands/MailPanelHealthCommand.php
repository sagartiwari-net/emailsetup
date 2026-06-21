<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Models\MailLog;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('mail-panel:health')]
#[Description('Check Mail Panel health: DB, queue, campaigns, disk')]
class MailPanelHealthCommand extends Command
{
    public function handle(): int
    {
        $checks = [];

        try {
            DB::connection()->getPdo();
            $checks[] = ['Database', 'OK', 'green'];
        } catch (\Throwable $e) {
            $checks[] = ['Database', 'FAIL: '.$e->getMessage(), 'red'];

            return $this->render($checks);
        }

        $queueSize = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();
        $checks[] = ['Queue pending', (string) $queueSize, $queueSize > 500 ? 'yellow' : 'green'];
        $checks[] = ['Failed jobs', (string) $failedJobs, $failedJobs > 0 ? 'yellow' : 'green'];

        $stuckCampaigns = Campaign::query()
            ->where('status', 'running')
            ->where('updated_at', '<', now()->subHours(6))
            ->count();
        $checks[] = ['Stuck campaigns (>6h)', (string) $stuckCampaigns, $stuckCampaigns > 0 ? 'yellow' : 'green'];

        $todayFailed = MailLog::query()->whereDate('created_at', today())->where('status', 'failed')->count();
        $checks[] = ['Failed sends today', (string) $todayFailed, $todayFailed > 10 ? 'yellow' : 'green'];

        $storagePath = storage_path('logs');
        $checks[] = ['Storage writable', is_writable($storagePath) ? 'OK' : 'FAIL', is_writable($storagePath) ? 'green' : 'red'];

        $queueDriver = config('queue.default');
        $checks[] = ['Queue driver', $queueDriver, 'green'];

        return $this->render($checks);
    }

    private function render(array $checks): int
    {
        $this->info('Mail Panel Health Check');
        $this->newLine();

        $hasFailure = false;

        foreach ($checks as [$label, $value, $color]) {
            $line = str_pad($label, 24).$value;

            match ($color) {
                'red' => $this->error($line),
                'yellow' => $this->warn($line),
                default => $this->line($line),
            };

            if ($color === 'red') {
                $hasFailure = true;
            }
        }

        $this->newLine();

        if ($hasFailure) {
            $this->error('Health check failed.');

            return self::FAILURE;
        }

        $this->info('All checks passed.');

        return self::SUCCESS;
    }
}
