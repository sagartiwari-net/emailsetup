@extends('layouts.admin')

@section('title', $tenant->exists ? 'Edit Tenant' : 'New Tenant')

@section('content')
    <header class="header">
        <div class="header-left">
            <h1>{{ $tenant->exists ? 'Edit Tenant' : 'Create Friend Account' }}</h1>
            <p>Set daily limits and account access</p>
        </div>
    </header>

    <div class="card" style="max-width:720px;">
        <form method="POST" action="{{ $tenant->exists ? route('admin.tenants.update', $tenant) : route('admin.tenants.store') }}">
            @csrf
            @if ($tenant->exists)
                @method('PUT')
            @endif

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Account Name *</label>
                    <input type="text" class="form-input" name="name" value="{{ old('name', $tenant->name) }}" placeholder="Rahul Website" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Daily Send Cap *</label>
                    <input type="number" class="form-input" name="daily_limit" value="{{ old('daily_limit', $tenant->daily_limit) }}" min="1" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Status</label>
                <select class="form-input" name="status">
                    <option value="active" @selected(old('status', $tenant->status) === 'active')>Active</option>
                    <option value="suspended" @selected(old('status', $tenant->status) === 'suspended')>Suspended</option>
                </select>
            </div>

            @if (! $tenant->exists)
                <div style="border-top:1px solid var(--border-color);margin:24px 0;padding-top:24px;">
                    <h3 style="font-size:16px;margin-bottom:16px;"><i class="fas fa-user-lock" style="color:var(--primary-purple);margin-right:8px;"></i>Login Credentials</h3>
                    <div class="form-group">
                        <label class="form-label">Owner Name *</label>
                        <input type="text" class="form-input" name="owner_name" value="{{ old('owner_name') }}" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Owner Email *</label>
                            <input type="email" class="form-input" name="owner_email" value="{{ old('owner_email') }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Password *</label>
                            <input type="password" class="form-input" name="owner_password" required>
                        </div>
                    </div>
                </div>
            @endif

            <div style="display:flex;gap:12px;">
                <button class="btn btn-success" type="submit"><i class="fas fa-save"></i> Save</button>
                <a href="{{ route('admin.tenants.index') }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
@endsection
