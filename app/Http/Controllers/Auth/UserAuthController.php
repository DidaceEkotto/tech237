<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Inertia\Inertia;

class UserAuthController extends Controller
{
    public function showRegister(Request $request)
    {
        if ($request->filled('redirect')) {
            session()->put('url.intended', $request->input('redirect'));
        } elseif (!session()->has('url.intended')) {
            $prev = url()->previous();
            if ($prev && !str_contains($prev, '/login') && !str_contains($prev, '/register')) {
                session()->put('url.intended', $prev);
            }
        }
        return Inertia::render('Auth/Register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'first_name' => 'required|string|max:100',
            'gender' => 'required|string|in:Homme,Femme,Autre',
            'country_code' => 'required|string|max:10',
            'phone' => 'required|string|max:30',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                // At least 1 uppercase, 1 digit, 1 special char among _ - @ # { }, no whitespace
                'regex:/^[^\s]+$/', // No spaces
                'regex:/[A-Z]/',    // At least 1 uppercase
                'regex:/[0-9]/',    // At least 1 digit
                'regex:/[_@#\-\{\}]/', // At least 1 allowed special char
            ],
            'birth_date' => 'nullable|date',
            'marital_status' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:100',
        ], [
            'password.regex' => "Le mot de passe doit contenir au moins une majuscule, un chiffre, un caractère spécial autorisé (_ - @ # { }) et aucun espace.",
        ]);

        $user = User::create([
            'name' => $request->name,
            'first_name' => $request->first_name,
            'gender' => $request->gender,
            'country_code' => $request->country_code,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'birth_date' => $request->birth_date ?: null,
            'marital_status' => $request->marital_status ?: 'Célibataire',
            'city' => $request->city ?: 'Douala',
            'location' => $request->city ?: 'Douala',
            'status' => 'active',
            'registered_at' => now(),
        ]);

        // Auto-login upon registration
        Auth::guard('web')->login($user);

        $intended = session()->pull('url.intended', route('user.dashboard'));
        return redirect()->to($intended)->with('success', 'Bienvenue sur votre espace Tech237 !');
    }

    public function showLogin(Request $request)
    {
        if ($request->filled('redirect')) {
            session()->put('url.intended', $request->input('redirect'));
        } elseif (!session()->has('url.intended')) {
            $prev = url()->previous();
            if ($prev && !str_contains($prev, '/login') && !str_contains($prev, '/register')) {
                session()->put('url.intended', $prev);
            }
        }
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $intended = session()->pull('url.intended', route('user.dashboard'));
            return redirect()->to($intended);
        }

        return back()->withErrors([
            'email' => 'Les identifiants fournis ne correspondent à aucun compte utilisateur.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
