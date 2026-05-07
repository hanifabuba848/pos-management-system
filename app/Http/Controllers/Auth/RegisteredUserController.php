<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:Admin,Cashier'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Dynamically create the role if it doesn't exist, and assign it
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => $request->role]);
        $user->assignRole($role);

        event(new Registered($user));

        Auth::login($user);

        // Redirect based on role
        if ($user->hasRole('Admin')) {
            return redirect(route('admin.dashboard', absolute: false));
        } elseif ($user->hasRole('Cashier')) {
            return redirect(route('cashier.pos', absolute: false));
        }

        return redirect(route('dashboard', absolute: false));
    }
}
