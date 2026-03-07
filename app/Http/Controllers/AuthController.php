<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordMail;
use App\Mail\VerificationCodeMail;
use App\Models\User;
use App\Models\VerificationCode;
use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    // =====================
    // LOGIN / LOGOUT
    // =====================

    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
{
    $loginMethod = $request->input('login_method', 'email');

    if ($loginMethod === 'phone') {
        $credentials = $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required'],
        ], [
            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        $user = User::where('phone', $credentials['phone'])->first();
    } else {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'Veuillez entrer une adresse email valide.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        $user = User::where('email', $credentials['email'])->first();
    }

    // Tentative d'authentification
    if (!$user || !Hash::check($request->password, $user->password)) {
        return back()->withErrors([
            'email' => 'Les identifiants ne correspondent pas.',
        ])->withInput($request->except('password'));
    }

    // Connexion manuelle
    Auth::login($user, $request->boolean('remember'));

    $request->session()->regenerate();

    // Vérification du compte
    if (!$user->is_verified) {
        Auth::logout();
        $request->session()->invalidate();

        Session::put('verification_user_id', $user->id);
        Session::put('verification_method', 'email');

        try {
            $this->sendVerificationCode($user, 'email');
        } catch (\Exception $e) {
            Log::error("Erreur envoi code de vérification pour user {$user->id} : " . $e->getMessage());
        }

        return redirect()->route('verification.show')
            ->with('warning', 'Votre compte n\'est pas encore vérifié. Un code a été envoyé à votre email.');
    }

    if (!$user->is_active) {
        Auth::logout();
        $request->session()->invalidate();

        return back()->withErrors([
            'email' => 'Votre compte a été désactivé. Contactez l\'administration.',
        ]);
    }

    // Mise à jour des infos de connexion
    $user->update([
        'last_login_at' => now(),
        'last_login_ip' => $request->ip(),
        'last_login_device' => $request->userAgent(),
    ]);

    // Redirection selon le rôle
    if ($user->is_admin || $user->is_moderator) {
        return redirect()->intended(route('admin.dashboard'));
    }

    return redirect()->intended(route('client.dashboard'));
}

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // =====================
    // REGISTER
    // =====================

    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'city' => ['required', 'string', 'max:255'],
            'terms' => ['required', 'accepted'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female,other'],
            'address' => ['nullable', 'string', 'max:500'],
        ], [
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom est obligatoire.',
            'email.required' => 'L\'email est obligatoire.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'phone.required' => 'Le téléphone est obligatoire.',
            'phone.unique' => 'Ce numéro est déjà utilisé.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit faire au moins 8 caractères.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'city.required' => 'La ville est obligatoire.',
            'terms.accepted' => 'Vous devez accepter les conditions d\'utilisation.',
        ]);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'name' => $validated['first_name'].' '.$validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'member_type' => 'particulier',
            'member_status' => 'pending',
            'is_active' => true,
            'is_verified' => false,
            'preferred_verification_method' => 'email', // Forcé à email
            'birth_date' => $validated['birth_date'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'],
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'wallet_number' => 'BHDM-WALLET-'.strtoupper(Str::random(8)),
            'balance' => 0,
            'currency' => 'XOF',
            'status' => 'active',
            'activated_at' => now(),
        ]);

        // Stockage en session pour la vérification
        Session::put('verification_user_id', $user->id);
        Session::put('verification_method', 'email');

        // Envoi du code de vérification par email
        try {
            $this->sendVerificationCode($user, 'email');
        } catch (\Exception $e) {
            Log::error("Erreur envoi code de vérification pour user {$user->id} : ".$e->getMessage());
        }

        return redirect()->route('verification.show')
            ->with('success', 'Compte créé avec succès ! Un code de vérification a été envoyé à votre adresse email.');
    }

    // =====================
    // VERIFICATION
    // =====================

    public function showVerification(): View
    {
        $userId = Session::get('verification_user_id');
        $method = Session::get('verification_method', 'email');

        if (! $userId) {
            return view('auth.login')->with('error', 'Session expirée. Veuillez vous reconnecter.');
        }

        $user = User::find($userId);

        if (! $user || $user->is_verified) {
            Session::forget(['verification_user_id', 'verification_method']);

            return view('auth.login')->with('info', 'Votre compte est déjà vérifié. Veuillez vous connecter.');
        }

        $maskedEmail = $method === 'email' ? $this->maskEmail($user->email) : null;
        $maskedPhone = $method === 'sms' ? $this->maskPhone($user->phone) : null;

        return view('auth.verify', compact('method', 'maskedEmail', 'maskedPhone'));
    }

    public function verifyCode(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ], [
            'code.required' => 'Le code est obligatoire.',
            'code.size' => 'Le code doit contenir 6 chiffres.',
        ]);

        $userId = Session::get('verification_user_id');
        if (! $userId) {
            return redirect()->route('login')->with('error', 'Session expirée.');
        }

        $verification = VerificationCode::where('user_id', $userId)
            ->where('code', $request->code)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (! $verification) {
            return back()->withErrors(['code' => 'Code invalide ou expiré.']);
        }

        $verification->update(['used' => true]);

        $user = User::find($userId);
        $user->update([
            'is_verified' => true,
            'email_verified_at' => now(),
            'member_status' => 'active',
            'member_id' => 'BHDM-'.now()->year.'-'.str_pad($user->id, 6, '0', STR_PAD_LEFT),
            'member_since' => now(),
        ]);

        Session::forget(['verification_user_id', 'verification_method']);
        Auth::login($user);

        return redirect()->route('client.dashboard')
            ->with('success', 'Votre compte est vérifié ! Bienvenue sur BHDM.');
    }

    public function resendCode(): RedirectResponse
    {
        $userId = Session::get('verification_user_id');
        $method = Session::get('verification_method', 'email');

        if (! $userId) {
            return redirect()->route('login')->with('error', 'Session expirée.');
        }

        $user = User::find($userId);
        if (! $user || $user->is_verified) {
            return redirect()->route('login')->with('info', 'Compte déjà vérifié.');
        }

        $lastCode = VerificationCode::where('user_id', $userId)
            ->where('created_at', '>', now()->subMinute())
            ->first();

        if ($lastCode) {
            $seconds = 60 - now()->diffInSeconds($lastCode->created_at);

            return back()->with('error', "Veuillez attendre {$seconds} secondes.");
        }

        try {
            $this->sendVerificationCode($user, $method);
        } catch (\Exception $e) {
            Log::error("Erreur envoi code de vérification pour user {$user->id} : ".$e->getMessage());
        }

        return back()->with('success', 'Nouveau code envoyé !');
    }

    public function changeMethod(Request $request): RedirectResponse
    {
        $userId = Session::get('verification_user_id');
        if (! $userId) {
            return redirect()->route('login')->with('error', 'Session expirée.');
        }

        $user = User::find($userId);
        $newMethod = Session::get('verification_method') === 'email' ? 'sms' : 'email';
        Session::put('verification_method', $newMethod);
        $user->update(['preferred_verification_method' => $newMethod]);

        try {
            $this->sendVerificationCode($user, $newMethod);
        } catch (\Exception $e) {
            Log::error("Erreur envoi code de vérification pour user {$user->id} : ".$e->getMessage());
        }

        return redirect()->route('verification.show')->with('success', 'Méthode changée. Nouveau code envoyé.');
    }

    // Dans VerificationController
    public function updateContact(Request $request)
    {
        $method = $request->input('method');

        if ($method === 'email') {
            $request->validate(['new_email' => 'required|email']);
            // Mettre à jour en session + en base
            $user = User::find(session('verification_user_id'));
            $user->update(['email' => $request->new_email]);
        } else {
            $request->validate(['new_phone' => 'required|string|min:8']);
            $user = User::find(session('verification_user_id'));
            $user->update(['phone' => $request->new_phone]);
        }

        // Renvoyer un nouveau code
        // ... votre logique d'envoi de code

        return back()->with('success', 'Contact mis à jour. Un nouveau code a été envoyé.');
    }
    // =====================
    // PRIVATE METHODS
    // =====================

    private function sendVerificationCode(User $user, ?string $method = null): void
    {
        $method = $method ?? $user->preferred_verification_method ?? 'email';

        VerificationCode::where('user_id', $user->id)
            ->where('used', false)
            ->update(['used' => true]);

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        VerificationCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'method' => $method,
            'expires_at' => now()->addMinutes(10),
        ]);

        if ($method === 'email') {
            try {
                Mail::to($user->email)->send(new VerificationCodeMail($code));
            } catch (\Exception $e) {
                Log::error("Erreur envoi mail verification user {$user->id} : ".$e->getMessage());
            }
        } else {
            $this->sendSmsCode($user, $code);
        }
    }

    private function sendSmsCode(User $user, string $code): void
    {
        $apiKey = env('TERMII_API_KEY');
        $senderId = env('TERMII_SENDER_ID');
        $baseUrl = env('TERMII_BASE_URL', 'https://v3.api.termii.com');

        $to = preg_replace('/\s+/', '', $user->phone);

        // Normalisation numéro Bénin
        if (substr($to, 0, 1) === '0') {
            $to = '229'.substr($to, 1);
        }

        if (substr($to, 0, 3) !== '229') {
            $to = '229'.$to;
        }

        if (! $apiKey) {
            Log::error('TERMII_API_KEY manquante');

            return;
        }

        try {

            // $response = Http::withHeaders([
            //     'Content-Type' => 'application/json',
            // ])->post($baseUrl.'/sms/send', [
            //     'to' => $to,
            //     'from' => $senderId,
            //     'sms' => "Votre code de vérification BHDM est : $code",
            //     'type' => 'plain',
            //     'channel' => 'generic',
            //     'api_key' => $apiKey,
            // ]);
            $response = Http::withOptions(['verify' => false])
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post('https://api.ng.termii.com/api/sms/otp/send', [
                    'api_key' => $apiKey,
                    'message_type' => 'NUMERIC',
                    'to' => $to,
                    'from' => 'N-Alert',
                    'channel' => 'generic',
                    'pin_attempts' => 3,
                    'pin_time_to_live' => 10,
                    'pin_length' => 6,
                    'pin_placeholder' => '< 123456 >',
                    'message_text' => 'Votre code de vérification BHDM est : '.$code,
                    'pin_type' => 'NUMERIC',
                ]);

            if ($response->successful()) {
                Log::info("SMS envoyé avec succès à {$to}", $response->json());
            } else {
                Log::error('Erreur Termii : '.$response->body());
            }

        } catch (\Exception $e) {
            Log::error('Exception SMS Termii : '.$e->getMessage());
        }
    }

    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        $name = $parts[0];
        $domain = $parts[1] ?? '';
        $maskedName = substr($name, 0, 2).str_repeat('*', max(strlen($name) - 4, 2)).substr($name, -2);

        return $maskedName.'@'.$domain;
    }

    private function maskPhone(string $phone): string
    {
        $length = strlen($phone);
        if ($length <= 4) {
            return $phone;
        }

        return str_repeat('*', $length - 4).substr($phone, -4);
    }

    // =====================
    // PASSWORD RESET
    // =====================

     /**
     * Affiche le formulaire de demande de réinitialisation
     */
    public function showForgotPasswordForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Envoie le lien de réinitialisation par email
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'Veuillez entrer une adresse email valide.',
            'email.exists' => 'Cette adresse email n\'est pas enregistrée.',
        ]);

        $email = $request->email;
        $token = Str::random(64);

        // Stocker le token (Laravel 10+ utilise password_reset_tokens)
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // ENVOI DE L'EMAIL
        try {
            Mail::to($email)->send(new ResetPasswordMail($token, $email));

            Log::info("Email de réinitialisation envoyé à : {$email}");

        } catch (\Exception $e) {
            Log::error("Erreur envoi email reset password à {$email} : " . $e->getMessage());

            // En développement, on peut logguer le token pour tester
            if (app()->environment('local')) {
                Log::info("TOKEN DEBUG pour {$email} : {$token}");
            }

            return back()->withErrors(['email' => 'Impossible d\'envoyer l\'email. Veuillez réessayer plus tard.']);
        }

        return back()->with('status', 'Un lien de réinitialisation a été envoyé à votre adresse email.');
    }

    /**
     * Affiche le formulaire de nouveau mot de passe
     */
    public function showResetPasswordForm(string $token): View
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    /**
     * Traite la réinitialisation du mot de passe
     */
    public function resetPassword(Request $request): RedirectResponse
{
    $request->validate([
        'token' => ['required'],
        'email' => ['required', 'email'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ], [
        'token.required' => 'Le token est manquant.',
        'email.required' => 'L\'email est obligatoire.',
        'email.email' => 'L\'email n\'est pas valide.',
        'password.required' => 'Le mot de passe est obligatoire.',
        'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        'password.confirmed' => 'Les mots de passe ne correspondent pas.',
    ]);

    $email = $request->email;
    $token = $request->token;

    // Vérifier le token
    $reset = DB::table('password_reset_tokens')
        ->where('email', $email)
        ->first();

    if (! $reset) {
        return redirect()->route('password.request')
            ->withErrors(['email' => 'Aucune demande de réinitialisation trouvée pour cet email.']);
    }

    if (! Hash::check($token, $reset->token)) {
        return redirect()->route('password.reset', ['token' => $token])
            ->withErrors(['email' => 'Le lien de réinitialisation est invalide.']);
    }

    // Vérifier expiration (60 minutes)
    if (now()->diffInMinutes($reset->created_at) > 60) {
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return redirect()->route('password.request')
            ->withErrors(['email' => 'Ce lien de réinitialisation a expiré. Veuillez faire une nouvelle demande.']);
    }

    // Mettre à jour le mot de passe
    $user = User::where('email', $email)->first();

    if (! $user) {
        return redirect()->route('password.request')
            ->withErrors(['email' => 'Utilisateur non trouvé.']);
    }

    $user->update([
        'password' => Hash::make($request->password),
    ]);

    // Supprimer le token utilisé
    DB::table('password_reset_tokens')->where('email', $email)->delete();

    Log::info("Mot de passe réinitialisé pour : {$email}");

    return redirect()->route('login')
        ->with('success', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.');
}
}
