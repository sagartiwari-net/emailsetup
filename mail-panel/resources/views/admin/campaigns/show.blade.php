@extends('layouts.admin')

@section('title', $campaign->name)

@section('content')
    <header class="header">
        <div class="header-left">
            <h1>{{ $campaign->name }}</h1>
            <p>{{ ucfirst($campaign->schedule_type) }} campaign · Run #{{ $campaign->run_number }}</p>
        </div>
        <div class="header-actions">
            @if ($campaign->status === 'running' || $campaign->status === 'scheduled')
                <form method="POST" action="{{ route('admin.campaigns.pause', $campaign) }}" style="display:inline;">@csrf<button class="btn btn-outline" type="submit"><i class="fas fa-pause"></i> Pause</button></form>
            @endif
            @if ($campaign->status === 'paused')
                <form method="POST" action="{{ route('admin.campaigns.resume', $campaign) }}" style="display:inline;">@csrf<button class="btn btn-success" type="submit"><i class="fas fa-play"></i> Resume</button></form>
            @endif
            @if (! in_array($campaign->status, ['completed', 'cancelled']))
                <form method="POST" action="{{ route('admin.campaigns.cancel', $campaign) }}" style="display:inline;">@csrf<button class="btn btn-danger" type="submit" onclick="return confirm('Cancel campaign?')"><i class="fas fa-times"></i> Cancel</button></form>
            @endif
        </div>
    </header>

    <div class="stats-grid">
        <div class="stat-card green">
            <div class="stat-label">Sent</div>
            <div class="stat-value">{{ $campaign->sent_count }}</div>
            <div class="stat-icon"><i class="fas fa-check"></i></div>
        </div>
        <div class="stat-card pink">
            <div class="stat-label">Failed</div>
            <div class="stat-value">{{ $campaign->failed_count }}</div>
            <div class="stat-icon"><i class="fas fa-times"></i></div>
        </div>
        <div class="stat-card blue">
            <div class="stat-label">Skipped</div>
            <div class="stat-value">{{ $campaign->skipped_count }}</div>
            <div class="stat-icon"><i class="fas fa-forward"></i></div>
        </div>
        <div class="stat-card purple">
            <div class="stat-label">Status</div>
            <div class="stat-value" style="font-size:18px;">{{ ucfirst($campaign->status) }}</div>
            <div class="stat-icon"><i class="fas fa-flag"></i></div>
        </div>
    </div>

    <div class="stats-grid" style="margin-top:-8px;">
        <div class="stat-card" style="border-left:3px solid #f59e0b;">
            <div class="stat-label">Bounced (list)</div>
            <div class="stat-value">{{ $subscriberStats['bounced'] }}</div>
        </div>
        <div class="stat-card" style="border-left:3px solid #6366f1;">
            <div class="stat-label">Unsubscribed (list)</div>
            <div class="stat-value">{{ $subscriberStats['unsubscribed'] }}</div>
        </div>
        <div class="stat-card" style="border-left:3px solid #10b981;">
            <div class="stat-label">Active (list)</div>
            <div class="stat-value">{{ $subscriberStats['active'] }}</div>
        </div>
        <div class="stat-card" style="border-left:3px solid #8b5cf6;">
            <div class="stat-label">Next Run</div>
            <div class="stat-value" style="font-size:16px;">{{ $campaign->next_run_at?->format('d M H:i') ?? '—' }}</div>
        </div>
    </div>

    <div class="page-grid-1-1">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Campaign Details</h3></div>
            <p><strong>List:</strong> {{ $campaign->subscriberList->name }}</p>
            <p><strong>Domain:</strong> {{ $campaign->apiKey->domain->domain_name }}</p>
            <p><strong>Template:</strong> <code>{{ $campaign->template_slug }}</code></p>
            <p><strong>Subject:</strong> {{ $campaign->subject ?? '(from template)' }}</p>
            <p><strong>Last batch:</strong> {{ $campaign->last_batch_at?->format('d M Y H:i') ?? '—' }}</p>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">Send Test</h3></div>
            <form method="POST" action="{{ route('admin.campaigns.test', $campaign) }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Your Email</label>
                    <input type="email" class="form-input" name="to_email" placeholder="you@gmail.com" required>
                </div>
                <button class="btn btn-purple btn-full" type="submit"><i class="fas fa-paper-plane"></i> Send Test</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Recent Campaign Logs</h3></div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Subscriber</th>
                    <th>Run</th>
                    <th>Status</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($campaign->logs as $log)
                    <tr>
                        <td>{{ $log->subscriber?->email ?? '—' }}</td>
                        <td>#{{ $log->run_number }}</td>
                        <td><span class="status-badge {{ $log->status === 'sent' ? 'sent' : 'failed' }}">{{ ucfirst($log->status) }}</span></td>
                        <td>{{ $log->sent_at?->format('d M H:i') ?? $log->created_at->format('d M H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align:center;padding:24px;color:var(--text-secondary);">No logs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
