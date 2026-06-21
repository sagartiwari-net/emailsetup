@extends('layouts.admin')

@section('title', 'New Campaign')

@section('content')
    <header class="header">
        <div class="header-left">
            <h1>Create Campaign</h1>
            <p>Send promo mail respecting your daily warmup cap</p>
        </div>
    </header>

    <div class="card" style="max-width:800px;">
        @if ($lists->isEmpty() || $apiKeys->isEmpty())
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                @if ($lists->isEmpty())
                    Pehle <a href="{{ route('admin.subscribers.index') }}">subscriber list</a> banao aur emails add karo.
                @endif
                @if ($apiKeys->isEmpty())
                    Pehle <a href="{{ route('admin.domains.index') }}">domain</a> aur <a href="{{ route('admin.api-keys.index') }}">API key</a> create karo.
                @endif
            </div>
        @else
        <form method="POST" action="{{ route('admin.campaigns.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Campaign Name *</label>
                <input type="text" class="form-input" name="name" placeholder="Summer Offer 2026" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Subscriber List *</label>
                    <select class="form-input" name="subscriber_list_id" required>
                        @foreach ($lists as $list)
                            <option value="{{ $list->id }}">{{ $list->name }} ({{ $list->active_subscribers_count }} active)</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">API Key / Domain *</label>
                    <select class="form-input" name="api_key_id" required>
                        @foreach ($apiKeys as $key)
                            <option value="{{ $key->id }}">{{ $key->label }} — {{ $key->domain->domain_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Template *</label>
                    <select class="form-input" name="template_slug" required>
                        @foreach ($templates as $template)
                            <option value="{{ $template->slug }}">{{ $template->name }} ({{ $template->slug }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Subject (optional override)</label>
                    <input type="text" class="form-input" name="subject" placeholder="Special offer for you">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Schedule Type *</label>
                <select class="form-input" name="schedule_type" id="scheduleType" required>
                    <option value="now">Send Now (batched, respects daily cap)</option>
                    <option value="once">One-time — future date</option>
                    <option value="recurring">Recurring — every X days</option>
                </select>
            </div>

            <div id="onceFields" style="display:none;">
                <div class="form-group">
                    <label class="form-label">Send At</label>
                    <input type="datetime-local" class="form-input" name="send_at">
                </div>
            </div>

            <div id="recurringFields" style="display:none;">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Every (days)</label>
                        <input type="number" class="form-input" name="interval_days" value="3" min="1" max="90">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Send Time</label>
                        <input type="time" class="form-input" name="send_time" value="10:00">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-input" name="start_date">
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Date (optional)</label>
                        <input type="date" class="form-input" name="end_date">
                    </div>
                </div>
            </div>

            <p class="form-hint" style="margin-bottom:16px;">
                <i class="fas fa-info-circle"></i> Promo template must include <code>@{{unsubscribe_url}}</code>. Max 50 emails per batch, 5 min gap. Daily cap enforced automatically.
            </p>

            <button class="btn btn-purple" type="submit"><i class="fas fa-rocket"></i> Launch Campaign</button>
        </form>
        @endif
    </div>

    @push('scripts')
    <script>
        const type = document.getElementById('scheduleType');
        const once = document.getElementById('onceFields');
        const recurring = document.getElementById('recurringFields');
        function toggleSchedule() {
            once.style.display = type.value === 'once' ? 'block' : 'none';
            recurring.style.display = type.value === 'recurring' ? 'block' : 'none';
        }
        type.addEventListener('change', toggleSchedule);
        toggleSchedule();
    </script>
    @endpush
@endsection
