@extends('layouts.admin')

@section('title', 'Subscribers')

@section('content')
    <header class="header">
        <div class="header-left">
            <h1>Subscriber Lists</h1>
            <p>Upload opt-in emails for promotional campaigns</p>
        </div>
    </header>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Create New List</h3>
        </div>
        <form method="POST" action="{{ route('admin.subscribers.store') }}">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">List Name *</label>
                    <input type="text" class="form-input" name="name" placeholder="Website 1 subscribers" required>
                </div>
                <div class="form-group" style="display:flex;align-items:flex-end;">
                    <button class="btn btn-success" type="submit"><i class="fas fa-plus"></i> Create List</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="profile-list">
            @forelse ($lists as $list)
                <div class="profile-card">
                    <div class="profile-info">
                        <div class="profile-avatar"><i class="fas fa-list"></i></div>
                        <div class="profile-details">
                            <h4>{{ $list->name }}</h4>
                            <p>{{ $list->active_subscribers_count }} active · {{ $list->subscribers_count }} total</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.subscribers.show', $list) }}" class="btn btn-outline btn-sm">
                        <i class="fas fa-eye"></i> Manage
                    </a>
                </div>
            @empty
                <p style="text-align:center;color:var(--text-secondary);padding:32px;">No lists yet. Create one to upload emails.</p>
            @endforelse
        </div>
    </div>
@endsection
