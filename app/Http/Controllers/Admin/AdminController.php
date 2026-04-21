<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trail;
use App\Models\TrailMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Show admin login form
     */
    public function loginForm()
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    /**
     * Handle admin login
     */
    public function login(Request $request)
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();

            if (! $user->isAdmin()) {
                Auth::logout();

                return back()->withErrors(['email' => 'Access denied. Admin privileges required.']);
            }

            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    /**
     * Handle admin logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    /**
     * Show admin dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_trails' => Trail::count(),
            'featured_trails' => Trail::featured()->count(),
            'active_trails' => Trail::active()->count(),
            'inactive_trails' => Trail::where('status', '!=', 'active')->count(),
            'total_photos' => TrailMedia::where('media_type', 'photo')->count(),
            'recent_trails' => Trail::latest()->take(5)->get(),

            'gpx_trails' => Trail::where('data_source', 'gpx')->count(),
            'manual_trails' => Trail::where('data_source', 'manual')->count(),
            'mixed_trails' => Trail::where('data_source', 'mixed')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
