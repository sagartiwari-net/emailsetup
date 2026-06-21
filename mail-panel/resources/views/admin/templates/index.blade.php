@extends('layouts.admin')

@section('title', 'Templates')

@section('content')
    <header class="header">
        <div class="header-left">
            <h1>Email Templates</h1>
            <p>Manage OTP, welcome and promo email templates</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.templates.create') }}" class="btn btn-purple"><i class="fas fa-plus"></i> New Template</a>
        </div>
    </header>

    <div class="card">
        <p class="form-hint" style="margin-bottom:20px;">
            <i class="fas fa-info-circle"></i> Use variables like <code>@{{otp}}</code>, <code>@{{name}}</code> in subject and body.
        </p>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Slug</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Subject</th>
                    <th>Scope</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($templates as $template)
                    <tr>
                        <td><code>{{ $template->slug }}</code></td>
                        <td><strong>{{ $template->name }}</strong></td>
                        <td><span class="status-badge {{ $template->type === 'promo' ? 'queued' : 'sent' }}">{{ ucfirst($template->type) }}</span></td>
                        <td>{{ Str::limit($template->subject, 40) }}</td>
                        <td>{{ $template->tenant_id ? 'Custom' : 'Global' }}</td>
                        <td>
                            <a href="{{ route('admin.templates.edit', $template) }}" class="action-btn" title="Edit"><i class="fas fa-edit"></i></a>
                            @if ($template->tenant_id || auth()->user()->isSuperAdmin())
                                <form method="POST" action="{{ route('admin.templates.destroy', $template) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="action-btn" type="submit" title="Delete" style="color:#ef4444;" onclick="return confirm('Delete this template?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;padding:32px;color:var(--text-secondary);">No templates yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
