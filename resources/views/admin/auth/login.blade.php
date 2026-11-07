<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login — Culina Academy</title>
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
<div class="admin-login-page">
    <div class="admin-login-card">
        <h1>Culina Academy CMS</h1>
        <p>Sign in with your WordPress administrator account.</p>

        @if(session('error'))
            <div class="admin-alert admin-alert-error">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf
            <div class="admin-field">
                <label for="username">Username or email</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus>
            </div>
            <div class="admin-field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="admin-field">
                <label><input type="checkbox" name="remember" value="1"> Remember me</label>
            </div>
            <button type="submit" class="admin-btn admin-btn-primary" style="width:100%">Sign in</button>
        </form>
    </div>
</div>
</body>
</html>