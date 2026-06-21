@extends('layouts.admin')

@section('title', 'Login')

@section('content')
<div class="login-page">
    <div class="login-card">
        <div class="login-logo">
            <div class="logo-icon"><i class="fas fa-envelope"></i></div>
            <span class="logo-text">Mail Panel</span>
        </div>
        <h2>Welcome back</h2>
        <p class="login-subtitle">Sign in to manage your mail system</p>

        @if ($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-input" name="email" value="{{ old('email') }}" placeholder="admin@mail.local" required>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" class="form-input" name="password" placeholder="••••••••" required>
            </div>
            <label class="checkbox-row">
                <input type="checkbox" name="remember"> Remember me
            </label>
            <button type="submit" class="btn btn-purple btn-full">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>
    </div>
</div>
@endsection
