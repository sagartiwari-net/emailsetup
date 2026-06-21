@extends('layouts.admin')

@section('title', 'Tenants')

@section('content')
    <header class="header">
        <div class="header-left">
            <h1>Tenant Accounts</h1>
            <p>Manage friend accounts with isolated access</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.tenants.create') }}" class="btn btn-purple"><i class="fas fa-user-plus"></i> Add Account</a>
        </div>
    </header>

    <div class="card">
        <div class="profile-list">
            @forelse ($tenants as $tenant)
                <div class="profile-card">
                    <div class="profile-info">
                        <div class="profile-avatar">{{ strtoupper(substr($tenant->name, 0, 1)) }}</div>
                        <div class="profile-details">
                            <h4>{{ $tenant->name }}</h4>
                            <p>{{ $tenant->daily_limit }}/day cap · {{ $tenant->domains_count }} domains · {{ $tenant->api_keys_count }} keys</p>
                            <span class="status-badge {{ $tenant->status === 'active' ? 'sent' : 'failed' }}">
                                {{ ucfirst($tenant->status) }}
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('admin.tenants.show', $tenant) }}" class="btn btn-outline btn-sm"><i class="fas fa-chart-line"></i> Activity</a>
                    <a href="{{ route('admin.tenants.edit', $tenant) }}" class="btn btn-outline btn-sm"><i class="fas fa-cog"></i> Edit</a>
                </div>
            @empty
                <p style="text-align:center;color:var(--text-secondary);padding:32px;">No tenant accounts yet.</p>
            @endforelse
        </div>
    </div>
@endsection
