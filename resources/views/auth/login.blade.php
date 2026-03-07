@extends('layouts.auth')

@section('title', 'Connexion')

@section('content')
    @if($errors->any())
        <div class="alert alert-error mb-4">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if(session('status'))
        <div class="alert alert-success mb-4">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login.submit') }}" id="loginForm" class="professional-form">
        @csrf

        <div class="login-method-tabs">
            <button type="button" class="method-tab active" data-method="email" onclick="switchMethod('email')">
                Email
            </button>
            <button type="button" class="method-tab" data-method="phone" onclick="switchMethod('phone')">
                Téléphone
            </button>
        </div>

        <input type="hidden" name="login_method" id="loginMethod" value="email">

        <!-- Champ Email -->
        <div class="form-group" id="emailField">
            <label class="form-label">Adresse email <span class="required">*</span></label>
            <input type="email" name="email" id="emailInput" value="{{ old('email') }}"
                   class="form-input" placeholder="jean.dupont@email.com" required>
        </div>

        <!-- Champ Téléphone (caché par défaut) -->
        <div class="form-group hidden" id="phoneField">
            <label class="form-label">Numéro de téléphone <span class="required">*</span></label>
            <input type="tel" name="phone" id="phoneInput" value="{{ old('phone') }}"
                   class="form-input" placeholder="+225 01 23 45 67 89">
        </div>

        <div class="form-group">
            <label class="form-label">Mot de passe <span class="required">*</span></label>
            <div class="password-field">
                <input type="password" name="password" id="password" class="form-input" placeholder="Votre mot de passe" required>
                <button type="button" class="toggle-password" onclick="togglePassword()">
                    Afficher
                </button>
            </div>
        </div>

        <div class="form-options">
            <label class="checkbox-field">
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                <span class="checkbox-text">Se souvenir de moi</span>
            </label>
            <a href="{{ route('password.request') }}" class="forgot-link">
                Mot de passe oublié ?
            </a>
        </div>

        <button type="submit" class="btn-submit" id="submitBtn">
            Se connecter
        </button>
    </form>

    <div class="form-footer">
        <p>Pas encore de compte ? <a href="{{ route('register') }}" class="link-register">Créer un compte</a></p>
    </div>
@endsection

@section('styles')
<style>
    .professional-form {
        max-width: 100%;
    }

    .login-method-tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        border-bottom: 2px solid #e2e8f0;
    }

    .method-tab {
        flex: 1;
        padding: 0.75rem;
        background: none;
        border: none;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        font-size: 0.875rem;
        font-weight: 500;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }

    .method-tab:hover {
        color: #1e3a5f;
    }

    .method-tab.active {
        color: #1e3a5f;
        border-bottom-color: #1e3a5f;
    }

    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-group.hidden {
        display: none;
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

    .form-input {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        font-size: 0.9375rem;
        transition: border-color 0.2s, box-shadow 0.2s;
        background: #ffffff;
    }

    .form-input:focus {
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

    .form-options {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .checkbox-field {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
    }

    .checkbox-field input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: #1e3a5f;
        cursor: pointer;
    }

    .checkbox-text {
        font-size: 0.875rem;
        color: #475569;
    }

    .forgot-link {
        font-size: 0.875rem;
        color: #1e3a5f;
        text-decoration: none;
        font-weight: 500;
    }

    .forgot-link:hover {
        text-decoration: underline;
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

    .link-register {
        color: #1e3a5f;
        font-weight: 600;
        text-decoration: none;
    }

    .link-register:hover {
        text-decoration: underline;
    }

    .alert {
        padding: 1rem;
        border-radius: 4px;
        font-size: 0.875rem;
    }

    .alert-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #dc2626;
    }

    .alert-success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #16a34a;
    }

    @media (max-width: 480px) {
        .form-options {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    function switchMethod(method) {
        const emailField = document.getElementById('emailField');
        const phoneField = document.getElementById('phoneField');
        const emailInput = document.getElementById('emailInput');
        const phoneInput = document.getElementById('phoneInput');
        const loginMethod = document.getElementById('loginMethod');
        const tabs = document.querySelectorAll('.method-tab');

        // Mise à jour des onglets
        tabs.forEach(tab => {
            tab.classList.remove('active');
            if (tab.dataset.method === method) {
                tab.classList.add('active');
            }
        });

        // Mise à jour des champs
        if (method === 'email') {
            emailField.classList.remove('hidden');
            phoneField.classList.add('hidden');
            emailInput.required = true;
            phoneInput.required = false;
            phoneInput.value = '';
            loginMethod.value = 'email';
        } else {
            emailField.classList.add('hidden');
            phoneField.classList.remove('hidden');
            emailInput.required = false;
            phoneInput.required = true;
            emailInput.value = '';
            loginMethod.value = 'phone';
        }
    }

    function togglePassword() {
        const password = document.getElementById('password');
        const button = password.nextElementSibling;

        if (password.type === 'password') {
            password.type = 'text';
            button.textContent = 'Masquer';
        } else {
            password.type = 'password';
            button.textContent = 'Afficher';
        }
    }

    // Validation avant soumission
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const method = document.getElementById('loginMethod').value;
        const email = document.getElementById('emailInput').value;
        const phone = document.getElementById('phoneInput').value;
        const password = document.getElementById('password').value;

        if (method === 'email' && !email) {
            e.preventDefault();
            alert('Veuillez saisir votre adresse email.');
            return false;
        }

        if (method === 'phone' && !phone) {
            e.preventDefault();
            alert('Veuillez saisir votre numéro de téléphone.');
            return false;
        }

        if (!password) {
            e.preventDefault();
            alert('Veuillez saisir votre mot de passe.');
            return false;
        }

        document.getElementById('submitBtn').disabled = true;
        document.getElementById('submitBtn').textContent = 'Connexion en cours...';
    });

    // Préservation de la méthode après erreur de validation
    @if(old('login_method') === 'phone')
        switchMethod('phone');
    @endif
</script>
@endsection
