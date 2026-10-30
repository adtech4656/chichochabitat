<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        // An explicit allowlist: `role`, `permissions` and `suspended_at` are
        // deliberately not validated or accepted here, so a crafted payload
        // cannot register an administrator.
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:32'],
            'password' => ['required', 'string', Password::min(8)->letters()->numbers()],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
            'locale' => app()->getLocale(),
        ]);
        $user->forceFill(['role' => 'customer'])->save();
        $user->assignRole(Role::findOrCreate('customer', 'web'));

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('auth_confirmed_at', time());

        return back()->with('success', 'Welcome to ChiChoc-Habitat.');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        // A single generic failure message for both "no such user" and "wrong
        // password": distinguishing them hands an attacker a user-enumeration
        // oracle, which is then used to target the reset flow.
        if (! Auth::attempt($credentials, $remember)) {
            // Hash a dummy value so a failed login takes the same time whether
            // or not the account exists (timing-based enumeration).
            Hash::check('timing-equaliser', '$2y$12$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG');

            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $user = Auth::user();

        if ($user->suspended_at !== null) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'This account is suspended. Contact support.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put('auth_confirmed_at', time());

        return back()->with('success', 'Signed in.');
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:32'],
        ]);

        $user->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Your current password is incorrect.',
            ]);
        }

        // Reject reuse of the same secret: it provides no protection against a
        // credential that has already leaked.
        if (Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'Choose a password you have not used before.',
            ]);
        }

        $user->forceFill(['password' => $data['password']])->save();

        // Invalidate every other session: a password change must evict an
        // attacker who already holds a live session.
        Auth::logoutOtherDevices($data['current_password']);

        $request->session()->put('auth_confirmed_at', time());

        return back()->with('success', 'Password updated. Other devices were signed out.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
