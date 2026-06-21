<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MailLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MailLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = MailLog::query()->with(['domain'])->latest();

        if (! $request->user()->isSuperAdmin()) {
            $query->where('tenant_id', $request->user()->tenant_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($builder) use ($search) {
                $builder->where('to_email', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('message_id', 'like', "%{$search}%");
            });
        }

        return view('admin.logs.index', [
            'logs' => $query->paginate(20)->withQueryString(),
        ]);
    }
}
