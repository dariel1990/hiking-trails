<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'current_password' => ['nullable', 'current_password'],
            'password' => ['nullable', 'min:8', 'confirmed'],
            'public_profile' => ['boolean'],
            'share_completions' => ['boolean'],
        ]);

        // Update basic info
        $user->name = $request->name;
        
        // Handle email change
        if ($user->email !== $request->email) {
            $user->email = $request->email;
            $user->email_verified_at = null; // Require re-verification
        }
        
        // Handle password change
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        
        // Update privacy settings
        $user->public_profile = $request->boolean('public_profile');
        $user->share_completions = $request->boolean('share_completions');
        
        $user->save();

        $message = 'Profile updated successfully!';
        if ($user->wasChanged('email')) {
            $message .= ' Please verify your new email address.';
        }

        return redirect()->route('profile')->with('success', $message);
    }
}