<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TemplateController extends Controller
{
    public function index(Request $request): View
    {
        $query = Template::query()->orderBy('slug');

        if (! $request->user()->isSuperAdmin()) {
            $query->where(function ($builder) use ($request) {
                $builder->whereNull('tenant_id')
                    ->orWhere('tenant_id', $request->user()->tenant_id);
            });
        }

        return view('admin.templates.index', [
            'templates' => $query->get(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.templates.form', [
            'template' => new Template(['type' => 'transactional']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $tenantId = $request->user()->isSuperAdmin() ? null : $request->user()->tenant_id;

        $this->ensureUniqueSlug($validated['slug'], $tenantId);

        Template::create([
            ...$validated,
            'tenant_id' => $tenantId,
        ]);

        return redirect()->route('admin.templates.index')->with('success', 'Template created.');
    }

    public function edit(Request $request, Template $template): View
    {
        $this->authorizeTemplate($request, $template);

        return view('admin.templates.form', compact('template'));
    }

    public function update(Request $request, Template $template): RedirectResponse
    {
        $this->authorizeTemplate($request, $template);

        $validated = $this->validated($request, $template);
        $this->ensureUniqueSlug($validated['slug'], $template->tenant_id, $template->id);

        $template->update($validated);

        return redirect()->route('admin.templates.index')->with('success', 'Template updated.');
    }

    public function destroy(Request $request, Template $template): RedirectResponse
    {
        $this->authorizeTemplate($request, $template);

        if ($template->tenant_id === null && ! $request->user()->isSuperAdmin()) {
            abort(403);
        }

        $template->delete();

        return redirect()->route('admin.templates.index')->with('success', 'Template deleted.');
    }

    private function validated(Request $request, ?Template $template = null): array
    {
        return $request->validate([
            'slug' => ['required', 'string', 'max:100', 'alpha_dash'],
            'name' => ['required', 'string', 'max:100'],
            'subject' => ['required', 'string', 'max:255'],
            'html_body' => ['required', 'string'],
            'text_body' => ['nullable', 'string'],
            'type' => ['required', 'in:transactional,promo'],
        ]);
    }

    private function ensureUniqueSlug(string $slug, ?int $tenantId, ?int $ignoreId = null): void
    {
        $query = Template::query()->where('slug', $slug);

        if ($tenantId === null) {
            $query->whereNull('tenant_id');
        } else {
            $query->where('tenant_id', $tenantId);
        }

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'slug' => 'This template slug already exists.',
            ]);
        }
    }

    private function authorizeTemplate(Request $request, Template $template): void
    {
        if ($request->user()->isSuperAdmin()) {
            return;
        }

        if ($template->tenant_id !== null && $template->tenant_id !== $request->user()->tenant_id) {
            abort(403);
        }
    }
}
