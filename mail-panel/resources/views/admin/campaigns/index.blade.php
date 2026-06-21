@extends('layouts.admin')

@section('title', 'Campaigns')

@section('content')
    <header class="header">
        <div class="header-left">
            <h1>Campaigns</h1>
            <p>Schedule promotional emails to your opt-in lists</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.campaigns.create') }}" class="btn btn-purple"><i class="fas fa-plus"></i> New Campaign</a>
        </div>
    </header>

    <div class="card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>List</th>
                    <th>Schedule</th>
                    <th>Status</th>
                    <th>Sent</th>
                    <th>Failed</th>
                    <th>Skipped</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($campaigns as $campaign)
                    <tr>
                        <td><strong>{{ $campaign->name }}</strong></td>
                        <td>{{ $campaign->subscriberList->name }}</td>
                        <td>{{ ucfirst($campaign->schedule_type) }}</td>
                        <td><span class="status-badge {{ in_array($campaign->status, ['running','completed']) ? 'sent' : ($campaign->status === 'paused' ? 'pending' : ($campaign->status === 'cancelled' ? 'failed' : 'queued')) }}">{{ ucfirst($campaign->status) }}</span></td>
                        <td>{{ $campaign->sent_count }}</td>
                        <td>{{ $campaign->failed_count }}</td>
                        <td>{{ $campaign->skipped_count }}</td>
                        <td><a href="{{ route('admin.campaigns.show', $campaign) }}" class="btn btn-outline btn-sm">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" style="text-align:center;padding:32px;color:var(--text-secondary);">No campaigns yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
