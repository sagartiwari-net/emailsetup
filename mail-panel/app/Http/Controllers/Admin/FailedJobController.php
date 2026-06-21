<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FailedJobController extends Controller
{
    public function index(): View
    {
        $jobs = DB::table('failed_jobs')->orderByDesc('failed_at')->limit(50)->get();

        return view('admin.failed-jobs.index', compact('jobs'));
    }

    public function retry(int $id): RedirectResponse
    {
        $job = DB::table('failed_jobs')->where('id', $id)->first();

        if ($job) {
            Artisan::call('queue:retry', ['id' => [$job->uuid]]);
        }

        return back()->with('success', 'Job queued for retry.');
    }

    public function destroy(int $id): RedirectResponse
    {
        DB::table('failed_jobs')->where('id', $id)->delete();

        return back()->with('success', 'Failed job removed.');
    }
}
