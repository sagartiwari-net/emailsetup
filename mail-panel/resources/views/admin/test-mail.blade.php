@extends('layouts.admin')

@section('title', 'Test Mail')

@section('content')
    <header class="header">
        <div class="header-left">
            <h1>Send Test Mail</h1>
            <p>Test templates and delivery before connecting websites</p>
        </div>
    </header>

    <div class="card" style="max-width:640px;">
        @if ($apiKeys->isEmpty())
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> Pehle ek domain aur API key create karo.
            </div>
            <a href="{{ route('admin.domains.index') }}" class="btn btn-purple"><i class="fas fa-globe"></i> Add Domain</a>
        @else
            <form method="POST" action="{{ route('admin.test-mail.send') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">API Key</label>
                    <select class="form-input" name="api_key_id" required>
                        @foreach ($apiKeys as $key)
                            <option value="{{ $key->id }}">{{ $key->label }} ({{ $key->domain->domain_name }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">To Email</label>
                    <input type="email" class="form-input" name="to_email" placeholder="you@gmail.com" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Template</label>
                    <select class="form-input" name="template" required>
                        @foreach ($templates as $tpl)
                            <option value="{{ $tpl }}">{{ $tpl }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-purple btn-full" type="submit">
                    <i class="fas fa-paper-plane"></i> Send Test Mail
                </button>
            </form>
        @endif
    </div>
@endsection
