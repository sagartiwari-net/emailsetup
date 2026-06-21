@extends('layouts.admin')

@section('title', 'Websites')

@section('content')
    <header class="header">
        <div class="header-left">
            <h1>Website Integrations</h1>
            <p>Track OTP connections for your websites</p>
        </div>
    </header>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Register Website</h3></div>
        <form method="POST" action="{{ route('admin.websites.store') }}">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Website Name *</label>
                    <input type="text" class="form-input" name="name" placeholder="My Shop" required>
                </div>
                <div class="form-group">
                    <label class="form-label">URL</label>
                    <input type="url" class="form-input" name="url" placeholder="https://myshop.com">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Domain</label>
                    <select class="form-input" name="domain_id">
                        <option value="">— Select —</option>
                        @foreach ($domains as $domain)
                            <option value="{{ $domain->id }}">{{ $domain->domain_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">API Key</label>
                    <select class="form-input" name="api_key_id">
                        <option value="">— Select —</option>
                        @foreach ($apiKeys as $key)
                            <option value="{{ $key->id }}">{{ $key->name }} ({{ $key->domain->domain_name }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select class="form-input" name="integration_status">
                        <option value="pending">Pending</option>
                        <option value="testing">Testing</option>
                        <option value="connected">Connected</option>
                    </select>
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
            <div class="form-group">
                <label class="form-label">Notes</label>
                <input type="text" class="form-input" name="notes" placeholder="Pilot site, OTP only">
            </div>
            <button class="btn btn-success" type="submit"><i class="fas fa-plus"></i> Add Website</button>
        </form>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Registered Websites</h3></div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Domain</th>
                    <th>Status</th>
                    <th>Last Send</th>
                    @if (auth()->user()->isSuperAdmin())<th>Tenant</th>@endif
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($websites as $website)
                    <tr>
                        <td>
                            <strong>{{ $website->name }}</strong>
                            @if ($website->url)
                                <br><a href="{{ $website->url }}" target="_blank" style="font-size:12px;color:var(--accent-purple);">{{ $website->url }}</a>
                            @endif
                        </td>
                        <td>{{ $website->domain?->domain_name ?? '—' }}</td>
                        <td>
                            <span class="status-badge {{ $website->integration_status === 'connected' ? 'sent' : ($website->integration_status === 'testing' ? 'pending' : 'queued') }}">
                                {{ ucfirst($website->integration_status) }}
                            </span>
                        </td>
                        <td>{{ $website->last_send_at?->format('d M H:i') ?? 'Never' }}</td>
                        @if (auth()->user()->isSuperAdmin())
                            <td>{{ $website->tenant->name }}</td>
                        @endif
                        <td>
                            <details>
                                <summary class="btn btn-outline btn-sm" style="cursor:pointer;">Edit</summary>
                                <form method="POST" action="{{ route('admin.websites.update', $website) }}" style="margin-top:12px;padding:12px;background:var(--bg-secondary);border-radius:8px;">
                                    @csrf
                                    @method('PUT')
                                    <div class="form-group">
                                        <input type="text" class="form-input" name="name" value="{{ $website->name }}" required>
                                    </div>
                                    <div class="form-group">
                                        <select class="form-input" name="integration_status">
                                            @foreach (['pending', 'testing', 'connected'] as $status)
                                                <option value="{{ $status }}" @selected($website->integration_status === $status)>{{ ucfirst($status) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <input type="hidden" name="url" value="{{ $website->url }}">
                                    <input type="hidden" name="domain_id" value="{{ $website->domain_id }}">
                                    <input type="hidden" name="api_key_id" value="{{ $website->api_key_id }}">
                                    <button class="btn btn-purple btn-sm" type="submit">Save</button>
                                </form>
                                <form method="POST" action="{{ route('admin.websites.destroy', $website) }}" style="margin-top:8px;" onsubmit="return confirm('Remove website?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                                </form>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;padding:32px;color:var(--text-secondary);">No websites registered yet. Add your first site above.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
