@extends('layouts.auth')

@section('title', 'Connexion')

@section('content')
    @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if(session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    <div class="login-tabs">
        <button type="button" class="tab-btn active" data-method="email" onclick="switchMethod('email')">
            Email
        </button>
        <button type="button" class="tab-btn" data-method="phone" onclick="switchMethod('phone')">
            Téléphone
        </button>
    </div>

    <form action="{{ url('/login') }}" method="post" id="loginForm" class="login-form">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="login_method" id="loginMethod" value="email">

        <div class="input-group" id="emailField">
            <label for="email">Adresse email <span class="required">*</span></label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="votre@email.com">
        </div>

        <div class="input-group hidden" id="phoneField">
            <label for="phone">Numéro de téléphone <span class="required">*</span></label>
            <input type="text" id="phone" name="phone" value="{{ old('phone') }}" placeholder="0123456789">
        </div>

        <div class="input-group">
            <label for="password">Mot de passe <span class="required">*</span></label>
            <div class="password-wrap">
                <input type="password" id="password" name="password" placeholder="Votre mot de passe" required>
                <span class="toggle-pass" onclick="togglePass()">Afficher</span>
            </div>
        </div>

        <div class="form-options">
            <label class="checkbox-wrap">
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                <span>Se souvenir de moi</span>
            </label>
            <a href="{{ url('/forgot-password') }}" class="forgot-link">Mot de passe oublié ?</a>
        </div>

        <button type="submit" class="btn-submit" id="submitBtn">Se connecter</button>
    </form>

    <div class="register-link">
        Pas encore de compte ? <a href="{{ url('/register') }}">Créer un compte</a>
    </div>
@endsection

@section('styles')
<style>
    .alert {
        padding: 12px 16px;
        margin-bottom: 16px;
        border-radius: 4px;
        font-size: 14px;
    }

    .alert-error {
        background: #fee2e2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    .alert-success {
        background: #dcfce7;
        border: 1px solid #bbf7d0;
        color: #166534;
    }

    .login-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 20px;
        border-bottom: 2px solid #e2e8f0;
    }

    .tab-btn {
        flex: 1;
        padding: 12px;
        background: none;
        border: none;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        font-size: 14px;
        font-weight: 500;
        color: #64748b;
        cursor: pointer;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.2s;
    }

    .tab-btn:hover {
        color: #1e3a5f;
    }

    .tab-btn.active {
        color: #1e3a5f;
        border-bottom-color: #1e3a5f;
    }

    .login-form {
        width: 100%;
    }

    .input-group {
        margin-bottom: 16px;
    }

    .input-group.hidden {
        display: none;
    }

    .input-group label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: #334155;
        margin-bottom: 6px;
    }

    .required {
        color: #dc2626;
    }

    .input-group input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        font-size: 14px;
        transition: border-color 0.2s;
    }

    .input-group input:focus {
        outline: none;
        border-color: #1e3a5f;
    }

    .password-wrap {
        position: relative;
    }

    .password-wrap input {
        padding-right: 70px;
    }

    .toggle-pass {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 12px;
        color: #64748b;
        cursor: pointer;
        user-select: none;
        text-transform: uppercase;
    }

    .toggle-pass:hover {
        color: #1e3a5f;
    }

    .form-options {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .checkbox-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        font-size: 13px;
        color: #475569;
    }

    .checkbox-wrap input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: #1e3a5f;
    }

    .forgot-link {
        font-size: 13px;
        color: #1e3a5f;
        text-decoration: none;
        font-weight: 500;
    }

    .forgot-link:hover {
        text-decoration: underline;
    }

    .btn-submit {
        width: 100%;
        padding: 14px;
        background: #1e3a5f;
        color: #ffffff;
        border: none;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-submit:hover {
        background: #2c5282;
    }

    .btn-submit:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .register-link {
        text-align: center;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
        font-size: 14px;
        color: #64748b;
    }

    .register-link a {
        color: #1e3a5f;
        font-weight: 600;
        text-decoration: none;
    }

    .register-link a:hover {
        text-decoration: underline;
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
        var emailField = document.getElementById('emailField');
        var phoneField = document.getElementById('phoneField');
        var emailInput = document.getElementById('email');
        var phoneInput = document.getElementById('phone');
        var loginMethod = document.getElementById('loginMethod');
        var tabs = document.querySelectorAll('.tab-btn');

        tabs.forEach(function(tab) {
            tab.classList.remove('active');
            if (tab.dataset.method === method) {
                tab.classList.add('active');
            }
        });

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

    function togglePass() {
        var input = document.getElementById('password');
        var span = input.nextElementSibling;

        if (input.type === 'password') {
            input.type = 'text';
            span.textContent = 'Masquer';
        } else {
            input.type = 'password';
            span.textContent = 'Afficher';
        }
    }

    document.getElementById('loginForm').addEventListener('submit', function() {
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('submitBtn').textContent = 'Connexion en cours...';
    });

    // Préservation méthode après erreur
    @if(old('login_method') === 'phone')
        switchMethod('phone');
    @endif
</script>
@endsection
