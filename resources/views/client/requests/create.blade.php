@extends('layouts.app')

@section('title', 'Nouvelle demande de financement')
@section('header-title', 'Nouvelle demande')

@section('header-action')
<a href="{{ route('client.requests.index') }}" class="btn-back">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
    </svg>
</a>
@endsection

@section('content')

<div class="request-create">

    {{-- ÉTAPE 1 : Liste des financements --}}
    <div class="card" id="step-selection">
        <div class="card-header">
            <h2 class="section-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                1. Choisissez votre type de financement
            </h2>
        </div>

        <p class="text-muted mb-4">Sélectionnez l'offre qui correspond à vos besoins</p>

        <div class="financement-list">
            @forelse($availableTypes as $financement)
            <div class="financement-card {{ $preselectedType && $preselectedType->id == $financement->id ? 'selected' : '' }}"
                onclick="selectFinancement({{ $financement->id }})"
                data-id="{{ $financement->id }}"
                data-name="{{ $financement->name }}"
                data-variable="{{ $financement->is_variable_amount ? '1' : '0' }}"
                data-max-daily="{{ $financement->max_daily_amount }}"
                data-daily-gain="{{ $financement->daily_gain }}"
                data-amount="{{ $financement->amount }}"
                data-duration="{{ $financement->duration_months }}"
                data-reg-fee="{{ $financement->registration_fee }}">

                <div class="financement-radio">
                    <div class="radio-circle {{ $preselectedType && $preselectedType->id == $financement->id ? 'checked' : '' }}">
                        @if($preselectedType && $preselectedType->id == $financement->id)
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="12" height="12">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                        @endif
                    </div>
                </div>

                <div class="financement-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <div class="financement-body">
                    <h3 class="financement-name">{{ $financement->name }}</h3>
                    <p class="financement-desc">{{ Str::limit($financement->description, 100) }}</p>

                    <div class="financement-tags">
                        @if($financement->is_variable_amount && $financement->max_daily_amount)
                        <span class="tag tag-primary">
                            Jusqu'à {{ number_format($financement->max_daily_amount, 0, ',', ' ') }} FCFA/jour
                        </span>
                        <span class="tag">Montant libre</span>
                        @elseif($financement->daily_gain && $financement->amount)
                        <span class="tag tag-success">
                            {{ number_format($financement->daily_gain, 0, ',', ' ') }} FCFA/jour
                        </span>
                        <span class="tag">
                            Total {{ number_format($financement->amount, 0, ',', ' ') }} FCFA
                        </span>
                        @endif

                        @if($financement->duration_months)
                        <span class="tag">{{ $financement->duration_months }} mois</span>
                        @endif

                        <span class="tag">
                            Frais {{ number_format($financement->registration_fee, 0, ',', ' ') }} FCFA
                        </span>
                    </div>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <div class="empty-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="48" height="48">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3>Aucun financement disponible</h3>
                <p>Aucune offre ne correspond à votre profil actuellement.</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- ÉTAPE 2 : Formulaire --}}
    <div class="card" id="step-form" style="{{ $preselectedType ? '' : 'display: none;' }}">
        <div class="card-header">
            <h2 class="section-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                2. Détails de votre demande
            </h2>
        </div>

        <form id="requestForm" class="request-form" onsubmit="return false;">
            @csrf
            <input type="hidden" name="typefinancement_id" id="selected_type_id"
                value="{{ $preselectedType ? $preselectedType->id : '' }}">

            {{-- Résumé du financement --}}
            <div class="selected-financement" id="selected-summary">
                @if($preselectedType)
                <div class="sf-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="sf-info">
                    <span class="sf-label">Financement sélectionné</span>
                    <span class="sf-name">{{ $preselectedType->name }}</span>
                </div>
                @endif
            </div>

            {{-- MONTANT --}}
            <div class="form-group" id="amount-section">
                <label class="form-label" id="amount-label">
                    @if($preselectedType && $preselectedType->is_variable_amount)
                    Montant quotidien souhaité
                    <span class="text-muted">(Max {{ number_format($preselectedType->max_daily_amount, 0, ',', ' ') }} FCFA/jour)</span>
                    @elseif($preselectedType)
                    Montant du financement
                    @else
                    Montant
                    @endif
                </label>

                <div id="variable-amount-input"
                    style="{{ $preselectedType && $preselectedType->is_variable_amount ? '' : 'display: none;' }}">
                    <div class="amount-input-wrapper">
                        <input type="number" name="amount_requested" id="amount_requested" class="form-control"
                            placeholder="Ex: 50000" min="1000" step="1000" value="{{ old('amount_requested') }}">
                        <span class="amount-currency">FCFA/jour</span>
                    </div>

                    <div class="amount-preview">
                        <div class="ap-row">
                            <span>Montant quotidien:</span>
                            <strong id="daily-display">0 FCFA</strong>
                        </div>
                        <div class="ap-row">
                            <span>Durée:</span>
                            <span id="duration-display">-</span>
                        </div>
                        <div class="ap-row ap-total">
                            <span>Total estimé:</span>
                            <strong id="total-estimated">0 FCFA</strong>
                        </div>
                    </div>
                </div>

                <div id="fixed-amount-display"
                    style="{{ $preselectedType && !$preselectedType->is_variable_amount ? '' : 'display: none;' }}">
                    <div class="fixed-amount">
                        <span class="fa-value" id="fixed-amount-value">
                            {{ $preselectedType ? number_format($preselectedType->amount, 0, ',', ' ') . ' FCFA' : '-' }}
                        </span>
                        <span class="fa-detail" id="fixed-amount-detail">
                            @if($preselectedType && !$preselectedType->is_variable_amount)
                            {{ number_format($preselectedType->daily_gain, 0, ',', ' ') }} FCFA × {{ $preselectedType->duration_months * 30 }} jours
                            @endif
                        </span>
                    </div>
                    <input type="hidden" name="amount_requested" id="fixed_amount_input"
                        value="{{ $preselectedType && !$preselectedType->is_variable_amount ? $preselectedType->amount : '' }}">
                </div>
            </div>

            {{-- DURÉE --}}
            <div class="form-group">
                <label class="form-label">Durée du financement</label>
                <div class="fixed-duration">
                    <span class="fd-value" id="duration-value">
                        {{ $preselectedType ? $preselectedType->duration_months . ' mois' : '-' }}
                    </span>
                    <span class="fd-detail" id="duration-detail">
                        {{ $preselectedType ? 'Soit ' . ($preselectedType->duration_months * 30) . ' jours' : '' }}
                    </span>
                </div>
                <input type="hidden" name="duration" id="duration_input"
                    value="{{ $preselectedType ? $preselectedType->duration_months : '' }}">
            </div>

            {{-- TITRE --}}
            <div class="form-group">
                <label class="form-label" for="title">Titre de la demande <span class="text-danger">*</span></label>
                <input type="text" name="title" id="title" class="form-control"
                    placeholder="Ex: Financement pour mon projet agricole" value="{{ old('title') }}" maxlength="100"
                    required>
            </div>

            {{-- DESCRIPTION --}}
            <div class="form-group">
                <label class="form-label" for="description">Description du projet <span class="text-muted">(optionnel)</span></label>
                <textarea name="description" id="description" class="form-control" rows="3" maxlength="500"
                    placeholder="Décrivez brièvement l'objet de votre demande...">{{ old('description') }}</textarea>
                <small class="char-count"><span id="desc-count">0</span>/500</small>
            </div>

            {{-- RÉCAPITULATIF FRAIS --}}
            <div class="fees-summary">
                <h4 class="fs-title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a1 1 0 11-2 0 1 1 0 012 0z" />
                    </svg>
                    Frais d'inscription à payer
                </h4>
                <div class="fs-row">
                    <span>Frais d'inscription</span>
                    <span id="reg-fee-display">
                        {{ $preselectedType ? number_format($preselectedType->registration_fee, 0, ',', ' ') . ' FCFA' : '-' }}
                    </span>
                </div>
                <div class="fs-row fs-total">
                    <span>Total à payer maintenant</span>
                    <span class="fs-total-value" id="total-fee-display">
                        {{ $preselectedType ? number_format($preselectedType->registration_fee, 0, ',', ' ') . ' FCFA' : '-' }}
                    </span>
                </div>
            </div>

            {{-- ZONE DE PAIEMENT --}}
            <div id="payment-section" style="display: none;">
                <div class="payment-loading" id="payment-loading">
                    <div class="spinner"></div>
                    <p>Préparation du paiement...</p>
                </div>
                <div id="kkiapay-widget-container" style="display: none;"></div>
                <div id="payment-polling" style="display: none; text-align: center; padding: 2rem;">
                    <div class="spinner"></div>
                    <p>Confirmation du paiement en cours...</p>
                    <small class="text-muted">Veuillez patienter, cela peut prendre quelques secondes</small>
                </div>
            </div>

            {{-- ACTIONS --}}
            <div class="form-actions" id="form-actions">
                <button type="button" class="btn btn-primary btn-block btn-lg" id="submitBtn" onclick="preparePayment()"
                    {{ $preselectedType ? '' : 'disabled' }}>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a1 1 0 11-2 0 1 1 0 012 0z" />
                    </svg>
                    Procéder au paiement
                </button>
                <p class="form-note">
                    Vous serez redirigé vers Kkiapay pour effectuer le paiement sécurisé.
                </p>
            </div>
        </form>
    </div>

