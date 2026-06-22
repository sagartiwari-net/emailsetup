@extends('layouts.admin')

@section('title', 'Change Password')

@section('content')
    <header class="header">
        <div class="header-left">
            <h1>Change Password</h1>
            <p>Update your login password for {{ $user->email }}</p>
        </div>
    </header>

    <div class="card" style="max-width:640px;">
        <form method="POST" action="{{ route('admin.account.password.update') }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">Current Password</label>
                <input type="password" class="form-input" name="current_password" required autocomplete="current-password">
            </div>

            <div class="form-group">
                <label class="form-label">New Password</label>
                <input type="password" class="form-input" name="password" required autocomplete="new-password" minlength="8">
            </div>

            <div class="form-group">
                <label class="form-label">Confirm New Password</label>
                <input type="password" class="form-input" name="password_confirmation" required autocomplete="new-password" minlength="8">
            </div>

            <button class="btn btn-purple btn-full" type="submit">
                <i class="fas fa-lock"></i> Update Password
            </button>
        </form>
    </div>
@endsection
