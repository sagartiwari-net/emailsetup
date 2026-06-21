@extends('layouts.admin')

@section('title', 'Failed Jobs')

@section('content')
    <header class="header">
        <div class="header-left">
            <h1>Failed Jobs</h1>
            <p>Queue jobs that failed — retry or remove</p>
        </div>
    </header>

    <div class="card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Queue</th>
                    <th>Job</th>
                    <th>Failed At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jobs as $job)
                    <tr>
                        <td>{{ $job->queue }}</td>
                        <td><small>{{ Str::limit($job->exception, 80) }}</small></td>
                        <td>{{ \Carbon\Carbon::parse($job->failed_at)->format('d M Y H:i') }}</td>
                        <td style="display:flex;gap:8px;">
                            <form method="POST" action="{{ route('admin.failed-jobs.retry', $job->id) }}">@csrf<button class="btn btn-outline btn-sm" type="submit">Retry</button></form>
                            <form method="POST" action="{{ route('admin.failed-jobs.destroy', $job->id) }}">@csrf @method('DELETE')<button class="btn btn-danger btn-sm" type="submit">Delete</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align:center;padding:32px;color:var(--text-secondary);">No failed jobs. All good!</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