</div>

@endsection

@section('scripts')
<script src="https://cdn.kkiapay.me/k.js"></script>

<script>
const financements = @json($availableTypes->keyBy('id'));
let currentSelection = {{ $preselectedType ? $preselectedType->id : 'null' }};
let currentFundingRequestId = null;
let currentTransaction = null;
let isProcessing = false;

document.addEventListener('DOMContentLoaded', function () {
    setupEventListeners();

    @if($preselectedType)
        updateFormForSelection({{ $preselectedType->id }});
    @endif

    // Attacher listeners Kkiapay
    if (typeof addSuccessListener === 'function') {
        addSuccessListener(onKkiapaySuccess);
        addFailedListener(onKkiapayFailed);
    }
});

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content
        || document.querySelector('input[name="_token"]')?.value;
}

function setupEventListeners() {
    // Montant variable
    const amountInput = document.getElementById('amount_requested');
    if (amountInput) {
        amountInput.addEventListener('input', calculateTotals);
    }

    // Compteur description
    const descInput = document.getElementById('description');
    if (descInput) {
        descInput.addEventListener('input', function() {
            document.getElementById('desc-count').textContent = this.value.length;
        });
    }
}

function calculateTotals() {
    const daily = parseInt(this.value) || 0;
    const duration = parseInt(document.getElementById('duration_input')?.value) || 6;
    const total = daily * duration * 30;

    document.getElementById('daily-display').textContent = daily.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('total-estimated').textContent = total.toLocaleString('fr-FR') + ' FCFA';
}

