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

    <form method="POST" action="{{ route('login.submit') }}">
        @csrf

        <div class="form-group">
            <label class="form-label">Adresse email</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="form-input" placeholder="votre@email.com" required autofocus>
        </div>

        <div class="form-group">
            <label class="form-label">Mot de passe</label>
            <input type="password" name="password" class="form-input" required>
        </div>

        <div class="form-group flex items-center justify-between">
            <label class="flex items-center">
                <input type="checkbox" name="remember" class="mr-2">
                <span class="text-sm">Se souvenir de moi</span>
            </label>
            <a href="{{ route('password.request') }}" class="text-sm text-primary hover:underline">
                Mot de passe oublié ?
            </a>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg">
            Se connecter
        </button>
    </form>

    <p class="text-center mt-4 text-sm text-muted">
        Pas encore de compte ?
        <a href="{{ route('register') }}" class="text-primary font-semibold">S'inscrire</a>
    </p>
@endsection
