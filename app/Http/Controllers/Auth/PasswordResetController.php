<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\User;
use App\Models\CustomPasswordReset;

class PasswordResetController extends Controller
{
    public function showLinkRequestForm()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'Aucun compte utilisateur ne correspond à cette adresse email.',
        ]);

        $email = $request->input('email');

        // Check 2-minute cooldown timer
        $recentReset = CustomPasswordReset::where('email', $email)
            ->where('created_at', '>=', now()->subMinutes(2))
            ->first();

        if ($recentReset) {
            $secondsLeft = 120 - now()->diffInSeconds($recentReset->created_at);
            return back()->with('error', "Veuillez patienter encore {$secondsLeft} secondes avant de demander un nouvel envoi.");
        }

        $token = Str::random(64);

        // Delete any expired or previous tokens
        CustomPasswordReset::where('email', $email)->delete();

        // Valid for 10 minutes
        CustomPasswordReset::create([
            'email' => $email,
            'token' => $token,
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
        ]);

        $resetUrl = route('password.reset', ['token' => $token]);

        try {
            Mail::send('emails.password_reset', [
                'resetUrl' => $resetUrl,
                'email' => $email,
            ], function ($message) use ($email) {
                $message->to($email)
                    ->subject('Tech237 - Réinitialisation de votre mot de passe');
            });
        } catch (\Throwable $e) {
            Log::error('Erreur envoi email réinitialisation mot de passe : ' . $e->getMessage());
        }

        return back()->with([
            'success' => 'Un email contenant le lien sécurisé de réinitialisation vous a été transmis. Veuillez vérifier votre boîte de réception.',
            'demo_reset_url' => $resetUrl,
        ]);
    }

    public function showResetForm(string $token)
    {
        $resetRecord = CustomPasswordReset::where('token', $token)->first();

        if (!$resetRecord || $resetRecord->isExpired()) {
            return redirect()->route('password.request')->with('error', 'Le lien de réinitialisation a expiré (validité 10 minutes) ou est invalide. Veuillez renouveler votre demande.');
        }

        return Inertia::render('Auth/ResetPassword', [
            'token' => $token,
            'email' => $resetRecord->email,
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email|exists:users,email',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Z]/',            // At least 1 uppercase
                'regex:/[0-9]/',            // At least 1 number
                'regex:/[_@#\-{}]/',        // At least 1 special char (_ - @ # { })
                'not_regex:/\s/',           // No spaces allowed
            ],
        ], [
            'password.min' => 'Le mot de passe doit comporter au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.regex' => 'Le mot de passe doit contenir au moins une majuscule, un chiffre et un caractère spécial (_ - @ # { }).',
            'password.not_regex' => 'Le mot de passe ne doit contenir aucun espace vide.',
        ]);

        $resetRecord = CustomPasswordReset::where('token', $request->token)
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord || $resetRecord->isExpired()) {
            return redirect()->route('password.request')->with('error', 'Ce lien a expiré. Veuillez refaire une demande.');
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete used token
        $resetRecord->delete();

        // Auto-login upon reset as required
        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect()->route('user.dashboard')->with('success', 'Votre mot de passe a été réinitialisé avec succès ! Vous êtes désormais connecté.');
    }
}
