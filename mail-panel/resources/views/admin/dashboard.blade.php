@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <header class="header">
        <div class="header-left">
            <h1>Dashboard</h1>
            <p>Welcome back! Here's your mail sending overview</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.test-mail.create') }}" class="btn btn-purple"><i class="fas fa-paper-plane"></i> Send Test</a>
            <a href="{{ route('admin.api-keys.index') }}" class="btn btn-outline"><i class="fas fa-key"></i> API Keys</a>
        </div>
    </header>

    @unless ($setupComplete)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list-check" style="color:var(--primary-purple);margin-right:8px;"></i> Quick Setup</h3>
            </div>
            @foreach ($setupSteps as $step)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--border-color);">
                    <span style="font-size:14px;">
                        @if ($step['done'])
                            <i class="fas fa-check-circle" style="color:#059669;margin-right:8px;"></i>
                        @else
                            <i class="far fa-circle" style="color:var(--text-secondary);margin-right:8px;"></i>
                        @endif
                        {{ $step['label'] }}
                    </span>
                    @unless ($step['done'])
                        <a href="{{ route($step['route']) }}" class="btn btn-outline btn-sm">Setup</a>
                    @endunless
                </div>
            @endforeach
        </div>
    @endunless

    <div class="dashboard-grid">
        <div class="card hero-card">
            <h3 class="card-title">Daily Send Cap</h3>
            @isset($stats['remaining'])
                <div class="hero-value">{{ $stats['remaining'] }} <span style="font-size:16px;font-weight:500;opacity:0.7;">remaining</span></div>
                <div class="hero-meta">
                    <i class="fas fa-chart-line"></i>
                    <span>{{ $stats['sent_today'] ?? 0 }} / {{ $stats['daily_cap'] }} sent today</span>
                </div>
            @else
                <div class="hero-value">{{ $stats['sent_today'] }}</div>
                <div class="hero-meta">
                    <i class="fas fa-paper-plane"></i>
                    <span>Total mails sent today across all accounts</span>
                </div>
            @endisset
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Mail Logs</h3>
                <a href="{{ route('admin.logs.index') }}" class="btn btn-outline btn-sm">View All</a>
            </div>
            <div class="transactions-list">
                @forelse ($recentLogs as $log)
                    <div class="transaction-item">
                        <div class="transaction-left">
                            <div class="transaction-icon {{ $log->status }}">
                                <i class="fas fa-{{ $log->status === 'sent' ? 'check' : ($log->status === 'failed' ? 'times' : 'clock') }}"></i>
                            </div>
                            <div class="transaction-info">
                                <h4>{{ Str::limit($log->subject, 40) }}</h4>
                                <p>{{ $log->to_email }} · {{ $log->created_at->format('d M, H:i') }}</p>
                            </div>
                        </div>
                        <span class="status-badge {{ $log->status }}">{{ ucfirst($log->status) }}</span>
                    </div>
                @empty
                    <p style="color:var(--text-secondary);padding:20px 0;text-align:center;">No mail logs yet. Send a test mail to get started.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card green">
            <div class="stat-label">Sent Today</div>
            <div class="stat-value">{{ $stats['sent_today'] }}</div>
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        </div>
        <div class="stat-card pink">
            <div class="stat-label">Failed Today</div>
            <div class="stat-value">{{ $stats['failed_today'] }}</div>
            <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
        </div>
        <div class="stat-card blue">
            <div class="stat-label">Queued Today</div>
            <div class="stat-value">{{ $stats['queued_today'] }}</div>
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
        </div>
        <div class="stat-card purple">
            <div class="stat-label">Active Domains</div>
            <div class="stat-value">{{ $stats['domains'] }}</div>
            <div class="stat-icon"><i class="fas fa-globe"></i></div>
        </div>
    </div>

    <div class="two-col-grid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Today by Domain</h3>
            </div>
            @forelse ($domainStats as $domainName => $rows)
                <div style="margin-bottom:16px;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                        <span style="font-size:14px;font-weight:600;"><i class="fas fa-globe" style="color:var(--primary-purple);margin-right:6px;"></i>{{ $domainName }}</span>
                    </div>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        @foreach ($rows as $row)
                            <span class="status-badge {{ $row->status }}">{{ ucfirst($row->status) }}: {{ $row->total }}</span>
                        @endforeach
                    </div>
                </div>
            @empty
                <p style="color:var(--text-secondary);font-size:14px;">No sends today yet.</p>
            @endforelse
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <a href="{{ route('admin.domains.index') }}" class="btn btn-outline" style="justify-content:center;padding:16px;">
                    <i class="fas fa-plus"></i> Add Domain
                </a>
                <a href="{{ route('admin.api-keys.index') }}" class="btn btn-success" style="justify-content:center;padding:16px;">
                    <i class="fas fa-key"></i> New API Key
                </a>
                <a href="{{ route('admin.templates.index') }}" class="btn btn-outline" style="justify-content:center;padding:16px;">
                    <i class="fas fa-file-code"></i> Templates
                </a>
                <a href="{{ route('admin.campaigns.create') }}" class="btn btn-purple" style="justify-content:center;padding:16px;">
                    <i class="fas fa-bullhorn"></i> New Campaign
                </a>
                <a href="{{ route('admin.subscribers.index') }}" class="btn btn-outline" style="justify-content:center;padding:16px;">
                    <i class="fas fa-address-book"></i> Subscribers
                </a>
            </div>
        </div>
    </div>
@endsection