function selectFinancement(id) {
    currentSelection = id;
    updateFormForSelection(id);
}

function updateFormForSelection(id) {
    const fin = financements[id];
    if (!fin) return;

    // UI sélection
    document.querySelectorAll('.financement-card').forEach(card => {
        card.classList.remove('selected');
        const r = card.querySelector('.radio-circle');
        if (r) { r.classList.remove('checked'); r.innerHTML = ''; }
    });

    const selectedCard = document.querySelector(`[data-id="${id}"]`);
    if (selectedCard) {
        selectedCard.classList.add('selected');
        const r = selectedCard.querySelector('.radio-circle');
        if (r) {
            r.classList.add('checked');
            r.innerHTML = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="12" height="12"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>';
        }
    }

    // Mettre à jour champs
    document.getElementById('selected_type_id').value = id;
    document.getElementById('step-form').style.display = 'block';

    // Résumé
    updateSummary(fin);

    // Montant
    updateAmountSection(fin);

    // Durée
    updateDurationSection(fin);

    // Frais
    updateFeesSection(fin);

    document.getElementById('submitBtn').disabled = false;
}

function updateSummary(fin) {
    document.getElementById('selected-summary').innerHTML = `
        <div class="sf-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="sf-info">
            <span class="sf-label">Financement sélectionné</span>
            <span class="sf-name">${escapeHtml(fin.name)}</span>
        </div>`;
}

