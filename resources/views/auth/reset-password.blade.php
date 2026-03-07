@extends('layouts.auth')

@section('title', 'Réinitialisation du mot de passe')

@section('content')
    <div class="reset-header">
        <h2>Nouveau mot de passe</h2>
        <p>Entrez votre nouveau mot de passe.</p>
    </div>

    @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ url('/reset-password') }}" method="post" class="reset-form">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="input-group">
            <label for="email">Adresse email <span class="required">*</span></label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
        </div>

        <div class="input-group">
            <label for="password">Nouveau mot de passe <span class="required">*</span></label>
            <div class="password-wrap">
                <input type="password" id="password" name="password" minlength="8" required>
                <span class="toggle-pass" onclick="togglePass('password')">Afficher</span>
            </div>
        </div>

        <div class="input-group">
            <label for="password_confirmation">Confirmer le mot de passe <span class="required">*</span></label>
            <div class="password-wrap">
                <input type="password" id="password_confirmation" name="password_confirmation" required>
                <span class="toggle-pass" onclick="togglePass('password_confirmation')">Afficher</span>
            </div>
        </div>

        <button type="submit" class="btn-submit">Réinitialiser le mot de passe</button>
    </form>
@endsection

@section('styles')
<style>
    .reset-header {
        text-align: center;
        margin-bottom: 24px;
    }

    .reset-header h2 {
        font-size: 20px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 8px;
    }

    .reset-header p {
        font-size: 14px;
        color: #64748b;
    }

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

    .reset-form {
        width: 100%;
    }

    .input-group {
        margin-bottom: 16px;
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
    }

    .btn-submit:hover {
        background: #2c5282;
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
</script>
@endsection
