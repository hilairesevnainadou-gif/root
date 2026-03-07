@extends('layouts.auth')

@section('title', 'Inscription')

@section('content')
    @if($errors->any())
        <div class="alert alert-error mb-4">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('register.submit') }}" id="registerForm">
        @csrf

        <!-- Nom et Prénom -->
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Prénom *</label>
                <input type="text" name="first_name" value="{{ old('first_name') }}"
                       class="form-input" placeholder="Jean" required>
            </div>
            <div class="form-group">
                <label class="form-label">Nom *</label>
                <input type="text" name="last_name" value="{{ old('last_name') }}"
                       class="form-input" placeholder="Dupont" required>
            </div>
        </div>

        <!-- Email et Téléphone -->
        <div class="form-group">
            <label class="form-label">Adresse email *</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="form-input" placeholder="jean.dupont@email.com" required>
        </div>

        <div class="form-group">
            <label class="form-label">Téléphone *</label>
            <input type="tel" name="phone" value="{{ old('phone') }}"
                   class="form-input" placeholder="+225 01 23 45 67 89" required>
        </div>

        <!-- Ville -->
        <div class="form-group">
            <label class="form-label">Ville *</label>
            <input type="text" name="city" value="{{ old('city') }}"
                   class="form-input" placeholder="Abidjan" required>
        </div>

        <!-- 🔴 CHAMPS OPTIONNELS (cachés ou dans section avancée) -->
        <details class="form-advanced">
            <summary>Informations complémentaires (optionnel)</summary>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Date de naissance</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date') }}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Genre</label>
                    <select name="gender" class="form-select">
                        <option value="">Sélectionner</option>
                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Homme</option>
                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Femme</option>
                        <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Autre</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Adresse complète</label>
                <input type="text" name="address" value="{{ old('address') }}"
                       class="form-input" placeholder="123 Rue des Exemples, Quartier">
            </div>
        </details>

        <!-- Méthode de vérification -->
        <div class="form-group">
            <label class="form-label">Méthode de vérification *</label>
            <div class="verification-method-selector">
                <label class="verification-method-option">
                    <input type="radio" name="verification_method" value="email"
                           {{ old('verification_method', 'email') == 'email' ? 'checked' : '' }} required>
                    <div class="verification-method-card">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <div>
                            <strong>Email</strong>
                            <small>Code envoyé par email</small>
                        </div>
                    </div>
                </label>
                <label class="verification-method-option">
                    <input type="radio" name="verification_method" value="sms"
                           {{ old('verification_method') == 'sms' ? 'checked' : '' }} required>
                    <div class="verification-method-card">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <div>
                            <strong>SMS</strong>
                            <small>Code envoyé par SMS</small>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Mot de passe -->
        <div class="section-divider">
            <span>Sécurité</span>
        </div>

        <div class="form-group">
            <label class="form-label">Mot de passe *</label>
            <div class="password-input">
                <input type="password" name="password" id="password"
                       class="form-input" placeholder="Minimum 8 caractères" required>
                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>
            </div>
            <div class="password-strength" id="passwordStrength"></div>
        </div>

        <div class="form-group">
            <label class="form-label">Confirmer le mot de passe *</label>
            <div class="password-input">
                <input type="password" name="password_confirmation" id="passwordConfirm"
                       class="form-input" placeholder="Répéter le mot de passe" required>
                <button type="button" class="password-toggle" onclick="togglePassword('passwordConfirm')">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Conditions -->
        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="terms" required {{ old('terms') ? 'checked' : '' }}>
                <span>J'accepte les <a href="#" target="_blank">conditions d'utilisation</a> et la <a href="#" target="_blank">politique de confidentialité</a> *</span>
            </label>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg" id="submitBtn">
            Créer mon compte
        </button>
    </form>

    <p class="text-center mt-4 text-sm text-muted">
        Déjà un compte ?
        <a href="{{ route('login') }}" class="text-primary font-semibold">Se connecter</a>
    </p>
@endsection

@section('styles')
<style>
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    @media (max-width: 480px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }

    .form-advanced {
        margin: 1rem 0;
        padding: 1rem;
        background: #f8fafc;
        border-radius: var(--radius);
        border: 1px solid var(--border);
    }

    .form-advanced summary {
        cursor: pointer;
        font-weight: 500;
        color: var(--text-muted);
    }

    .form-advanced[open] summary {
        margin-bottom: 1rem;
    }

    .verification-method-selector {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .verification-method-option {
        cursor: pointer;
    }

    .verification-method-option input {
        position: absolute;
        opacity: 0;
    }

    .verification-method-card {
        border: 2px solid var(--border);
        border-radius: var(--radius);
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        transition: all 0.2s;
    }

    .verification-method-card svg {
        color: var(--text-muted);
        flex-shrink: 0;
    }

    .verification-method-card strong {
        display: block;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .verification-method-card small {
        display: block;
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .verification-method-option input:checked + .verification-method-card {
        border-color: var(--primary);
        background: #eff6ff;
    }

    .verification-method-option input:checked + .verification-method-card svg {
        color: var(--primary);
    }

    .section-divider {
        display: flex;
        align-items: center;
        margin: 1.5rem 0;
        color: var(--text-muted);
        font-size: 0.875rem;
        font-weight: 500;
    }

    .section-divider::before,
    .section-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border);
    }

    .section-divider span {
        padding: 0 1rem;
    }

    .password-input {
        position: relative;
    }

    .password-toggle {
        position: absolute;
        right: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 0.25rem;
    }

    .password-strength {
        height: 4px;
        margin-top: 0.5rem;
        border-radius: 2px;
        background: var(--border);
        transition: all 0.3s;
    }

    .password-strength.weak { background: var(--danger); width: 33%; }
    .password-strength.medium { background: var(--warning); width: 66%; }
    .password-strength.strong { background: var(--success); width: 100%; }

    .checkbox-label {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        font-size: 0.875rem;
        color: var(--text-muted);
    }

    .checkbox-label input {
        margin-top: 0.25rem;
    }

    .checkbox-label a {
        color: var(--primary);
        text-decoration: underline;
    }
</style>
@endsection

@section('scripts')
<script>
    // Toggle password visibility
    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        input.type = input.type === 'password' ? 'text' : 'password';
    }

    // Password strength
    document.getElementById('password').addEventListener('input', function() {
        const strength = calculateStrength(this.value);
        document.getElementById('passwordStrength').className = 'password-strength ' + strength;
    });

    function calculateStrength(password) {
        if (password.length < 6) return '';

        let score = 0;
        if (password.length >= 8) score++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;

        if (score <= 2) return 'weak';
        if (score === 3) return 'medium';
        return 'strong';
    }

    // Form validation
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirm = document.getElementById('passwordConfirm').value;

        if (password !== confirm) {
            e.preventDefault();
            alert('Les mots de passe ne correspondent pas.');
            return false;
        }

        if (password.length < 8) {
            e.preventDefault();
            alert('Le mot de passe doit contenir au moins 8 caractères.');
            return false;
        }

        document.getElementById('submitBtn').disabled = true;
        document.getElementById('submitBtn').textContent = 'Création en cours...';
    });
</script>
@endsection