function updateAmountSection(fin) {
    const isVariable = fin.is_variable_amount == 1;
    const amountLabel = document.getElementById('amount-label');
    const varInput = document.getElementById('variable-amount-input');
    const fixedDisplay = document.getElementById('fixed-amount-display');

    if (isVariable) {
        amountLabel.innerHTML = `Montant quotidien souhaité <span class="text-muted">(Max ${parseInt(fin.max_daily_amount).toLocaleString('fr-FR')} FCFA/jour)</span>`;
        varInput.style.display = 'block';
        fixedDisplay.style.display = 'none';

        const inp = document.getElementById('amount_requested');
        inp.max = fin.max_daily_amount;
        inp.value = '';
        inp.required = true;
    } else {
        amountLabel.innerHTML = 'Montant du financement';
        varInput.style.display = 'none';
        fixedDisplay.style.display = 'block';

        document.getElementById('fixed-amount-value').textContent = `${parseInt(fin.amount).toLocaleString('fr-FR')} FCFA`;
        document.getElementById('fixed-amount-detail').textContent = `${parseInt(fin.daily_gain).toLocaleString('fr-FR')} FCFA × ${fin.duration_months * 30} jours`;
        document.getElementById('fixed_amount_input').value = fin.amount;
        document.getElementById('amount_requested').required = false;
    }
}

function updateDurationSection(fin) {
    document.getElementById('duration-value').textContent = `${fin.duration_months} mois`;
    document.getElementById('duration-detail').textContent = `Soit ${fin.duration_months * 30} jours`;
    document.getElementById('duration_input').value = fin.duration_months;
    document.getElementById('duration-display').textContent = `${fin.duration_months} mois (${fin.duration_months * 30} jours)`;
}

function updateFeesSection(fin) {
    const regFee = parseInt(fin.registration_fee);
    const formatted = regFee.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('reg-fee-display').textContent = formatted;
    document.getElementById('total-fee-display').textContent = formatted;
}

async function preparePayment() {
    if (isProcessing) return;
    isProcessing = true;

    const csrfToken = getCsrfToken();
    const typeId = document.getElementById('selected_type_id')?.value;
    const fin = financements[typeId];

    // Validations
    if (!validateForm(fin)) {
        isProcessing = false;
        return;
    }

    try {
        showLoading();

        // Étape 1: Créer la demande
        const formData = new FormData(document.getElementById('requestForm'));
        const storeRes = await fetch('{{ route("client.requests.store") }}', {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });

        const storeData = await storeRes.json();
        if (!storeData.success) throw new Error(storeData.message);

        currentFundingRequestId = storeData.funding_request_id;

        // Étape 2: Initialiser paiement
        const initRes = await fetch(`/requests/${currentFundingRequestId}/payment/initialize`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        });

        const initData = await initRes.json();
        if (!initData.success) throw new Error(initData.message);

        currentTransaction = initData.transaction;

        // Étape 3: Ouvrir widget
        openKkiapayWidget({
            amount: initData.kkiapay_config.amount,
            key: initData.kkiapay_config.key,
            sandbox: initData.kkiapay_config.sandbox,
            data: initData.kkiapay_config.data,
            theme: '#2563eb',
            name: 'BHDM',
        });

    } catch (error) {
        console.error('Payment error:', error);
        alert(error.message || 'Erreur lors de la préparation du paiement');
        hideLoading();
        isProcessing = false;
    }
}

function validateForm(fin) {
    const csrfToken = getCsrfToken();
    if (!csrfToken) { alert('Erreur CSRF'); return false; }

    if (fin.is_variable_amount == 1) {
        const amount = parseInt(document.getElementById('amount_requested')?.value);
        if (!amount || amount < 1000) { alert('Montant minimum: 1 000 FCFA/jour'); return false; }
        if (amount > fin.max_daily_amount) { alert(`Montant maximum: ${fin.max_daily_amount} FCFA`); return false; }
    }

    const title = document.getElementById('title')?.value.trim();
    if (!title) { alert('Veuillez saisir un titre'); return false; }

    return true;
}

function showLoading() {
    document.getElementById('form-actions').style.display = 'none';
    document.getElementById('payment-section').style.display = 'block';
}

function hideLoading() {
    document.getElementById('form-actions').style.display = 'block';
    document.getElementById('payment-section').style.display = 'none';
}

