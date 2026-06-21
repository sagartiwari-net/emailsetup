@extends('layouts.admin')

@section('title', 'Domains')

@section('content')
    <header class="header">
        <div class="header-left">
            <h1>Domains</h1>
            <p>Manage sending domains for your websites</p>
        </div>
    </header>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add New Domain</h3>
        </div>
        <form method="POST" action="{{ route('admin.domains.store') }}">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Domain Name *</label>
                    <input type="text" class="form-input" name="domain_name" placeholder="website1.com" required>
                </div>
                <div class="form-group">
                    <label class="form-label">From Email</label>
                    <input type="email" class="form-input" name="from_email" placeholder="noreply@website1.com">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">From Name</label>
                    <input type="text" class="form-input" name="from_name" placeholder="Website 1">
                </div>
                @if ($tenants->isNotEmpty())
                    <div class="form-group">
                        <label class="form-label">Tenant</label>
                        <select class="form-input" name="tenant_id">
                            @foreach ($tenants as $tenant)
                                <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
            <button class="btn btn-success" type="submit"><i class="fas fa-plus"></i> Add Domain</button>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Your Domains</h3>
        </div>
        <div class="profile-list">
            @forelse ($domains as $domain)
                <div class="profile-card">
                    <div class="profile-info">
                        <div class="profile-avatar"><i class="fas fa-globe"></i></div>
                        <div class="profile-details">
                            <h4>{{ $domain->domain_name }}</h4>
                            <p>{{ $domain->defaultFromEmail() }}</p>
                            <span class="status-badge {{ $domain->dkim_verified ? 'sent' : 'queued' }}">
                                DKIM {{ $domain->dkim_verified ? 'Verified' : 'Pending' }}
                            </span>
                        </div>
                    </div>
                    <div style="font-size:13px;color:var(--text-secondary);margin-bottom:8px;">
                        {{ $domain->tenant->name ?? '—' }}
                    </div>
                    @if (auth()->user()->isSuperAdmin())
                        <details>
                            <summary class="btn btn-outline btn-sm" style="cursor:pointer;">Assign</summary>
                            <form method="POST" action="{{ route('admin.domains.update', $domain) }}" style="margin-top:8px;">
                                @csrf
                                @method('PUT')
                                <select class="form-input" name="tenant_id" style="margin-bottom:8px;">
                                    @foreach ($tenants as $tenant)
                                        <option value="{{ $tenant->id }}" @selected($domain->tenant_id === $tenant->id)>{{ $tenant->name }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-purple btn-sm" type="submit">Save</button>
                            </form>
                        </details>
                    @endif
                </div>
            @empty
                <p style="color:var(--text-secondary);text-align:center;padding:20px;">No domains added yet.</p>
            @endforelse
        </div>
    </div>
@endsection
