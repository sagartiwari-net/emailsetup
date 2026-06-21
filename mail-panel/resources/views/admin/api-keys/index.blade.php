@extends('layouts.admin')

@section('title', 'API Keys')

@section('content')
    <header class="header">
        <div class="header-left">
            <h1>Manage Keys</h1>
            <p>Generate and manage API keys for your websites</p>
        </div>
    </header>

    @if ($domains->isEmpty())
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> Pehle <a href="{{ route('admin.domains.index') }}">domain add karo</a>, phir API key generate kar sakte ho.
        </div>
    @endif

    <div class="page-grid-1-1">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Generate New API Key</h3>
            </div>
            <form method="POST" action="{{ route('admin.api-keys.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Key Name *</label>
                    <input type="text" class="form-input" name="label" placeholder="e.g. Website 1 OTP" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Domain *</label>
                    <select class="form-input" name="domain_id" required @disabled($domains->isEmpty())>
                        @forelse ($domains as $domain)
                            <option value="{{ $domain->id }}">{{ $domain->domain_name }}</option>
                        @empty
                            <option value="">No domain — add one first</option>
                        @endforelse
                    </select>
                    <p class="form-hint"><i class="fas fa-info-circle"></i> Mail will be sent from this domain</p>
                </div>
                <button class="btn btn-success btn-full" type="submit" @disabled($domains->isEmpty())>
                    <i class="fas fa-key"></i> Generate API Key
                </button>
            </form>
        </div>

        <div>
            @if (session('new_api_key'))
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Generated Key</h3>
                    </div>
                    <div class="key-display">
                        <i class="fas fa-key" style="font-size:32px;color:var(--primary-purple);margin-bottom:12px;"></i>
                        <p style="color:var(--text-secondary);font-size:14px;margin-bottom:8px;">Save it now — it won't be shown again</p>
                        <div class="key-value" id="newApiKey">{{ session('new_api_key') }}</div>
                        <button type="button" class="btn btn-purple" onclick="navigator.clipboard.writeText(document.getElementById('newApiKey').textContent)">
                            <i class="fas fa-copy"></i> Copy Key
                        </button>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Generated Key</h3>
                    </div>
                    <div class="key-display">
                        <i class="fas fa-lock" style="font-size:40px;color:var(--text-secondary);opacity:0.3;margin-bottom:12px;"></i>
                        <p style="color:var(--text-secondary);">Generate a new key to see it here</p>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Active Keys</h3>
                </div>
                <div class="profile-list">
                    @forelse ($apiKeys as $apiKey)
                        <div class="profile-card">
                            <div class="profile-info">
                                <div class="profile-avatar"><i class="fas fa-server"></i></div>
                                <div class="profile-details">
                                    <h4>{{ $apiKey->label }}</h4>
                                    <p>{{ $apiKey->domain->domain_name }}</p>
                                    <small style="color:var(--text-secondary);font-size:11px;">{{ $apiKey->key_prefix }}... · {{ $apiKey->created_at->format('d M Y') }}</small>
                                </div>
                            </div>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <span class="status-badge {{ $apiKey->is_active ? 'sent' : 'failed' }}">
                                    {{ $apiKey->is_active ? 'Active' : 'Revoked' }}
                                </span>
                                @if ($apiKey->is_active)
                                    <form method="POST" action="{{ route('admin.api-keys.destroy', $apiKey) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="action-btn" type="submit" title="Revoke" style="color:#ef4444;" onclick="return confirm('Revoke this key?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p style="color:var(--text-secondary);text-align:center;padding:20px;">No API keys yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
