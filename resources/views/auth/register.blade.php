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

    <form method="POST" action="{{ route('register.submit') }}" id="registerForm" class="professional-form">
        @csrf

        <!-- Section Identité -->
        <div class="form-section">
            <h3 class="section-title">Identité</h3>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Prénom <span class="required">*</span></label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}"
                           class="form-input" placeholder="Jean" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nom <span class="required">*</span></label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}"
                           class="form-input" placeholder="Dupont" required>
                </div>
            </div>

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
        </div>

        <!-- Section Contact -->
        <div class="form-section">
            <h3 class="section-title">Coordonnées</h3>

            <div class="form-group">
                <label class="form-label">Adresse email <span class="required">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="form-input" placeholder="jean.dupont@email.com" required>
            </div>

            <div class="form-group">
                <label class="form-label">Téléphone <span class="required">*</span></label>
                <input type="tel" name="phone" value="{{ old('phone') }}"
                       class="form-input" placeholder="+225 01 23 45 67 89" required>
            </div>

            <div class="form-group">
                <label class="form-label">Adresse</label>
                <input type="text" name="address" value="{{ old('address') }}"
                       class="form-input" placeholder="123 Rue des Exemples">
            </div>

            <div class="form-group">
                <label class="form-label">Ville <span class="required">*</span></label>
                <input type="text" name="city" value="{{ old('city') }}"
                       class="form-input" placeholder="Abidjan" required>
            </div>
        </div>

        <!-- Section Sécurité -->
        <div class="form-section">
            <h3 class="section-title">Sécurité</h3>

            <div class="form-group">
                <label class="form-label">Mot de passe <span class="required">*</span></label>
                <div class="password-field">
                    <input type="password" name="password" id="password"
                           class="form-input" placeholder="Minimum 8 caractères" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('password')">
                        Afficher
                    </button>
                </div>
                <div class="password-hint">Le mot de passe doit contenir au moins 8 caractères</div>
            </div>

            <div class="form-group">
                <label class="form-label">Confirmer le mot de passe <span class="required">*</span></label>
                <div class="password-field">
                    <input type="password" name="password_confirmation" id="passwordConfirm"
                           class="form-input" placeholder="Répéter le mot de passe" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('passwordConfirm')">
                        Afficher
                    </button>
                </div>
            </div>
        </div>

        <!-- Note de vérification -->
        <div class="verification-notice">
            <p>Un code de vérification sera envoyé à votre adresse email pour sécuriser votre compte.</p>
        </div>

        <!-- Conditions -->
        <div class="form-section terms-section">
            <label class="checkbox-field">
                <input type="checkbox" name="terms" required {{ old('terms') ? 'checked' : '' }}>
                <span class="checkbox-text">
                    J'accepte les <a href="#" target="_blank">conditions d'utilisation</a>
                    et la <a href="#" target="_blank">politique de confidentialité</a> de BHDM.
                    <span class="required">*</span>
                </span>
            </label>
        </div>

        <button type="submit" class="btn-submit" id="submitBtn">
            Créer mon compte
        </button>
    </form>

    <div class="form-footer">
        <p>Déjà un compte ? <a href="{{ route('login') }}" class="link-login">Se connecter</a></p>
    </div>
@endsection

@section('styles')
<style>
    .professional-form {
        max-width: 100%;
    }

    .form-section {
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .form-section:last-of-type {
        border-bottom: none;
    }

    .section-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: #1e3a5f;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 1.25rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #1e3a5f;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: #334155;
        margin-bottom: 0.5rem;
    }

    .required {
        color: #dc2626;
    }

    .form-input,
    .form-select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        font-size: 0.9375rem;
        transition: border-color 0.2s, box-shadow 0.2s;
        background: #ffffff;
    }

    .form-input:focus,
    .form-select:focus {
        outline: none;
        border-color: #1e3a5f;
        box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.1);
    }

    .password-field {
        position: relative;
        display: flex;
        align-items: center;
    }

    .password-field .form-input {
        padding-right: 5rem;
    }

    .toggle-password {
        position: absolute;
        right: 0.5rem;
        background: none;
        border: none;
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 500;
        cursor: pointer;
        padding: 0.25rem 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }

    .toggle-password:hover {
        color: #1e3a5f;
    }

    .password-hint {
        font-size: 0.75rem;
        color: #64748b;
        margin-top: 0.375rem;
    }

    .verification-notice {
        background: #eff6ff;
        border-left: 4px solid #1e3a5f;
        padding: 1rem;
        margin-bottom: 1.5rem;
        border-radius: 0 4px 4px 0;
    }

    .verification-notice p {
        font-size: 0.875rem;
        color: #1e3a5f;
        margin: 0;
    }

    .terms-section {
        background: #f8fafc;
        padding: 1.25rem;
        border-radius: 4px;
        border: 1px solid #e2e8f0;
    }

    .checkbox-field {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        cursor: pointer;
    }

    .checkbox-field input[type="checkbox"] {
        width: 18px;
        height: 18px;
        margin-top: 2px;
        accent-color: #1e3a5f;
        cursor: pointer;
    }

    .checkbox-text {
        font-size: 0.875rem;
        color: #475569;
        line-height: 1.5;
    }

    .checkbox-text a {
        color: #1e3a5f;
        text-decoration: underline;
        font-weight: 500;
    }

    .btn-submit {
        width: 100%;
        padding: 1rem;
        background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 0.9375rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }

    .btn-submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(30, 58, 95, 0.3);
    }

    .btn-submit:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }

    .form-footer {
        text-align: center;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e2e8f0;
    }

    .form-footer p {
        font-size: 0.875rem;
        color: #64748b;
    }

    .link-login {
        color: #1e3a5f;
        font-weight: 600;
        text-decoration: none;
    }

    .link-login:hover {
        text-decoration: underline;
    }

    @media (max-width: 640px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const button = input.nextElementSibling;

        if (input.type === 'password') {
            input.type = 'text';
            button.textContent = 'Masquer';
        } else {
            input.type = 'password';
            button.textContent = 'Afficher';
        }
    }

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
