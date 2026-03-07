@extends('layouts.auth')

@section('title', 'Inscription')

@section('content')
    @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ url('/register') }}" method="post" accept-charset="UTF-8" class="register-form">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">

        <div class="form-block">
            <h3 class="block-title">Informations personnelles</h3>

            <div class="input-row">
                <div class="input-group">
                    <label for="first_name">Prénom <span class="star">*</span></label>
                    <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                </div>

                <div class="input-group">
                    <label for="last_name">Nom <span class="star">*</span></label>
                    <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                </div>
            </div>

            <div class="input-row">
                <div class="input-group">
                    <label for="birth_date">Date de naissance</label>
                    <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date') }}">
                </div>

                <div class="input-group">
                    <label for="gender">Genre</label>
                    <select id="gender" name="gender">
                        <option value="">-- Sélectionner --</option>
                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Homme</option>
                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Femme</option>
                        <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Autre</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-block">
            <h3 class="block-title">Coordonnées</h3>

            <div class="input-group">
                <label for="email">Adresse email <span class="star">*</span></label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            </div>

            <div class="input-group">
                <label for="phone">Téléphone <span class="star">*</span></label>
                <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required>
            </div>

            <div class="input-group">
                <label for="address">Adresse postale</label>
                <input type="text" id="address" name="address" value="{{ old('address') }}">
            </div>

            <div class="input-group">
                <label for="city">Ville <span class="star">*</span></label>
                <input type="text" id="city" name="city" value="{{ old('city') }}" required>
            </div>
        </div>

        <div class="form-block">
            <h3 class="block-title">Sécurité</h3>

            <div class="input-group">
                <label for="password">Mot de passe <span class="star">*</span></label>
                <div class="password-wrap">
                    <input type="password" id="password" name="password" minlength="8" required>
                    <span class="show-pass" onclick="togglePass('password')">Afficher</span>
                </div>
                <small class="help-text">Minimum 8 caractères</small>
            </div>

            <div class="input-group">
                <label for="password_confirmation">Confirmer le mot de passe <span class="star">*</span></label>
                <div class="password-wrap">
                    <input type="password" id="password_confirmation" name="password_confirmation" required>
                    <span class="show-pass" onclick="togglePass('password_confirmation')">Afficher</span>
                </div>
            </div>
        </div>

        <div class="info-box">
            Un code de vérification sera envoyé à votre adresse email pour activer votre compte.
        </div>

        <div class="terms-box">
            <label class="checkbox-wrap">
                <input type="checkbox" name="terms" value="1" required {{ old('terms') ? 'checked' : '' }}>
                <span class="check-text">
                    J'accepte les <a href="#" target="_blank">conditions d'utilisation</a> et la
                    <a href="#" target="_blank">politique de confidentialité</a> <span class="star">*</span>
                </span>
            </label>
        </div>

        <button type="submit" class="submit-btn">Créer mon compte</button>
    </form>

    <div class="bottom-link">
        Déjà inscrit ? <a href="{{ url('/login') }}">Se connecter</a>
    </div>
@endsection

@section('styles')
<style>
    .alert {
        padding: 12px 16px;
        margin-bottom: 20px;
        border-radius: 4px;
        font-size: 14px;
    }

    .alert-error {
        background-color: #fee2e2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    .register-form {
        width: 100%;
    }

    .form-block {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .block-title {
        font-size: 13px;
        font-weight: 600;
        color: #1e3a5f;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 16px;
        padding-bottom: 8px;
        border-bottom: 2px solid #1e3a5f;
    }

    .input-row {
        display: flex;
        gap: 16px;
        margin-bottom: 12px;
    }

    .input-group {
        flex: 1;
        margin-bottom: 12px;
    }

    .input-group label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: #334155;
        margin-bottom: 6px;
    }

    .star {
        color: #dc2626;
    }

    .input-group input,
    .input-group select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        font-size: 14px;
        background: #ffffff;
    }

    .input-group input:focus,
    .input-group select:focus {
        outline: none;
        border-color: #1e3a5f;
    }

    .password-wrap {
        position: relative;
    }

    .password-wrap input {
        padding-right: 70px;
    }

    .show-pass {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 12px;
        color: #64748b;
        cursor: pointer;
        user-select: none;
    }

    .show-pass:hover {
        color: #1e3a5f;
    }

    .help-text {
        display: block;
        font-size: 12px;
        color: #64748b;
        margin-top: 4px;
    }

    .info-box {
        background: #eff6ff;
        border-left: 4px solid #1e3a5f;
        padding: 12px 16px;
        margin-bottom: 20px;
        font-size: 13px;
        color: #1e3a5f;
    }

    .terms-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 16px;
        border-radius: 4px;
        margin-bottom: 20px;
    }

    .checkbox-wrap {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        cursor: pointer;
    }

    .checkbox-wrap input[type="checkbox"] {
        width: 16px;
        height: 16px;
        margin-top: 2px;
        accent-color: #1e3a5f;
    }

    .check-text {
        font-size: 13px;
        color: #475569;
        line-height: 1.5;
    }

    .check-text a {
        color: #1e3a5f;
        text-decoration: underline;
    }

    .submit-btn {
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
    }

    .submit-btn:hover {
        background: #2c5282;
    }

    .bottom-link {
        text-align: center;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
        font-size: 14px;
        color: #64748b;
    }

    .bottom-link a {
        color: #1e3a5f;
        font-weight: 600;
        text-decoration: none;
    }

    .bottom-link a:hover {
        text-decoration: underline;
    }

    @media (max-width: 480px) {
        .input-row {
            flex-direction: column;
            gap: 0;
        }

        .form-block {
            padding: 16px;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    function togglePass(id) {
        var input = document.getElementById(id);
        var span = input.nextElementSibling;

        if (input.type === 'password') {
            input.type = 'text';
            span.textContent = 'Masquer';
        } else {
            input.type = 'password';
            span.textContent = 'Afficher';
        }
    }

    document.querySelector('.register-form').addEventListener('submit', function(e) {
        var pass = document.getElementById('password').value;
        var confirm = document.getElementById('password_confirmation').value;

        if (pass !== confirm) {
            e.preventDefault();
            alert('Les mots de passe ne correspondent pas.');
            return false;
        }

        if (pass.length < 8) {
            e.preventDefault();
            alert('Le mot de passe doit contenir au moins 8 caractères.');
            return false;
        }
    });
</script>
@endsection
