<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\Domain;
use App\Services\MailSendService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestMailController extends Controller
{
    public function create(Request $request): View
    {
        $apiKeys = ApiKey::query()
            ->with('domain')
            ->where('is_active', true)
            ->when(! $request->user()->isSuperAdmin(), fn ($q) => $q->where('tenant_id', $request->user()->tenant_id))
            ->get();

        return view('admin.test-mail', [
            'apiKeys' => $apiKeys,
            'templates' => ['otp', 'welcome', 'promo'],
        ]);
    }

    public function send(Request $request, MailSendService $mailSendService): RedirectResponse
    {
        $validated = $request->validate([
            'api_key_id' => ['required', 'exists:api_keys,id'],
            'to_email' => ['required', 'email'],
            'template' => ['required', 'string'],
        ]);

        $apiKey = ApiKey::query()->with(['tenant', 'domain'])->findOrFail($validated['api_key_id']);

        if (! $request->user()->isSuperAdmin() && $apiKey->tenant_id !== $request->user()->tenant_id) {
            abort(403);
        }

        $data = match ($validated['template']) {
            'otp' => ['otp' => '123456', 'name' => 'Test User', 'minutes' => 10],
            'welcome' => ['name' => 'Test User', 'site_name' => $apiKey->domain->domain_name],
            'promo' => [
                'name' => 'Test User',
                'subject_line' => 'Test Promo',
                'message' => 'This is a test promotional email.',
                'unsubscribe_url' => url('/unsubscribe/test'),
            ],
            default => ['name' => 'Test User'],
        };

        try {
            $log = $mailSendService->queueSend(
                apiKey: $apiKey,
                to: $validated['to_email'],
                templateSlug: $validated['template'],
                data: $data,
                useQueue: false,
            );

            return back()->with('success', "Test mail {$log->status}. Message ID: {$log->message_id}");
        } catch (\Throwable $e) {
            return back()->withErrors(['to_email' => $e->getMessage()]);
        }
    }
}
