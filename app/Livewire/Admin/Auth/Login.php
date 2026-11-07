<?php

namespace App\Livewire\Admin\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use App\Models\AdminLog;

class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $showPassword = false;
    public int $num1 = 0;
    public int $num2 = 0;
    public ?int $captchaAnswer = null;
    public ?string $userCaptcha = '';
    public string $errorMessage = '';

    protected function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
            'userCaptcha' => 'required|integer',
        ];
    }

    public function mount(): void
    {
        if (Auth::guard('admin')->check()) {
            $this->redirectRoute('admin.dashboard', navigate: true);
            return;
        }
        $this->generateCaptcha();
    }

    public function generateCaptcha(): void
    {
        $this->num1 = rand(1, 9);
        $this->num2 = rand(1, 9);
        $this->captchaAnswer = $this->num1 + $this->num2;
        $this->userCaptcha = '';
    }

    public function togglePasswordVisibility(): void
    {
        $this->showPassword = !$this->showPassword;
    }

    public function authenticate()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'userCaptcha' => 'required',
        ], [
            'email.required' => 'L\'adresse email est requise.',
            'password.required' => 'Le mot de passe est requis.',
            'userCaptcha.required' => 'Veuillez résoudre le calcul de sécurité.',
        ]);

        $this->errorMessage = '';

        // 1. Verify Math Captcha
        if ((int)$this->userCaptcha !== (int)$this->captchaAnswer) {
            $this->errorMessage = 'Le résultat du calcul de sécurité est incorrect.';
            $this->generateCaptcha();
            return;
        }

        // 2. Find Admin by Email
        $admin = Admin::where('email', $this->email)->first();

        if (!$admin) {
            $this->errorMessage = 'Identifiants invalides.';
            $this->generateCaptcha();
            return;
        }

        // 3. Check if Admin is already Banned
        if ($admin->is_banned) {
            AdminLog::record('login_blocked_banned', "Tentative de connexion sur un compte banni ({$this->email})", $admin);
            $this->errorMessage = 'Ce compte administrateur est banni. Seul le Super Administrateur peut rétablir votre compte dans le menu dédié.';
            $this->generateCaptcha();
            return;
        }

        // 4. Attempt Authentication
        if (Auth::guard('admin')->attempt(['email' => $this->email, 'password' => $this->password])) {
            // Reset failed login attempts
            $admin->update([
                'failed_login_attempts' => 0,
            ]);

            AdminLog::record('login', 'Connexion réussie à l\'espace d\'administration', $admin);

            session()->regenerate();
            return redirect()->route('admin.dashboard');
        }

        // 5. Failed Attempt Handling & Auto-ban on > 5 attempts
        $admin->increment('failed_login_attempts');
        $attemptsLeft = 5 - $admin->failed_login_attempts;

        if ($admin->failed_login_attempts >= 5) {
            $admin->update([
                'is_banned' => true,
                'banned_at' => now(),
            ]);

            AdminLog::record('account_auto_banned', "Compte ({$this->email}) banni automatiquement suite à 5 tentatives de connexion erronées.", $admin);

            $this->errorMessage = 'ALERTE SÉCURITÉ : Vous avez échoué plus de 5 fois. Votre compte est désormais banni ! Veuillez contacter le Super Administrateur pour rétablir vos accès.';
        } else {
            AdminLog::record('login_failed', "Tentative échouée ({$admin->failed_login_attempts}/5) pour {$this->email}", $admin);
            $this->errorMessage = "Mot de passe incorrect. Attention, il vous reste {$attemptsLeft} tentative(s) avant bannissement automatique.";
        }

        $this->password = '';
        $this->generateCaptcha();
    }

    public function render()
    {
        return view('livewire.admin.auth.login')
            ->layout('layouts.admin-auth', ['title' => 'Connexion Administrateur']);
    }
}
