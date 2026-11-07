<?php

namespace App\Http\Controllers\Admin;

use App\Auth\WordPressPasswordHasher;
use App\Http\Controllers\Controller;
use App\Models\WpUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = WpUser::query()
            ->where('user_login', $credentials['username'])
            ->orWhere('user_email', $credentials['username'])
            ->first();

        if (
            !$user
            || !WordPressPasswordHasher::check($credentials['password'], $user->user_pass)
            || !$user->isAdministrator()
        ) {
            return back()
                ->withInput($request->only('username'))
                ->with('error', 'Invalid credentials or insufficient permissions.');
        }

        Auth::guard('admin')->login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}