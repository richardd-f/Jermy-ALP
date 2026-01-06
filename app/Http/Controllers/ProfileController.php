<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\User; // Make sure to import the User model

class ProfileController extends Controller
{
    /**
     * Show the logged-in user's profile page.
     */
    public function edit()
    {
        /** @var User $user */
        $user = Auth::user(); // Type hint for Intelephense

        return view('profile', [
            'title' => 'My Profile',
            'user'  => $user,
        ]);
    }

    /**
     * Update the logged-in user's profile data (name, email, etc.).
     */
    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user(); // Type hint for Intelephense

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        // Update and save
        $user->name  = $validated['name'];
        $user->email = $validated['email'];
        $user->save(); // Eloquent save

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Update the logged-in user's password.
     */
    public function updatePassword(Request $request)
    {
        /** @var User $user */
        $user = Auth::user(); // Type hint for Intelephense

        $validated = $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'confirmed', Password::defaults()],
        ]);

        // Check current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Update password and save
        $user->password = Hash::make($validated['password']);
        $user->save(); // Eloquent save

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Password updated successfully.');
    }

    /**
     * Delete the logged-in user's account.
     */
    public function destroy(Request $request)
    {
        /** @var User $user */
        $user = Auth::user(); // Type hint for Intelephense

        // Delete user
        $user->delete(); // Eloquent delete

        // Logout and invalidate session
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')
            ->with('success', 'Your account has been deleted.');
    }
}
