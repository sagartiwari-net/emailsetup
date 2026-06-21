<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignLog;
use App\Models\Subscriber;
use Carbon\Carbon;

class CampaignProcessorService
{
    public const BATCH_SIZE = 50;

    public function __construct(
        private readonly MailSendService $mailSendService,
        private readonly WarmupLimiter $warmupLimiter,
    ) {}

    public function activate(Campaign $campaign): void
    {
        $campaign->increment('run_number');

        $campaign->update([
            'status' => 'running',
            'started_at' => $campaign->started_at ?? now(),
            'last_run_at' => now(),
            'completed_at' => null,
        ]);
    }

    public function processDueCampaigns(): int
    {
        $processed = 0;

        Campaign::query()
            ->where(function ($query) {
                $query->where('status', 'running')
                    ->orWhere(function ($inner) {
                        $inner->where('status', 'scheduled')
                            ->where('next_run_at', '<=', now());
                    });
            })
            ->orderBy('id')
            ->each(function (Campaign $campaign) use (&$processed) {
                if ($campaign->status === 'scheduled') {
                    $this->activate($campaign);
                }

                if ($this->processBatch($campaign)) {
                    $processed++;
                }
            });

        return $processed;
    }

    public function processBatch(Campaign $campaign): bool
    {
        if (! $campaign->canProcessBatch()) {
            return false;
        }

        $campaign->load(['tenant', 'apiKey.domain', 'subscriberList']);

        if (! $campaign->tenant->isActive() || ! $campaign->apiKey?->is_active) {
            $campaign->update(['status' => 'paused']);

            return false;
        }

        $remaining = $this->warmupLimiter->remaining($campaign->tenant);

        if ($remaining <= 0) {
            return false;
        }

        $batchLimit = min(self::BATCH_SIZE, $remaining);

        $subscribers = Subscriber::query()
            ->where('subscriber_list_id', $campaign->subscriber_list_id)
            ->where('status', 'active')
            ->whereNotIn('id', function ($query) use ($campaign) {
                $query->select('subscriber_id')
                    ->from('campaign_logs')
                    ->where('campaign_id', $campaign->id)
                    ->where('run_number', $campaign->run_number)
                    ->whereIn('status', ['sent', 'pending']);
            })
            ->limit($batchLimit)
            ->get();

        if ($subscribers->isEmpty()) {
            $this->completeRun($campaign);

            return true;
        }

        foreach ($subscribers as $subscriber) {
            if (! $this->warmupLimiter->canSend($campaign->tenant)) {
                break;
            }

            $log = CampaignLog::create([
                'campaign_id' => $campaign->id,
                'subscriber_id' => $subscriber->id,
                'run_number' => $campaign->run_number,
                'status' => 'pending',
            ]);

            try {
                $mailLog = $this->mailSendService->queueSend(
                    apiKey: $campaign->apiKey,
                    to: $subscriber->email,
                    templateSlug: $campaign->template_slug,
                    data: [
                        'name' => $subscriber->name ?? 'User',
                        'email' => $subscriber->email,
                        'unsubscribe_url' => $subscriber->unsubscribeUrl(),
                        'subject_line' => $campaign->subject ?? 'Update from us',
                        'message' => $campaign->subject ?? 'We have an update for you.',
                    ],
                    subjectOverride: $campaign->subject,
                    useQueue: false,
                );

                $log->update([
                    'status' => $mailLog->status === 'sent' ? 'sent' : 'failed',
                    'mail_log_id' => $mailLog->id,
                    'sent_at' => $mailLog->sent_at,
                    'error' => $mailLog->error,
                ]);

                if ($mailLog->status === 'sent') {
                    $campaign->increment('sent_count');
                } else {
                    $campaign->increment('failed_count');
                }
            } catch (\Throwable $e) {
                $log->update([
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ]);
                $campaign->increment('failed_count');
            }
        }

        $campaign->update(['last_batch_at' => now()]);

        $pending = $this->pendingCount($campaign);

        if ($pending === 0) {
            $this->completeRun($campaign);
        }

        return true;
    }

    public function scheduleNextRun(Campaign $campaign): void
    {
        $config = $campaign->schedule_config ?? [];

        if ($campaign->schedule_type === 'once') {
            $campaign->update(['status' => 'completed', 'completed_at' => now(), 'next_run_at' => null]);

            return;
        }

        if ($campaign->schedule_type === 'now') {
            $campaign->update(['status' => 'completed', 'completed_at' => now(), 'next_run_at' => null]);

            return;
        }

        if ($campaign->schedule_type === 'recurring') {
            $intervalDays = (int) ($config['interval_days'] ?? 3);
            $sendTime = $config['send_time'] ?? '10:00';
            $endDate = isset($config['end_date']) ? Carbon::parse($config['end_date']) : null;

            $next = now()->addDays($intervalDays)->setTimeFromTimeString($sendTime);

            if ($endDate && $next->gt($endDate)) {
                $campaign->update(['status' => 'completed', 'completed_at' => now(), 'next_run_at' => null]);

                return;
            }

            $campaign->update([
                'status' => 'scheduled',
                'next_run_at' => $next,
                'completed_at' => null,
            ]);

            return;
        }

        $campaign->update(['status' => 'completed', 'completed_at' => now()]);
    }

    private function completeRun(Campaign $campaign): void
    {
        $campaign->refresh();
        $this->scheduleNextRun($campaign);
    }

    private function pendingCount(Campaign $campaign): int
    {
        return Subscriber::query()
            ->where('subscriber_list_id', $campaign->subscriber_list_id)
            ->where('status', 'active')
            ->whereNotIn('id', function ($query) use ($campaign) {
                $query->select('subscriber_id')
                    ->from('campaign_logs')
                    ->where('campaign_id', $campaign->id)
                    ->where('run_number', $campaign->run_number)
                    ->where('status', 'sent');
            })
            ->count();
    }

    public function computeInitialNextRun(string $scheduleType, array $config): ?Carbon
    {
        if ($scheduleType === 'now') {
            return now();
        }

        if ($scheduleType === 'once' && ! empty($config['send_at'])) {
            return Carbon::parse($config['send_at']);
        }

        if ($scheduleType === 'recurring') {
            $start = ! empty($config['start_date'])
                ? Carbon::parse($config['start_date'])
                : now();
            $time = $config['send_time'] ?? '10:00';

            return $start->setTimeFromTimeString($time);
        }

        return now();
    }
}
