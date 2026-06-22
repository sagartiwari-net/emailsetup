<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Mail Panel')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/cosmic-theme.css') }}">
    @stack('styles')
</head>
<body>
@auth
<div class="app-container">
    <aside class="sidebar">
        <div class="logo">
            <div class="logo-icon"><i class="fas fa-envelope"></i></div>
            <span class="logo-text">Mail Panel</span>
        </div>

        <div class="user-profile">
            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div class="user-info">
                <h3>{{ auth()->user()->name }}</h3>
                <p>{{ auth()->user()->isSuperAdmin() ? 'Super Admin' : 'Mail Account' }}</p>
            </div>
        </div>

        <ul class="nav-menu">
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i><span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.logs.index') }}" class="nav-link {{ request()->routeIs('admin.logs.*') ? 'active' : '' }}">
                    <i class="fas fa-paper-plane"></i><span>Mail Logs</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.domains.index') }}" class="nav-link {{ request()->routeIs('admin.domains.*') ? 'active' : '' }}">
                    <i class="fas fa-globe"></i><span>Domains</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.api-keys.index') }}" class="nav-link {{ request()->routeIs('admin.api-keys.*') ? 'active' : '' }}">
                    <i class="fas fa-key"></i><span>API Keys</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.templates.index') }}" class="nav-link {{ request()->routeIs('admin.templates.*') ? 'active' : '' }}">
                    <i class="fas fa-file-code"></i><span>Templates</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.subscribers.index') }}" class="nav-link {{ request()->routeIs('admin.subscribers.*') ? 'active' : '' }}">
                    <i class="fas fa-address-book"></i><span>Subscribers</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.campaigns.index') }}" class="nav-link {{ request()->routeIs('admin.campaigns.*') ? 'active' : '' }}">
                    <i class="fas fa-bullhorn"></i><span>Campaigns</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.websites.index') }}" class="nav-link {{ request()->routeIs('admin.websites.*') ? 'active' : '' }}">
                    <i class="fas fa-link"></i><span>Websites</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.test-mail.create') }}" class="nav-link {{ request()->routeIs('admin.test-mail.*') ? 'active' : '' }}">
                    <i class="fas fa-flask"></i><span>Test Mail</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.account.password.edit') }}" class="nav-link {{ request()->routeIs('admin.account.password.*') ? 'active' : '' }}">
                    <i class="fas fa-lock"></i><span>Change Password</span>
                </a>
            </li>
            @if (auth()->user()->isSuperAdmin())
                <li class="nav-divider"></li>
                <li class="nav-item">
                    <a href="{{ route('admin.failed-jobs.index') }}" class="nav-link {{ request()->routeIs('admin.failed-jobs.*') ? 'active' : '' }}">
                        <i class="fas fa-exclamation-triangle"></i><span>Failed Jobs</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.tenants.index') }}" class="nav-link {{ request()->routeIs('admin.tenants.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i><span>Tenants</span>
                    </a>
                </li>
            @endif
        </ul>

        <div class="nav-divider"></div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nav-link logout" style="width:100%;border:none;background:none;cursor:pointer;">
                <i class="fas fa-sign-out-alt"></i><span>Sign out</span>
            </button>
        </form>
    </aside>

    <main class="main-content">
        @if (session('success'))
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                @foreach ($errors->all() as $error)
                    <div><i class="fas fa-exclamation-circle"></i> {{ $error }}</div>
                @endforeach
            </div>
        @endif

        @yield('content')
    </main>
</div>
@else
    @yield('content')
@endauth
@stack('scripts')
</body>
</html>
