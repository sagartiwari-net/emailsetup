@extends('layouts.admin')

@section('title', $tenant->name)

@section('content')
    <header class="header">
        <div class="header-left">
            <h1>{{ $tenant->name }}</h1>
            <p>Tenant activity overview</p>
        </div>
        <div class="header-actions">
            @if ($tenant->status === 'active')
                <form method="POST" action="{{ route('admin.tenants.update', $tenant) }}" style="display:inline;" onsubmit="return confirm('Suspend this tenant?')">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="name" value="{{ $tenant->name }}">
                    <input type="hidden" name="daily_limit" value="{{ $tenant->daily_limit }}">
                    <input type="hidden" name="status" value="suspended">
                    <button class="btn btn-danger" type="submit"><i class="fas fa-ban"></i> Suspend</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.tenants.update', $tenant) }}" style="display:inline;">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="name" value="{{ $tenant->name }}">
                    <input type="hidden" name="daily_limit" value="{{ $tenant->daily_limit }}">
                    <input type="hidden" name="status" value="active">
                    <button class="btn btn-success" type="submit"><i class="fas fa-check"></i> Activate</button>
                </form>
            @endif
            <a href="{{ route('admin.tenants.edit', $tenant) }}" class="btn btn-outline"><i class="fas fa-cog"></i> Edit</a>
            <a href="{{ route('admin.tenants.index') }}" class="btn btn-purple"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </header>

    <div class="stats-grid">
        <div class="stat-card green">
            <div class="stat-label">Sent Today</div>
            <div class="stat-value">{{ $stats['sent_today'] }}</div>
            <div class="stat-icon"><i class="fas fa-paper-plane"></i></div>
        </div>
        <div class="stat-card pink">
            <div class="stat-label">Failed Today</div>
            <div class="stat-value">{{ $stats['failed_today'] }}</div>
            <div class="stat-icon"><i class="fas fa-times"></i></div>
        </div>
        <div class="stat-card blue">
            <div class="stat-label">Daily Cap</div>
            <div class="stat-value">{{ $stats['remaining'] }}/{{ $stats['daily_cap'] }}</div>
            <div class="stat-icon"><i class="fas fa-gauge"></i></div>
        </div>
        <div class="stat-card purple">
            <div class="stat-label">Status</div>
            <div class="stat-value" style="font-size:18px;">{{ ucfirst($tenant->status) }}</div>
            <div class="stat-icon"><i class="fas fa-user"></i></div>
        </div>
    </div>

    <div class="page-grid-1-1">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Assigned Resources</h3></div>
            <p><strong>Domains:</strong> {{ $tenant->domains->pluck('domain_name')->join(', ') ?: 'None' }}</p>
            <p><strong>API Keys:</strong> {{ $tenant->apiKeys->count() }}</p>
            <p><strong>Active Campaigns:</strong> {{ $stats['active_campaigns'] }}</p>
            <p><strong>Users:</strong> {{ $tenant->users->pluck('email')->join(', ') }}</p>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">Recent Campaigns</h3></div>
            @forelse ($recentCampaigns as $campaign)
                <p style="margin-bottom:8px;">
                    <a href="{{ route('admin.campaigns.show', $campaign) }}">{{ $campaign->name }}</a>
                    · <span class="status-badge {{ $campaign->status === 'completed' ? 'sent' : 'pending' }}">{{ ucfirst($campaign->status) }}</span>
                </p>
            @empty
                <p style="color:var(--text-secondary);">No campaigns yet.</p>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Recent Mail Logs</h3></div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>To</th>
                    <th>Template</th>
                    <th>Status</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentLogs as $log)
                    <tr>
                        <td>{{ $log->to_email }}</td>
                        <td><code>{{ $log->template_slug }}</code></td>
                        <td><span class="status-badge {{ $log->status === 'sent' ? 'sent' : 'failed' }}">{{ ucfirst($log->status) }}</span></td>
                        <td>{{ $log->created_at->format('d M H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align:center;padding:24px;color:var(--text-secondary);">No mail logs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