async function onKkiapaySuccess(response) {
    console.log('=== KKIAPAY SUCCESS ===', response);

    const transactionId = response.transactionId;

    if (!currentFundingRequestId || !currentTransaction) {
        alert('Erreur interne. Référence: ' + transactionId);
        return;
    }

    // Afficher le loading
    const paymentSection = document.getElementById('payment-section');
    paymentSection.innerHTML = `
        <div class="payment-loading">
            <div class="spinner"></div>
            <p>Vérification du paiement...</p>
            <small id="verify-status">Connexion à Kkiapay...</small>
        </div>
    `;

    // Polling avec backoff exponentiel
    const maxAttempts = 20;
    let attempt = 0;
    
    while (attempt < maxAttempts) {
        attempt++;
        const delay = Math.min(1000 * Math.pow(1.2, attempt), 5000); // Backoff max 5s
        
        console.log(`🔄 Polling attempt ${attempt}/${maxAttempts}, delay: ${delay}ms`);
        
        try {
            const verifyRes = await fetch('/payment/verify', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    transactionId: transactionId,
                    funding_request_id: currentFundingRequestId,
                    internal_transaction_id: currentTransaction.transaction_id,
                }),
            });

            const data = await verifyRes.json();
            console.log('Verify response:', data);

            // Mettre à jour le statut visuel
            const statusEl = document.getElementById('verify-status');
            if (statusEl) {
                statusEl.textContent = `Tentative ${attempt}/${maxAttempts}...`;
            }

            if (data.status === 'paid') {
                console.log('✅ Payment confirmed!');
                paymentSection.innerHTML = `
                    <div class="payment-success">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        <p>Paiement confirmé !</p>
                        <small>Redirection...</small>
                    </div>
                `;
                window.location.href = data.redirect_url;
                return;
            }
            
            if (data.status === 'failed') {
                alert('Paiement échoué: ' + (data.message || 'Erreur inconnue'));
                hideLoading();
                return;
            }

            // Attendre avant prochaine tentative
            await new Promise(resolve => setTimeout(resolve, delay));

        } catch (err) {
            console.error('Polling error:', err);
            await new Promise(resolve => setTimeout(resolve, 2000));
        }
    }

    // Timeout - mais ne pas abandonner, rediriger vers la page de statut
    console.warn('Max polling attempts reached');
    alert('Votre paiement est en cours de traitement. Vous serez notifié par email.');
    window.location.href = `/my-requests`; // Page liste des demandes
}

