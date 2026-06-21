<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DomainController extends Controller
{
    public function index(Request $request): View
    {
        $query = Domain::query()->with('tenant')->orderBy('domain_name');

        if (! $request->user()->isSuperAdmin()) {
            $query->where('tenant_id', $request->user()->tenant_id);
        }

        return view('admin.domains.index', [
            'domains' => $query->get(),
            'tenants' => $request->user()->isSuperAdmin()
                ? Tenant::query()->orderBy('name')->get()
                : collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'domain_name' => ['required', 'string', 'max:255', 'unique:domains,domain_name'],
            'from_email' => ['nullable', 'email', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'tenant_id' => ['nullable', 'exists:tenants,id'],
        ]);

        $user = $request->user();
        $tenantId = $user->isSuperAdmin()
            ? ($validated['tenant_id'] ?? $user->tenant_id)
            : $user->tenant_id;

        if (! $tenantId) {
            return back()->withErrors(['tenant_id' => 'Tenant is required.']);
        }

        Domain::create([
            'tenant_id' => $tenantId,
            'domain_name' => strtolower($validated['domain_name']),
            'from_email' => $validated['from_email'] ?? null,
            'from_name' => $validated['from_name'] ?? null,
        ]);

        return redirect()
            ->route('admin.domains.index')
            ->with('success', 'Domain added.');
    }

    public function update(Request $request, Domain $domain): RedirectResponse
    {
        if (! $request->user()->isSuperAdmin()) {
            if ($domain->tenant_id !== $request->user()->tenant_id) {
                abort(403);
            }
        }

        $validated = $request->validate([
            'from_email' => ['nullable', 'email', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'tenant_id' => ['nullable', 'exists:tenants,id'],
        ]);

        $updates = [
            'from_email' => $validated['from_email'] ?? $domain->from_email,
            'from_name' => $validated['from_name'] ?? $domain->from_name,
        ];

        if ($request->user()->isSuperAdmin() && ! empty($validated['tenant_id'])) {
            $updates['tenant_id'] = $validated['tenant_id'];
        }

        $domain->update($updates);

        return redirect()
            ->route('admin.domains.index')
            ->with('success', 'Domain updated.');
    }
}
