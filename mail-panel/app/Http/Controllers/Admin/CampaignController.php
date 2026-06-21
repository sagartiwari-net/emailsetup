<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\Campaign;
use App\Models\Subscriber;
use App\Models\SubscriberList;
use App\Models\Template;
use App\Services\CampaignProcessorService;
use App\Services\MailSendService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CampaignController extends Controller
{
    public function index(Request $request): View
    {
        $query = Campaign::query()
            ->with(['subscriberList', 'apiKey.domain'])
            ->latest();

        if (! $request->user()->isSuperAdmin()) {
            $query->where('tenant_id', $request->user()->tenant_id);
        }

        return view('admin.campaigns.index', [
            'campaigns' => $query->get(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.campaigns.form', [
            'campaign' => new Campaign(['schedule_type' => 'now', 'status' => 'draft']),
            'lists' => $this->listsFor($request),
            'apiKeys' => $this->apiKeysFor($request),
            'templates' => $this->templatesFor($request),
        ]);
    }

    public function store(Request $request, CampaignProcessorService $processor): RedirectResponse
    {
        $validated = $this->validated($request);
        $tenantId = $this->tenantId($request);

        $scheduleConfig = $this->buildScheduleConfig($request);
        $nextRun = $processor->computeInitialNextRun($validated['schedule_type'], $scheduleConfig);

        $status = match ($validated['schedule_type']) {
            'now' => 'running',
            'once', 'recurring' => $nextRun->lte(now()) ? 'running' : 'scheduled',
            default => 'draft',
        };

        $campaign = Campaign::create([
            'tenant_id' => $tenantId,
            'subscriber_list_id' => $validated['subscriber_list_id'],
            'api_key_id' => $validated['api_key_id'],
            'name' => $validated['name'],
            'template_slug' => $validated['template_slug'],
            'subject' => $validated['subject'] ?? null,
            'schedule_type' => $validated['schedule_type'],
            'schedule_config' => $scheduleConfig,
            'status' => $status,
            'next_run_at' => $status === 'scheduled' ? $nextRun : null,
        ]);

        if ($status === 'running') {
            $processor->activate($campaign);
            $processor->processBatch($campaign->fresh());
        }

        return redirect()->route('admin.campaigns.show', $campaign)->with('success', 'Campaign created.');
    }

    public function show(Request $request, Campaign $campaign): View
    {
        $this->authorizeCampaign($request, $campaign);

        $listId = $campaign->subscriber_list_id;

        return view('admin.campaigns.show', [
            'campaign' => $campaign->load([
                'subscriberList',
                'apiKey.domain',
                'logs' => fn ($q) => $q->with('subscriber')->latest()->limit(20),
            ]),
            'subscriberStats' => [
                'active' => Subscriber::query()->where('subscriber_list_id', $listId)->where('status', 'active')->count(),
                'bounced' => Subscriber::query()->where('subscriber_list_id', $listId)->where('status', 'bounced')->count(),
                'unsubscribed' => Subscriber::query()->where('subscriber_list_id', $listId)->where('status', 'unsubscribed')->count(),
            ],
        ]);
    }

    public function testSend(Request $request, Campaign $campaign, MailSendService $mailSendService): RedirectResponse
    {
        $this->authorizeCampaign($request, $campaign);

        $validated = $request->validate(['to_email' => ['required', 'email']]);

        $apiKey = $campaign->apiKey;

        $mailSendService->queueSend(
            apiKey: $apiKey,
            to: $validated['to_email'],
            templateSlug: $campaign->template_slug,
            data: [
                'name' => 'Test User',
                'unsubscribe_url' => url('/unsubscribe/test'),
                'subject_line' => $campaign->subject ?? 'Test Campaign',
                'message' => 'This is a test campaign email.',
            ],
            subjectOverride: $campaign->subject,
            useQueue: false,
        );

        return back()->with('success', 'Test mail sent.');
    }

    public function pause(Request $request, Campaign $campaign): RedirectResponse
    {
        $this->authorizeCampaign($request, $campaign);
        $campaign->update(['status' => 'paused']);

        return back()->with('success', 'Campaign paused.');
    }

    public function resume(Request $request, Campaign $campaign, CampaignProcessorService $processor): RedirectResponse
    {
        $this->authorizeCampaign($request, $campaign);
        $campaign->update(['status' => 'running']);
        $processor->processBatch($campaign->fresh());

        return back()->with('success', 'Campaign resumed.');
    }

    public function cancel(Request $request, Campaign $campaign): RedirectResponse
    {
        $this->authorizeCampaign($request, $campaign);
        $campaign->update(['status' => 'cancelled', 'completed_at' => now()]);

        return back()->with('success', 'Campaign cancelled.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'subscriber_list_id' => ['required', 'exists:subscriber_lists,id'],
            'api_key_id' => ['required', 'exists:api_keys,id'],
            'template_slug' => ['required', 'string', 'max:100'],
            'subject' => ['nullable', 'string', 'max:255'],
            'schedule_type' => ['required', 'in:now,once,recurring'],
            'send_at' => ['nullable', 'date'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'interval_days' => ['nullable', 'integer', 'min:1', 'max:90'],
            'send_time' => ['nullable', 'date_format:H:i'],
        ]);
    }

    private function buildScheduleConfig(Request $request): array
    {
        return array_filter([
            'send_at' => $request->input('send_at'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'interval_days' => $request->input('interval_days', 3),
            'send_time' => $request->input('send_time', '10:00'),
        ]);
    }

    private function listsFor(Request $request)
    {
        return SubscriberList::query()
            ->when(! $request->user()->isSuperAdmin(), fn ($q) => $q->where('tenant_id', $request->user()->tenant_id))
            ->withCount(['subscribers as active_subscribers_count' => fn ($q) => $q->where('status', 'active')])
            ->get();
    }

    private function apiKeysFor(Request $request)
    {
        return ApiKey::query()
            ->with('domain')
            ->where('is_active', true)
            ->when(! $request->user()->isSuperAdmin(), fn ($q) => $q->where('tenant_id', $request->user()->tenant_id))
            ->get();
    }

    private function templatesFor(Request $request)
    {
        return Template::query()
            ->when(! $request->user()->isSuperAdmin(), function ($q) use ($request) {
                $q->where(function ($inner) use ($request) {
                    $inner->whereNull('tenant_id')->orWhere('tenant_id', $request->user()->tenant_id);
                });
            })
            ->orderBy('slug')
            ->get();
    }

    private function tenantId(Request $request): int
    {
        return $request->user()->isSuperAdmin()
            ? (SubscriberList::find($request->input('subscriber_list_id'))?->tenant_id ?? $request->user()->tenant_id)
            : $request->user()->tenant_id;
    }

    private function authorizeCampaign(Request $request, Campaign $campaign): void
    {
        if ($request->user()->isSuperAdmin()) {
            return;
        }

        if ($campaign->tenant_id !== $request->user()->tenant_id) {
            abort(403);
        }
    }
}