function onKkiapayFailed(response) {
    console.error('Kkiapay failed:', response);
    alert('Le paiement a été annulé ou a échoué.');
    hideLoading();
    isProcessing = false;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endsection

@section('styles')
<style>
    .request-create {
        max-width: 800px;
        margin: 0 auto;
    }

    .btn-back {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        background: var(--bg);
        border-radius: var(--radius-sm);
        color: var(--text);
        text-decoration: none;
        border: 1px solid var(--border);
        transition: all 0.2s;
    }

    .btn-back:hover {
        background: var(--border);
        color: var(--primary);
    }

    .section-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1rem;
        font-weight: 600;
        color: var(--text);
        margin: 0;
    }

    .financement-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .financement-card {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        background: var(--surface);
        border: 2px solid var(--border);
        border-radius: var(--radius);
        padding: 1.25rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .financement-card:hover {
        border-color: var(--primary);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.12);
        transform: translateY(-2px);
    }

    .financement-card.selected {
        border-color: var(--primary);
        background: #eff6ff;
    }

    .financement-radio {
        flex-shrink: 0;
    }

    .radio-circle {
        width: 20px;
        height: 20px;
        border: 2px solid var(--border);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .financement-card:hover .radio-circle,
    .radio-circle.checked {
        border-color: var(--primary);
        background: var(--primary);
        color: white;
    }

    .financement-icon {
        width: 46px;
        height: 46px;
        background: white;
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
        flex-shrink: 0;
    }

    .financement-body {
        flex: 1;
        min-width: 0;
    }

    .financement-name {
        font-size: 1rem;
        font-weight: 600;
        margin: 0 0 0.35rem;
        color: var(--text);
    }

    .financement-desc {
        font-size: 0.85rem;
        color: var(--text-muted);
        margin: 0 0 0.75rem;
        line-height: 1.5;
    }

    .financement-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
    }

    .tag {
        font-size: 0.7rem;
        font-weight: 500;
        background: var(--bg);
        color: var(--text-muted);
        border: 1px solid var(--border);
        padding: 0.25rem 0.6rem;
        border-radius: 9999px;
    }

    .tag-primary {
        background: #dbeafe;
        color: #1e40af;
        border-color: #3b82f6;
    }

    .tag-success {
        background: #dcfce7;
        color: #166534;
        border-color: #22c55e;
    }

    .selected-financement {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: var(--radius);
        padding: 1rem;
        margin-bottom: 1.5rem;
    }

    .sf-icon {
        width: 40px;
        height: 40px;
        background: white;
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
    }

    .sf-info {
        display: flex;
        flex-direction: column;
    }

    .sf-label {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .sf-name {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text);
    }

    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--text);
        margin-bottom: 0.5rem;
    }

    .form-label .text-muted {
        font-weight: 400;
        color: var(--text-muted);
    }

    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        background: var(--surface);
        color: var(--text);
        font-size: 0.9rem;
        transition: all 0.2s;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        outline: none;
    }

    .amount-input-wrapper {
        position: relative;
    }

    .amount-input-wrapper .form-control {
        padding-right: 5.5rem;
    }

    .amount-currency {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--text-muted);
    }

    .amount-preview {
        background: #f8fafc;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 0.875rem 1rem;
        margin-top: 0.75rem;
    }

    .ap-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.875rem;
        padding: 0.35rem 0;
    }

    .ap-row span:first-child {
        color: var(--text-muted);
    }

    .ap-row strong {
        color: var(--text);
        font-weight: 600;
    }

    .ap-total {
        border-top: 1px solid var(--border);
        margin-top: 0.5rem;
        padding-top: 0.5rem;
    }

    .ap-total strong {
        color: var(--primary);
        font-size: 1rem;
    }

    .fixed-amount {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        padding: 1rem;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: var(--radius);
    }

    .fa-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #166534;
    }

    .fa-detail {
        font-size: 0.8rem;
        color: #15803d;
    }

    .fixed-duration {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        padding: 0.75rem 1rem;
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: var(--radius);
    }

    .fd-value {
        font-size: 0.95rem;
        font-weight: 500;
        color: var(--text);
    }

    .fd-detail {
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    .char-count {
        display: block;
        text-align: right;
        font-size: 0.75rem;
        color: var(--text-muted);
        margin-top: 0.25rem;
    }

    .fees-summary {
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 1rem;
        margin: 1.5rem 0;
    }

    .fs-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text);
        margin: 0 0 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid var(--border);
    }

    .fs-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        font-size: 0.875rem;
    }

    .fs-row span:first-child {
        color: var(--text-muted);
    }

    .fs-row span:last-child {
        font-weight: 500;
        color: var(--text);
    }

    .fs-total {
        border-top: 1px solid var(--border);
        margin-top: 0.5rem;
        padding-top: 0.75rem;
    }

    .fs-total span:first-child {
        font-weight: 600;
        color: var(--text);
    }

    .fs-total-value {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--primary) !important;
    }

    .payment-loading, #payment-polling {
        text-align: center;
        padding: 2rem;
        color: var(--text-muted);
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 3px solid var(--border);
        border-top-color: var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 1rem;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    #kkiapay-widget-container {
        min-height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .form-actions {
        margin-top: 1.5rem;
    }

    .btn-block {
        width: 100%;
        justify-content: center;
    }

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .form-note {
        text-align: center;
        font-size: 0.75rem;
        color: var(--text-muted);
        margin-top: 0.75rem;
    }

    .empty-state {
        text-align: center;
        padding: 2rem;
    }

    .empty-icon {
        color: var(--text-muted);
        margin-bottom: 1rem;
    }

    .empty-state h3 {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text);
        margin: 0 0 0.5rem;
    }

    .empty-state p {
        font-size: 0.875rem;
        color: var(--text-muted);
        margin: 0 0 1rem;
    }

    #step-form {
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 640px) {
        .financement-card {
            padding: 1rem;
        }

        .financement-icon {
            width: 40px;
            height: 40px;
        }
    }
</style>
@endsection
