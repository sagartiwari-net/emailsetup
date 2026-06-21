@extends('layouts.admin')

@section('title', $template->exists ? 'Edit Template' : 'New Template')

@section('content')
    <header class="header">
        <div class="header-left">
            <h1>{{ $template->exists ? 'Edit Template' : 'New Template' }}</h1>
            <p>{{ $template->exists ? 'Update email template content' : 'Create a new email template' }}</p>
        </div>
    </header>

    <div class="card" style="max-width:800px;">
        <form method="POST" action="{{ $template->exists ? route('admin.templates.update', $template) : route('admin.templates.store') }}">
            @csrf
            @if ($template->exists)
                @method('PUT')
            @endif

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Slug (API identifier) *</label>
                    <input type="text" class="form-input" name="slug" value="{{ old('slug', $template->slug) }}" placeholder="otp" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Display Name *</label>
                    <input type="text" class="form-input" name="name" value="{{ old('name', $template->name) }}" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Type</label>
                <select class="form-input" name="type">
                    <option value="transactional" @selected(old('type', $template->type) === 'transactional')>Transactional</option>
                    <option value="promo" @selected(old('type', $template->type) === 'promo')>Promo</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Subject *</label>
                <input type="text" class="form-input" name="subject" value="{{ old('subject', $template->subject) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">HTML Body *</label>
                <textarea class="form-input" name="html_body" rows="8" required>{{ old('html_body', $template->html_body) }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Plain Text Body (optional)</label>
                <textarea class="form-input" name="text_body" rows="5">{{ old('text_body', $template->text_body) }}</textarea>
            </div>

            <div style="display:flex;gap:12px;">
                <button class="btn btn-success" type="submit"><i class="fas fa-save"></i> Save Template</button>
                <a href="{{ route('admin.templates.index') }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
@endsection
