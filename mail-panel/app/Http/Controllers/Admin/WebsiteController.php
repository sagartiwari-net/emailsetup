<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\Domain;
use App\Models\Tenant;
use App\Models\Website;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebsiteController extends Controller
{
    public function index(Request $request): View
    {
        $query = Website::query()
            ->with(['domain', 'apiKey', 'tenant'])
            ->orderBy('name');

        if (! $request->user()->isSuperAdmin()) {
            $query->where('tenant_id', $request->user()->tenant_id);
        }

        return view('admin.websites.index', [
            'websites' => $query->get(),
            'domains' => $this->domainsFor($request),
            'apiKeys' => $this->apiKeysFor($request),
            'tenants' => $request->user()->isSuperAdmin()
                ? Tenant::query()->orderBy('name')->get()
                : collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'url' => ['nullable', 'url', 'max:255'],
            'domain_id' => ['nullable', 'exists:domains,id'],
            'api_key_id' => ['nullable', 'exists:api_keys,id'],
            'integration_status' => ['required', 'in:pending,testing,connected'],
            'notes' => ['nullable', 'string', 'max:500'],
            'tenant_id' => ['nullable', 'exists:tenants,id'],
        ]);

        $tenantId = $this->resolveTenantId($request, $validated);

        Website::create([
            'tenant_id' => $tenantId,
            'name' => $validated['name'],
            'url' => $validated['url'] ?? null,
            'domain_id' => $validated['domain_id'] ?? null,
            'api_key_id' => $validated['api_key_id'] ?? null,
            'integration_status' => $validated['integration_status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('admin.websites.index')->with('success', 'Website registered.');
    }

    public function update(Request $request, Website $website): RedirectResponse
    {
        $this->authorizeWebsite($request, $website);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'url' => ['nullable', 'url', 'max:255'],
            'domain_id' => ['nullable', 'exists:domains,id'],
            'api_key_id' => ['nullable', 'exists:api_keys,id'],
            'integration_status' => ['required', 'in:pending,testing,connected'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $website->update($validated);

        return redirect()->route('admin.websites.index')->with('success', 'Website updated.');
    }

    public function destroy(Request $request, Website $website): RedirectResponse
    {
        $this->authorizeWebsite($request, $website);
        $website->delete();

        return redirect()->route('admin.websites.index')->with('success', 'Website removed.');
    }

    private function domainsFor(Request $request)
    {
        return Domain::query()
            ->when(! $request->user()->isSuperAdmin(), fn ($q) => $q->where('tenant_id', $request->user()->tenant_id))
            ->orderBy('domain_name')
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

    private function resolveTenantId(Request $request, array $validated): int
    {
        if ($request->user()->isSuperAdmin()) {
            if (! empty($validated['tenant_id'])) {
                return (int) $validated['tenant_id'];
            }

            if (! empty($validated['domain_id'])) {
                return (int) Domain::find($validated['domain_id'])?->tenant_id;
            }

            if (! empty($validated['api_key_id'])) {
                return (int) ApiKey::find($validated['api_key_id'])?->tenant_id;
            }
        }

        return $request->user()->tenant_id;
    }

    private function authorizeWebsite(Request $request, Website $website): void
    {
        if ($request->user()->isSuperAdmin()) {
            return;
        }

        if ($website->tenant_id !== $request->user()->tenant_id) {
            abort(403);
        }
    }
}
