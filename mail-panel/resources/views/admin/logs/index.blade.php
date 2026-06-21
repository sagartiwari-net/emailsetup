@extends('layouts.admin')

@section('title', 'Mail Logs')

@section('content')
    <header class="header">
        <div class="header-left">
            <h1>Mail Logs</h1>
            <p>View and track all sent emails</p>
        </div>
    </header>

    <div class="card">
        <form method="GET" action="{{ route('admin.logs.index') }}">
            <div class="filter-section">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search email, subject, message ID…">
                </div>
                <select class="filter-select" name="status">
                    <option value="">All Status</option>
                    @foreach (['sent', 'failed', 'queued'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <button class="btn btn-purple" type="submit"><i class="fas fa-filter"></i> Filter</button>
            </div>
        </form>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Message ID</th>
                    <th>To</th>
                    <th>Subject</th>
                    <th>Template</th>
                    <th>Status</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td><small style="color:var(--text-secondary);">{{ Str::limit($log->message_id, 16) }}</small></td>
                        <td><strong>{{ $log->to_email }}</strong></td>
                        <td>{{ Str::limit($log->subject, 35) }}</td>
                        <td><code>{{ $log->template_slug }}</code></td>
                        <td><span class="status-badge {{ $log->status }}">{{ ucfirst($log->status) }}</span></td>
                        <td>{{ $log->created_at->format('d M Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;color:var(--text-secondary);padding:32px;">No logs found.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination-wrap">
            {{ $logs->links() }}
        </div>
    </div>
@endsection
