@extends('layouts.auth')

@section('title', 'Mot de passe oublié')

@section('content')
    <div class="text-center mb-6">
        <div class="forgot-icon">
            <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
        </div>
        <h2 class="text-xl font-semibold mb-2">Mot de passe oublié ?</h2>
        <p class="text-muted">
            Entrez votre adresse email et nous vous enverrons un lien pour réinitialiser votre mot de passe.
        </p>
    </div>

    @if(session('status'))
        <div class="alert alert-success mb-4">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-error mb-4">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="form-group">
            <label class="form-label">Adresse email *</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="form-input" placeholder="votre@email.com" required autofocus>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg">
            Envoyer le lien
        </button>
    </form>

    <p class="text-center mt-4">
        <a href="{{ route('login') }}" class="text-sm text-muted hover:text-primary">
            ← Retour à la connexion
        </a>
    </p>
@endsection

@section('styles')
<style>
    .forgot-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 1rem;
        background: #fef3c7;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #f59e0b;
    }
</style>
@endsection
