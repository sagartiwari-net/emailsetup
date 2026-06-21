<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\Domain;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiKeyController extends Controller
{
    public function index(Request $request): View
    {
        $query = ApiKey::query()->with(['domain', 'tenant'])->latest();

        if (! $request->user()->isSuperAdmin()) {
            $query->where('tenant_id', $request->user()->tenant_id);
        }

        return view('admin.api-keys.index', [
            'apiKeys' => $query->get(),
            'domains' => $this->availableDomains($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'domain_id' => ['required', 'exists:domains,id'],
        ]);

        $domain = Domain::query()->findOrFail($validated['domain_id']);
        $user = $request->user();

        if (! $user->isSuperAdmin() && $domain->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $generated = ApiKey::generate($validated['label'], $domain->tenant, $domain);

        return redirect()
            ->route('admin.api-keys.index')
            ->with('new_api_key', $generated['plain_key'])
            ->with('success', 'API key created. Copy it now — it will not be shown again.');
    }

    public function destroy(Request $request, ApiKey $apiKey): RedirectResponse
    {
        $this->authorizeKey($request, $apiKey);

        $apiKey->update(['is_active' => false]);

        return redirect()
            ->route('admin.api-keys.index')
            ->with('success', 'API key revoked.');
    }

    private function authorizeKey(Request $request, ApiKey $apiKey): void
    {
        if ($request->user()->isSuperAdmin()) {
            return;
        }

        if ($apiKey->tenant_id !== $request->user()->tenant_id) {
            abort(403);
        }
    }

    private function availableDomains(Request $request)
    {
        return Domain::query()
            ->when(! $request->user()->isSuperAdmin(), fn ($q) => $q->where('tenant_id', $request->user()->tenant_id))
            ->orderBy('domain_name')
            ->get();
    }
}
