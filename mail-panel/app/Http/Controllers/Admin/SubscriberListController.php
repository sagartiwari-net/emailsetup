<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriberList;
use App\Models\Tenant;
use App\Services\SubscriberImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriberListController extends Controller
{
    public function index(Request $request): View
    {
        $query = SubscriberList::query()->withCount([
            'subscribers',
            'subscribers as active_subscribers_count' => fn ($q) => $q->where('status', 'active'),
        ])->latest();

        if (! $request->user()->isSuperAdmin()) {
            $query->where('tenant_id', $request->user()->tenant_id);
        }

        return view('admin.subscribers.index', [
            'lists' => $query->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $tenantId = $request->user()->isSuperAdmin()
            ? ($request->user()->tenant_id ?? Tenant::query()->value('id'))
            : $request->user()->tenant_id;

        SubscriberList::create([
            'tenant_id' => $tenantId,
            'name' => $validated['name'],
        ]);

        return redirect()->route('admin.subscribers.index')->with('success', 'Subscriber list created.');
    }

    public function show(Request $request, SubscriberList $subscriberList): View
    {
        $this->authorizeList($request, $subscriberList);

        return view('admin.subscribers.show', [
            'list' => $subscriberList->loadCount('subscribers'),
            'subscribers' => $subscriberList->subscribers()->latest()->paginate(30),
        ]);
    }

    public function import(Request $request, SubscriberList $subscriberList, SubscriberImportService $importService): RedirectResponse
    {
        $this->authorizeList($request, $subscriberList);

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $result = $importService->importFromCsv($subscriberList, $request->file('csv_file'));

        return back()->with('success', "Import done: {$result['added']} added, {$result['skipped']} skipped, {$result['invalid']} invalid.");
    }

    public function addSubscriber(Request $request, SubscriberList $subscriberList, SubscriberImportService $importService): RedirectResponse
    {
        $this->authorizeList($request, $subscriberList);

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'name' => ['nullable', 'string', 'max:100'],
        ]);

        $importService->addManual($subscriberList, $validated['email'], $validated['name'] ?? null);

        return back()->with('success', 'Subscriber added.');
    }

    public function destroySubscriber(Request $request, SubscriberList $subscriberList, int $subscriber): RedirectResponse
    {
        $this->authorizeList($request, $subscriberList);

        $subscriberList->subscribers()->where('id', $subscriber)->delete();

        return back()->with('success', 'Subscriber removed.');
    }

    private function authorizeList(Request $request, SubscriberList $list): void
    {
        if ($request->user()->isSuperAdmin()) {
            return;
        }

        if ($list->tenant_id !== $request->user()->tenant_id) {
            abort(403);
        }
    }
}
