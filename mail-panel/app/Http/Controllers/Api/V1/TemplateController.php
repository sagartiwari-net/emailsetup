<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenant = $request->attributes->get('tenant');

        $templates = Template::query()
            ->where(function ($query) use ($tenant) {
                $query->whereNull('tenant_id')
                    ->orWhere('tenant_id', $tenant->id);
            })
            ->orderBy('slug')
            ->get(['id', 'slug', 'name', 'subject', 'type', 'tenant_id', 'updated_at']);

        return response()->json([
            'success' => true,
            'templates' => $templates->map(fn (Template $template) => [
                'slug' => $template->slug,
                'name' => $template->name,
                'subject' => $template->subject,
                'type' => $template->type,
                'scope' => $template->tenant_id ? 'tenant' : 'global',
                'updated_at' => $template->updated_at?->toIso8601String(),
            ]),
        ]);
    }

    public function sync(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9][a-z0-9\-_]*$/'],
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'html_body' => ['required', 'string'],
            'text_body' => ['nullable', 'string'],
            'type' => ['required', 'string', 'in:transactional,promo'],
        ]);

        $tenant = $request->attributes->get('tenant');

        $template = Template::query()->updateOrCreate(
            [
                'slug' => $validated['slug'],
                'tenant_id' => $tenant->id,
            ],
            [
                'name' => $validated['name'],
                'subject' => $validated['subject'],
                'html_body' => $validated['html_body'],
                'text_body' => $validated['text_body'] ?? null,
                'type' => $validated['type'],
            ],
        );

        return response()->json([
            'success' => true,
            'slug' => $template->slug,
            'scope' => 'tenant',
            'updated_at' => $template->updated_at?->toIso8601String(),
        ]);
    }

    public function destroy(Request $request, string $slug): JsonResponse
    {
        $tenant = $request->attributes->get('tenant');

        $template = Template::query()
            ->where('slug', $slug)
            ->where('tenant_id', $tenant->id)
            ->first();

        if (! $template) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found or not owned by this account.',
            ], 404);
        }

        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'Template deleted.',
        ]);
    }
}
