<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\MailLog;
use App\Models\Tenant;
use App\Models\User;
use App\Services\WarmupLimiter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function index(): View
    {
        return view('admin.tenants.index', [
            'tenants' => Tenant::query()->withCount(['domains', 'apiKeys', 'mailLogs'])->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.tenants.form', [
            'tenant' => new Tenant(['daily_limit' => 15, 'status' => 'active']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'daily_limit' => ['required', 'integer', 'min:1', 'max:10000'],
            'status' => ['required', 'in:active,suspended'],
            'owner_name' => ['required', 'string', 'max:100'],
            'owner_email' => ['required', 'email', 'unique:users,email'],
            'owner_password' => ['required', 'string', 'min:8'],
        ]);

        $tenant = Tenant::create([
            'name' => $validated['name'],
            'daily_limit' => $validated['daily_limit'],
            'status' => $validated['status'],
        ]);

        User::create([
            'name' => $validated['owner_name'],
            'email' => $validated['owner_email'],
            'password' => Hash::make($validated['owner_password']),
            'role' => 'tenant',
            'tenant_id' => $tenant->id,
        ]);

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant account created.');
    }

    public function show(Tenant $tenant, WarmupLimiter $warmupLimiter): View
    {
        $tenant->load(['users', 'domains', 'apiKeys']);

        return view('admin.tenants.show', [
            'tenant' => $tenant,
            'stats' => [
                'sent_today' => MailLog::query()
                    ->where('tenant_id', $tenant->id)
                    ->whereDate('created_at', today())
                    ->where('status', 'sent')
                    ->count(),
                'failed_today' => MailLog::query()
                    ->where('tenant_id', $tenant->id)
                    ->whereDate('created_at', today())
                    ->where('status', 'failed')
                    ->count(),
                'remaining' => $warmupLimiter->remaining($tenant),
                'daily_cap' => $warmupLimiter->dailyCap($tenant),
                'active_campaigns' => Campaign::query()
                    ->where('tenant_id', $tenant->id)
                    ->whereIn('status', ['running', 'scheduled', 'paused'])
                    ->count(),
            ],
            'recentLogs' => MailLog::query()
                ->where('tenant_id', $tenant->id)
                ->latest()
                ->limit(15)
                ->get(),
            'recentCampaigns' => Campaign::query()
                ->where('tenant_id', $tenant->id)
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }

    public function edit(Tenant $tenant): View
    {
        return view('admin.tenants.form', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'daily_limit' => ['required', 'integer', 'min:1', 'max:10000'],
            'status' => ['required', 'in:active,suspended'],
        ]);

        $tenant->update($validated);

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant updated.');
    }
}
