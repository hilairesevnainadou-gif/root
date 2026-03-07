@extends('layouts.auth')

@section('title', 'Vérification du compte')

@section('content')
    @if(!isset($method))
        <div class="alert alert-error mb-4">
            Erreur de session. <a href="{{ route('login') }}">Retour à la connexion</a>
        </div>
    @else
        <div class="text-center mb-6">
            <div class="verification-icon">
                @if($method === 'email')
                    <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                @else
                    <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                @endif
            </div>
            <h2 class="text-xl font-semibold mb-2">Vérifiez votre compte</h2>
            <p class="text-muted">
                @if($method === 'email' && isset($maskedEmail))
                    Un code à 6 chiffres a été envoyé à<br>
                    <strong>{{ $maskedEmail }}</strong>
                @elseif($method === 'sms' && isset($maskedPhone))
                    Un code à 6 chiffres a été envoyé au<br>
                    <strong>{{ $maskedPhone }}</strong>
                @else
                    Un code de vérification vous a été envoyé.
                @endif
            </p>

            {{-- Bouton modifier l'adresse --}}
            <button type="button" class="btn-edit-contact" id="toggleEditBtn" onclick="toggleEditForm()">
                @if($method === 'email')
                    ✏️ Modifier l'adresse email
                @else
                    ✏️ Modifier le numéro de téléphone
                @endif
            </button>
        </div>

        {{-- Formulaire de modification email/téléphone (caché par défaut) --}}
        <div id="editContactForm" class="edit-contact-box" style="display:none;">
            <div class="edit-contact-header">
                @if($method === 'email')
                    <strong>Corriger l'adresse email</strong>
                @else
                    <strong>Corriger le numéro de téléphone</strong>
                @endif
            </div>

            <form method="POST" action="{{ route('verification.update-contact') }}" id="updateContactForm">
                @csrf
                <input type="hidden" name="method" value="{{ $method }}">

                @if($method === 'email')
                    <div class="form-group">
                        <label class="form-label">Nouvelle adresse email *</label>
                        <input type="email"
                               name="new_email"
                               class="form-input"
                               placeholder="exemple@email.com"
                               required
                               autocomplete="email">
                        @error('new_email')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                @else
                    <div class="form-group">
                        <label class="form-label">Nouveau numéro de téléphone *</label>
                        <input type="tel"
                               name="new_phone"
                               class="form-input"
                               placeholder="Ex: 97000000"
                               required
                               inputmode="numeric"
                               pattern="[0-9+\s]*">
                        @error('new_phone')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                @endif

                <div class="edit-contact-actions">
                    <button type="submit" class="btn btn-primary btn-sm">
                        ✅ Valider et renvoyer le code
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="toggleEditForm()">
                        Annuler
                    </button>
                </div>
            </form>
        </div>

        @if(session('success'))
            <div class="alert alert-success mb-4">{{ session('success') }}</div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning mb-4">{{ session('warning') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-error mb-4">{{ session('error') }}</div>
        @endif

        @if(session('debug_verification_code') && app()->environment('local'))
            <div class="alert alert-info mb-4 text-center">
                <strong>🛠️ Mode développement</strong><br>
                Code : <code style="font-size: 1.25rem; font-weight: bold;">{{ session('debug_verification_code') }}</code>
            </div>
        @endif

        <form method="POST" action="{{ route('verification.verify') }}" id="verifyForm">
            @csrf

            <div class="form-group">
                <label class="form-label">Code de vérification *</label>
                <input type="text"
                       name="code"
                       id="codeInput"
                       class="form-input code-input"
                       placeholder="000000"
                       maxlength="6"
                       required
                       autocomplete="one-time-code"
                       inputmode="numeric"
                       pattern="[0-9]*">
                @error('code')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" id="verifyBtn">
                Vérifier mon compte
            </button>
        </form>

        <div class="verification-footer">
            <p class="text-sm text-muted mb-3">
                Vous n'avez pas reçu le code ?
            </p>

            <div class="verification-actions">
                <form method="POST" action="{{ route('verification.resend') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-link">Renvoyer</button>
                </form>

                <span class="text-muted">|</span>

                <form method="POST" action="{{ route('verification.change') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-link">
                        {{ $method === 'email' ? 'Par SMS' : 'Par email' }}
                    </button>
                </form>
            </div>

            <div class="mt-4 pt-4 border-t">
                <a href="{{ route('login') }}" class="text-sm text-muted hover:text-primary">
                    ← Retour à la connexion
                </a>
            </div>
        </div>
    @endif
@endsection

@section('styles')
<style>
    .verification-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 1rem;
        background: #eff6ff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
    }

    .code-input {
        text-align: center;
        font-size: 1.5rem;
        font-weight: 600;
        letter-spacing: 0.5rem;
        padding: 1rem;
    }

    .verification-footer {
        text-align: center;
        margin-top: 1.5rem;
    }

    .verification-actions {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .btn-link {
        background: none;
        border: none;
        color: var(--primary);
        text-decoration: underline;
        cursor: pointer;
        font-size: 0.875rem;
        padding: 0;
    }

    /* Bouton modifier contact */
    .btn-edit-contact {
        background: none;
        border: 1px dashed var(--primary, #3b82f6);
        color: var(--primary, #3b82f6);
        border-radius: 6px;
        padding: 0.35rem 0.85rem;
        font-size: 0.8rem;
        cursor: pointer;
        margin-top: 0.5rem;
        transition: background 0.2s, color 0.2s;
    }
    .btn-edit-contact:hover {
        background: var(--primary, #3b82f6);
        color: #fff;
    }

    /* Box modification contact */
    .edit-contact-box {
        background: #f8faff;
        border: 1px solid #c7d8f8;
        border-radius: 10px;
        padding: 1.25rem;
        margin-bottom: 1.25rem;
        animation: slideDown 0.25s ease;
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-8px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .edit-contact-header {
        font-size: 0.9rem;
        margin-bottom: 0.75rem;
        color: #1e3a5f;
    }

    .edit-contact-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 0.75rem;
        flex-wrap: wrap;
    }

    .btn-sm {
        padding: 0.4rem 0.9rem;
        font-size: 0.85rem;
    }

    .btn-secondary {
        background: #e5e7eb;
        color: #374151;
        border: none;
        border-radius: 6px;
        cursor: pointer;
    }
    .btn-secondary:hover {
        background: #d1d5db;
    }

    .d-inline { display: inline-block; }
    .border-t  { border-top: 1px solid var(--border); }
    .mt-4 { margin-top: 1rem; }
    .pt-4 { padding-top: 1rem; }
</style>
@endsection

@section('scripts')
<script>
    const codeInput = document.getElementById('codeInput');
    const verifyBtn = document.getElementById('verifyBtn');

    function toggleEditForm() {
        const form = document.getElementById('editContactForm');
        const btn  = document.getElementById('toggleEditBtn');
        const visible = form.style.display !== 'none';
        form.style.display = visible ? 'none' : 'block';
        btn.textContent = visible
            ? ('{{ $method }}' === 'email' ? 'Modifier l\'adresse email' : ' Modifier le numéro de téléphone')
            : '✖ Annuler la modification';
    }

    if (codeInput) {
        codeInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        codeInput.focus();
    }

    document.getElementById('verifyForm')?.addEventListener('submit', function() {
        verifyBtn.disabled = true;
        verifyBtn.textContent = 'Vérification...';
    });
</script>
@endsection
