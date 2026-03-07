@extends('layouts.auth')

@section('title', 'Vérification du compte')

@section('content')
    @if(!isset($method))
        <div class="alert alert-error">
            Erreur de session. <a href="{{ url('/login') }}">Retour à la connexion</a>
        </div>
    @else
        <div class="verify-header">
            <div class="verify-icon">
                @if($method === 'email')
                    <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                @else
                    <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                @endif
            </div>

            <h2>Vérification du compte</h2>

            <p class="verify-desc">
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

            <button type="button" class="btn-edit" onclick="toggleEditForm()">
                @if($method === 'email')
                    Modifier l'adresse email
                @else
                    Modifier le numéro de téléphone
                @endif
            </button>
        </div>

        {{-- Formulaire de modification (caché par défaut) --}}
        <div id="editContactForm" class="edit-box" style="display:none;">
            <div class="edit-title">
                @if($method === 'email')
                    Corriger l'adresse email
                @else
                    Corriger le numéro de téléphone
                @endif
            </div>

            <form action="{{ url('/verification/update-contact') }}" method="post" id="updateContactForm">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="method" value="{{ $method }}">

                @if($method === 'email')
                    <div class="input-group">
                        <label for="new_email">Nouvelle adresse email <span class="required">*</span></label>
                        <input type="email" id="new_email" name="new_email" placeholder="exemple@email.com" required>
                    </div>
                @else
                    <div class="input-group">
                        <label for="new_phone">Nouveau numéro de téléphone <span class="required">*</span></label>
                        <input type="text" id="new_phone" name="new_phone" placeholder="Ex: 97000000" required>
                    </div>
                @endif

                <div class="edit-actions">
                    <button type="submit" class="btn btn-primary btn-sm">Valider et renvoyer</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="toggleEditForm()">Annuler</button>
                </div>
            </form>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @if(session('debug_verification_code') && app()->environment('local'))
            <div class="alert alert-info text-center">
                <strong>Mode développement</strong><br>
                Code: <code>{{ session('debug_verification_code') }}</code>
            </div>
        @endif

        {{-- Formulaire de vérification --}}
        <form action="{{ url('/verification') }}" method="post" id="verifyForm">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            <div class="input-group">
                <label for="code">Code de vérification <span class="required">*</span></label>
                <input type="text"
                       id="code"
                       name="code"
                       class="code-input"
                       placeholder="000000"
                       maxlength="6"
                       required
                       autocomplete="off">
            </div>

            <button type="submit" class="btn-submit" id="verifyBtn">
                Vérifier mon compte
            </button>
        </form>

        <div class="verify-footer">
            <p>Vous n'avez pas reçu le code ?</p>

            <div class="verify-actions">
                <form action="{{ url('/verification/resend') }}" method="post" class="inline-form">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <button type="submit" class="btn-link">Renvoyer le code</button>
                </form>

                <span class="separator">|</span>

                <form action="{{ url('/verification/change') }}" method="post" class="inline-form">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <button type="submit" class="btn-link">
                        {{ $method === 'email' ? 'Recevoir par SMS' : 'Recevoir par email' }}
                    </button>
                </form>
            </div>

            <div class="back-link">
                <a href="{{ url('/login') }}">Retour à la connexion</a>
            </div>
        </div>
    @endif
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

    .alert-warning {
        background: #fef3c7;
        border: 1px solid #fde68a;
        color: #92400e;
    }

    .alert-info {
        background: #dbeafe;
        border: 1px solid #bfdbfe;
        color: #1e40af;
    }

    .alert-info code {
        font-size: 18px;
        font-weight: 600;
        background: #ffffff;
        padding: 4px 12px;
        border-radius: 4px;
    }

    .verify-header {
        text-align: center;
        margin-bottom: 24px;
    }

    .verify-icon {
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

    .verify-header h2 {
        font-size: 20px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 12px;
    }

    .verify-desc {
        font-size: 14px;
        color: #64748b;
        line-height: 1.6;
        margin-bottom: 12px;
    }

    .verify-desc strong {
        color: #1e293b;
        font-weight: 600;
    }

    .btn-edit {
        background: none;
        border: 1px dashed #1e3a5f;
        color: #1e3a5f;
        padding: 6px 12px;
        font-size: 12px;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-edit:hover {
        background: #1e3a5f;
        color: #ffffff;
    }

    .edit-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 20px;
    }

    .edit-title {
        font-size: 14px;
        font-weight: 600;
        color: #1e3a5f;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid #e2e8f0;
    }

    .input-group {
        margin-bottom: 12px;
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

    .edit-actions {
        display: flex;
        gap: 8px;
        margin-top: 12px;
    }

    .btn {
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary {
        background: #1e3a5f;
        color: #ffffff;
    }

    .btn-primary:hover {
        background: #2c5282;
    }

    .btn-secondary {
        background: #e2e8f0;
        color: #475569;
    }

    .btn-secondary:hover {
        background: #cbd5e1;
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 12px;
    }

    .code-input {
        text-align: center;
        font-size: 24px;
        font-weight: 600;
        letter-spacing: 8px;
        padding: 16px;
        width: 100%;
        border: 2px solid #e2e8f0;
        border-radius: 4px;
    }

    .code-input:focus {
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
        margin-top: 8px;
    }

    .btn-submit:hover {
        background: #2c5282;
    }

    .btn-submit:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .verify-footer {
        text-align: center;
        margin-top: 24px;
        padding-top: 24px;
        border-top: 1px solid #e2e8f0;
    }

    .verify-footer p {
        font-size: 14px;
        color: #64748b;
        margin-bottom: 12px;
    }

    .verify-actions {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .inline-form {
        display: inline;
    }

    .btn-link {
        background: none;
        border: none;
        color: #1e3a5f;
        font-size: 14px;
        font-weight: 500;
        text-decoration: underline;
        cursor: pointer;
        padding: 0;
    }

    .btn-link:hover {
        color: #2c5282;
    }

    .separator {
        color: #cbd5e1;
    }

    .back-link {
        margin-top: 20px;
        padding-top: 16px;
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

    .text-center {
        text-align: center;
    }
</style>
@endsection

@section('scripts')
<script>
    function toggleEditForm() {
        var form = document.getElementById('editContactForm');
        var btn = document.querySelector('.btn-edit');

        if (form.style.display === 'none') {
            form.style.display = 'block';
            btn.textContent = 'Annuler la modification';
        } else {
            form.style.display = 'none';
            btn.textContent = '{{ $method === "email" ? "Modifier l\'adresse email" : "Modifier le numéro de téléphone" }}';
        }
    }

    // N'autoriser que les chiffres dans le code
    var codeInput = document.getElementById('code');
    if (codeInput) {
        codeInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        codeInput.focus();
    }

    // Désactiver le bouton pendant la soumission
    var verifyForm = document.getElementById('verifyForm');
    var verifyBtn = document.getElementById('verifyBtn');

    if (verifyForm) {
        verifyForm.addEventListener('submit', function() {
            verifyBtn.disabled = true;
            verifyBtn.textContent = 'Vérification en cours...';
        });
    }
</script>
@endsection
