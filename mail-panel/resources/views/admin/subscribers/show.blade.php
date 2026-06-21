@extends('layouts.admin')

@section('title', $list->name)

@section('content')
    <header class="header">
        <div class="header-left">
            <h1>{{ $list->name }}</h1>
            <p>{{ $list->subscribers_count }} subscribers in this list</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.subscribers.index') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </header>

    <div class="page-grid-1-1">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Upload CSV</h3></div>
            <form method="POST" action="{{ route('admin.subscribers.import', $list) }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label class="form-label">CSV File</label>
                    <input type="file" class="form-input" name="csv_file" accept=".csv,.txt" required>
                    <p class="form-hint">Format: email,name (first row can be header)</p>
                </div>
                <button class="btn btn-purple btn-full" type="submit"><i class="fas fa-upload"></i> Upload CSV</button>
            </form>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">Add Manually</h3></div>
            <form method="POST" action="{{ route('admin.subscribers.add', $list) }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" class="form-input" name="email" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" class="form-input" name="name">
                </div>
                <button class="btn btn-success btn-full" type="submit"><i class="fas fa-user-plus"></i> Add Subscriber</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Subscribers</h3></div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Added</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($subscribers as $subscriber)
                    <tr>
                        <td><strong>{{ $subscriber->email }}</strong></td>
                        <td>{{ $subscriber->name ?? '—' }}</td>
                        <td><span class="status-badge {{ $subscriber->status === 'active' ? 'sent' : ($subscriber->status === 'unsubscribed' ? 'pending' : 'failed') }}">{{ ucfirst($subscriber->status) }}</span></td>
                        <td>{{ $subscriber->created_at->format('d M Y') }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.subscribers.remove', [$list, $subscriber]) }}">
                                @csrf
                                @method('DELETE')
                                <button class="action-btn" type="submit" style="color:#ef4444;" onclick="return confirm('Remove subscriber?')"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;padding:32px;color:var(--text-secondary);">No subscribers yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination-wrap">{{ $subscribers->links() }}</div>
    </div>
@endsection
