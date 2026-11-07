<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Culina Academy CMS</title>
    <link rel="stylesheet" href="/css/admin.css?v=20260708">
    @stack('styles')
</head>
<body class="admin-body">
<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-brand">
            <h1>Culina Academy</h1>
            <span>Content Management</span>
        </div>
        <nav class="admin-nav">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>

            <div class="nav-section">Content</div>
            <a href="{{ route('admin.pages.index') }}" class="{{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">Pages</a>
            <a href="{{ route('admin.courses.index') }}" class="{{ request()->routeIs('admin.courses.*') ? 'active' : '' }}">Courses</a>
            <a href="{{ route('admin.blog.index') }}" class="{{ request()->routeIs('admin.blog.*') ? 'active' : '' }}">Blog</a>
            <a href="{{ route('admin.media.index') }}" class="{{ request()->routeIs('admin.media.*') ? 'active' : '' }}">Media</a>

            <div class="nav-section">Site</div>
            <a href="{{ route('admin.menus.index') }}" class="{{ request()->routeIs('admin.menus.*') ? 'active' : '' }}">Menus</a>
            <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">Settings</a>
            <a href="{{ route('admin.languages.index') }}" class="{{ request()->routeIs('admin.languages.*') ? 'active' : '' }}">Languages</a>

            <div class="nav-section">Bookings</div>
            <a href="{{ route('admin.submissions.index', ['tab' => 'bookings']) }}" class="{{ request()->get('tab') === 'bookings' ? 'active' : '' }}">Bookings</a>
            <a href="{{ route('admin.submissions.index', ['tab' => 'waitinglists']) }}" class="{{ request()->get('tab') === 'waitinglists' ? 'active' : '' }}">Waiting Lists</a>
            <a href="{{ route('admin.intakes.index') }}" class="{{ request()->routeIs('admin.intakes.*') ? 'active' : '' }}">Course Intakes</a>

            <div class="nav-section">Inbox</div>
            <a href="{{ route('admin.submissions.index') }}" class="{{ request()->routeIs('admin.submissions.*') && !in_array(request()->get('tab'), ['bookings', 'waitinglists']) ? 'active' : '' }}">Form Submissions</a>

            <div class="nav-section">Public Site</div>
            <a href="/" target="_blank" rel="noopener">View Website ↗</a>
        </nav>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <h2>@yield('heading', 'Dashboard')</h2>
            <div class="admin-actions">
                <span class="admin-user">{{ auth('admin')->user()->display_name ?? auth('admin')->user()->user_login }}</span>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="admin-btn admin-btn-secondary admin-btn-sm">Sign out</button>
                </form>
            </div>
        </header>

        <main class="admin-content">
            @if(session('success'))
                <div class="admin-alert admin-alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="admin-alert admin-alert-error">{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>