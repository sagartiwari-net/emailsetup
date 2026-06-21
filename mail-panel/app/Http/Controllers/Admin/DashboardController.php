<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\Domain;
use App\Models\MailLog;
use App\Models\SubscriberList;
use App\Models\Website;
use App\Services\WarmupLimiter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, WarmupLimiter $warmupLimiter): View
    {
        $user = $request->user();
        $tenantId = $user->isSuperAdmin() ? null : $user->tenant_id;

        $logsQuery = MailLog::query()->latest();

        if ($tenantId) {
            $logsQuery->where('tenant_id', $tenantId);
        }

        $todayQuery = MailLog::query()->whereDate('created_at', today());

        if ($tenantId) {
            $todayQuery->where('tenant_id', $tenantId);
        }

        $domainQuery = Domain::query()->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId));
        $apiKeyQuery = ApiKey::query()->where('is_active', true)->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId));
        $listQuery = SubscriberList::query()->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId));
        $websiteQuery = Website::query()->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId));

        $stats = [
            'sent_today' => (clone $todayQuery)->where('status', 'sent')->count(),
            'failed_today' => (clone $todayQuery)->where('status', 'failed')->count(),
            'queued_today' => (clone $todayQuery)->where('status', 'queued')->count(),
            'domains' => (clone $domainQuery)->count(),
        ];

        $setupSteps = [
            ['label' => 'Add your domain', 'done' => $domainQuery->exists(), 'route' => 'admin.domains.index'],
            ['label' => 'Generate API key', 'done' => $apiKeyQuery->exists(), 'route' => 'admin.api-keys.index'],
            ['label' => 'Register website', 'done' => $websiteQuery->exists(), 'route' => 'admin.websites.index'],
            ['label' => 'Create subscriber list', 'done' => $listQuery->exists(), 'route' => 'admin.subscribers.index'],
            ['label' => 'Send test mail', 'done' => (clone $todayQuery)->where('status', 'sent')->exists(), 'route' => 'admin.test-mail.create'],
        ];
        $setupComplete = collect($setupSteps)->every(fn ($s) => $s['done']);

        if ($user->tenant) {
            $stats['daily_cap'] = $warmupLimiter->dailyCap($user->tenant);
            $stats['remaining'] = $warmupLimiter->remaining($user->tenant);
            $stats['sent_total'] = $warmupLimiter->todayCount($user->tenant);
        }

        $domainStats = MailLog::query()
            ->selectRaw('domain_id, status, count(*) as total')
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereDate('created_at', today())
            ->groupBy('domain_id', 'status')
            ->with('domain')
            ->get()
            ->groupBy(fn ($row) => $row->domain?->domain_name ?? 'Unknown');

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentLogs' => $logsQuery->limit(10)->get(),
            'domainStats' => $domainStats,
            'setupSteps' => $setupSteps,
            'setupComplete' => $setupComplete,
        ]);
    }
}
