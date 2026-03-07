@extends('layouts.auth')

@section('title', 'Mot de passe oublié')

@section('content')
    <div class="forgot-header">
        <div class="forgot-icon">
            <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
        </div>
        <h2>Mot de passe oublié</h2>
        <p>Entrez votre adresse email et nous vous enverrons un lien pour réinitialiser votre mot de passe.</p>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ url('/forgot-password') }}" method="post" class="forgot-form">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">

        <div class="input-group">
            <label for="email">Adresse email <span class="required">*</span></label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="votre@email.com" required autofocus>
        </div>

        <button type="submit" class="btn-submit">Envoyer le lien</button>
    </form>

    <div class="back-link">
        <a href="{{ url('/login') }}">Retour à la connexion</a>
    </div>
@endsection

@section('styles')
<style>
    .forgot-header {
        text-align: center;
        margin-bottom: 24px;
    }

    .forgot-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 16px;
        background: #eff6ff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #1e3a5f;
    }

    .forgot-header h2 {
        font-size: 20px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 8px;
    }

    .forgot-header p {
        font-size: 14px;
        color: #64748b;
        line-height: 1.6;
    }

    .alert {
        padding: 12px 16px;
        margin-bottom: 16px;
        border-radius: 4px;
        font-size: 14px;
    }

    .alert-success {
        background: #dcfce7;
        border: 1px solid #bbf7d0;
        color: #166534;
    }

    .alert-error {
        background: #fee2e2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    .forgot-form {
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

    .back-link {
        text-align: center;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
    }

    .back-link a {
        font-size: 14px;
        color: #64748b;
        text-decoration: none;
    }

    .back-link a:hover {
        color: #1e3a5f;
        text-decoration: underline;
    }
</style>
@endsection
