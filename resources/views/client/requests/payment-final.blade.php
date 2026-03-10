@extends('layouts.app')

@section('title', 'Frais de dossier - ' . $fundingRequest->request_number)
@section('header-title', 'Paiement des frais de dossier')

@section('content')

<div class="payment-container">

    @if($fundingRequest->status === 'approved' && !($fundingRequest->final_fee_paid ?? false))

        {{-- Progression --}}
        <div class="payment-header">
            <div class="step-indicator">
                <div class="step completed">
                    <span class="step-number">✓</span>
                    <span class="step-label">Soumission</span>
                </div>
                <div class="step-line active"></div>
                <div class="step completed">
                    <span class="step-number">✓</span>
                    <span class="step-label">Approuvée</span>
                </div>
                <div class="step-line active"></div>
                <div class="step active">
                    <span class="step-number">3</span>
                    <span class="step-label">Frais dossier</span>
                </div>
                <div class="step-line"></div>
                <div class="step">
                    <span class="step-number">4</span>
                    <span class="step-label">Financement</span>
                </div>
            </div>
        </div>

        {{-- Badge approbation --}}
        <div class="approval-badge">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Demande approuvée — Financement de
                <strong>{{ number_format($fees['approved'], 0, ',', ' ') }} FCFA</strong>
            </span>
        </div>

        {{-- Carte principale --}}
        <div class="payment-card" id="paymentCard">

            {{-- Référence --}}
            <div class="payment-reference">
                <span class="ref-label">Demande</span>
                <span class="ref-value">{{ $fundingRequest->request_number }}</span>
            </div>

            {{-- Montant à payer --}}
            <div class="payment-amount-section">
                <span class="amount-label">Frais de dossier à régler</span>
                <div class="amount-display">
                    <span class="currency">FCFA</span>
                    <span class="amount" id="amountDisplay">{{ number_format($fees['current'], 0, ',', ' ') }}</span>
                </div>
                <input type="hidden" id="feeAmount" value="{{ $fees['current'] }}">
            </div>

            {{-- Info montant net reçu --}}
            <div class="info-frais-finals info-net">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>
                    Après règlement, vous recevrez
                    <strong>{{ number_format($fees['net_amount'], 0, ',', ' ') }} FCFA</strong>
                    sur votre compte
                </span>
            </div>

            {{-- Séparateur --}}
            <div class="payment-divider"></div>

            {{-- Actions --}}
            <div class="payment-actions" id="paymentActions">
                <button type="button" class="btn-pay" id="btnPay" onclick="initierPaiement()">
                    <span class="btn-content">
                        <span class="btn-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a1 1 0 11-2 0 1 1 0 012 0z" />
                            </svg>
                        </span>
                        <span class="btn-text">Payer <span id="btnAmount">{{ number_format($fees['current'], 0, ',', ' ') }}</span> FCFA</span>
                    </span>
                    <span class="btn-loader" style="display: none;">
                        <span class="btn-spinner"></span>
                    </span>
                </button>

                <a href="{{ route('client.requests.show', $fundingRequest) }}" class="btn-cancel-link">
                    Retour à ma demande
                </a>
            </div>

            {{-- Sécurité --}}
            <div class="payment-security">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span>Paiement sécurisé par Kkiapay</span>
            </div>
        </div>

        {{-- Loading Overlay --}}
        <div id="payment-processing" class="loading-overlay" style="display: none;">
            <div class="loading-content">
                <div class="spinner"></div>
                <p>Vérification du paiement...</p>
                <small>Ne fermez pas cette page</small>
            </div>
        </div>

    @else

        {{-- État déjà payé --}}
        <div class="status-paid">
            <div class="paid-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="48" height="48">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h2>Frais de dossier réglés</h2>
            <p>Votre financement va être versé très prochainement.</p>
            <div class="paid-actions">
                <a href="{{ route('client.requests.show', $fundingRequest) }}" class="btn-primary">
                    Voir ma demande
                </a>
            </div>
        </div>

    @endif

</div>

@endsection

@section('scripts')
<script src="https://cdn.kkiapay.me/k.js"></script>
<script>
    let isProcessing = false;
    let kkiapayData  = null;

    const FUNDING_REQUEST_ID = {{ $fundingRequest->id }};
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof addSuccessListener === 'function') addSuccessListener(onKkiapaySuccess);
        if (typeof addFailedListener  === 'function') addFailedListener(onKkiapayFailed);
        if (typeof addCloseListener   === 'function') addCloseListener(onKkiapayClose);
    });

    /**
     * ÉTAPE 1 — Initialize la transaction pending pour frais finals
     */
    async function initierPaiement() {
        if (isProcessing) return;

        const btn = document.getElementById('btnPay');
        isProcessing = true;
        btn.disabled = true;
        btn.querySelector('.btn-content').style.display = 'none';
        btn.querySelector('.btn-loader').style.display = 'flex';

        try {
            const resp = await fetch('{{ route('client.payment.initialize', $fundingRequest) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ type: 'final_fee' }),
            });

            const data = await resp.json();

            if (!data.success) {
                throw new Error(data.message || 'Échec de l\'initialisation');
            }

            kkiapayData = data;
            const cfg   = data.kkiapay_config;

            openKkiapayWidget({
                amount:   cfg.amount,
                key:      cfg.key,
                sandbox:  cfg.sandbox,
                data:     cfg.data,
                theme:    '#10b981',   // vert pour distinguer du paiement initial
                position: 'center',
            });

        } catch (error) {
            console.error('Erreur initialisation:', error);
            resetButton();
            alert('Erreur lors de l\'ouverture du paiement : ' + error.message);
        }
    }

    function resetButton() {
        isProcessing = false;
        const btn = document.getElementById('btnPay');
        btn.disabled = false;
        btn.querySelector('.btn-content').style.display = 'flex';
        btn.querySelector('.btn-loader').style.display = 'none';
    }

    /**
     * ÉTAPE 2 — SDK succès → vérification serveur
     */
    function onKkiapaySuccess(response) {
        console.log('Kkiapay success (final):', response);
        document.getElementById('payment-processing').style.display = 'flex';

        if (!kkiapayData) {
            alert('Erreur interne : données de transaction manquantes. Contactez le support.');
            document.getElementById('payment-processing').style.display = 'none';
            resetButton();
            return;
        }

        const internalTxId = kkiapayData.transaction?.transaction_id;
        verifyPayment(response.transactionId, internalTxId, FUNDING_REQUEST_ID);
    }

    function onKkiapayFailed(response) {
        console.log('Kkiapay failed:', response);
        resetButton();
        if (response && response.transactionId) {
            alert('Le paiement a été annulé ou a échoué. Vous pouvez réessayer.');
        }
    }

    function onKkiapayClose() {
        if (isProcessing) resetButton();
    }

    /**
     * ÉTAPE 3 — Vérification via la route dédiée aux frais finals
     */
    async function verifyPayment(kkiapayTransactionId, internalTransactionId, fundingRequestId) {
        try {
            const resp = await fetch('{{ route('client.requests.payment.final.verify', $fundingRequest) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    transactionId:           kkiapayTransactionId,
                    internal_transaction_id: internalTransactionId,
                    funding_request_id:      fundingRequestId,
                }),
            });

            const data = await resp.json();

            if (data.success) {
                window.location.href = data.redirect_url
                    || '{{ route('client.requests.show', $fundingRequest) }}';
            } else if (data.status === 'pending') {
                // Webhook pas encore arrivé → réessayer
                setTimeout(() => verifyPayment(kkiapayTransactionId, internalTransactionId, fundingRequestId), 2000);
            } else {
                throw new Error(data.message || 'Erreur de vérification');
            }

        } catch (error) {
            console.error('Erreur vérification finale:', error);
            setTimeout(() => verifyPayment(kkiapayTransactionId, internalTransactionId, fundingRequestId), 3000);
        }
    }
</script>
@endsection

@section('styles')
<style>
    :root {
        --primary: #2563eb;
        --primary-dark: #1d4ed8;
        --success: #10b981;
        --success-dark: #059669;
        --danger: #ef4444;
        --surface: #ffffff;
        --background: #f8fafc;
        --text: #0f172a;
        --text-muted: #64748b;
        --border: #e2e8f0;
        --radius: 12px;
        --radius-sm: 8px;
        --shadow: 0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -1px rgba(0,0,0,.06);
    }

    .payment-container {
        max-width: 480px;
        margin: 0 auto;
        padding: 1.5rem 1rem;
    }

    /* Step Indicator */
    .payment-header { margin-bottom: 1.5rem; }

    .step-indicator {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
    }

    .step { display: flex; flex-direction: column; align-items: center; gap: .4rem; }

    .step-number {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .8rem;
        font-weight: 700;
        background: var(--border);
        color: var(--text-muted);
        transition: all .3s;
    }

    .step.completed .step-number { background: var(--success); color: white; }

    .step.active .step-number {
        background: var(--success);
        color: white;
        box-shadow: 0 0 0 4px rgba(16,185,129,.2);
        animation: pulse-green 2s infinite;
    }

    @keyframes pulse-green {
        0%,100% { box-shadow: 0 0 0 4px rgba(16,185,129,.2); }
        50%      { box-shadow: 0 0 0 8px rgba(16,185,129,.1); }
    }

    .step-label { font-size: .7rem; font-weight: 500; color: var(--text-muted); }
    .step.completed .step-label,
    .step.active   .step-label { color: var(--text); font-weight: 600; }

    .step-line { width: 32px; height: 2px; background: var(--border); border-radius: 1px; transition: all .3s; }
    .step-line.active { background: var(--success); }

    /* Approval Badge */
    .approval-badge {
        display: flex;
        align-items: center;
        gap: .75rem;
        background: #f0fdf4;
        border: 1px solid #86efac;
        border-radius: var(--radius-sm);
        padding: .875rem 1rem;
        margin-bottom: 1.25rem;
        font-size: .875rem;
        color: #166534;
    }

    .approval-badge svg { flex-shrink: 0; color: var(--success); }

    /* Payment Card */
    .payment-card {
        background: var(--surface);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 2rem;
        border: 1px solid var(--border);
    }

    .payment-reference {
        text-align: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px dashed var(--border);
    }

    .ref-label {
        display: block;
        font-size: .75rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: .25rem;
    }

    .ref-value {
        font-family: 'SF Mono', monospace;
        font-size: 1rem;
        color: var(--text);
        font-weight: 700;
    }

    .payment-amount-section { text-align: center; margin-bottom: 1rem; }
    .amount-label { display: block; font-size: .875rem; color: var(--text-muted); margin-bottom: .75rem; }

    .amount-display {
        display: flex;
        align-items: baseline;
        justify-content: center;
        gap: .5rem;
    }

    .currency { font-size: 1.5rem; font-weight: 600; color: var(--text-muted); }

    .amount {
        font-size: 3.5rem;
        font-weight: 800;
        color: var(--success);   /* vert pour frais finals */
        line-height: 1;
        letter-spacing: -.02em;
    }

    .info-frais-finals {
        display: flex;
        align-items: flex-start;
        gap: .75rem;
        background: #f0fdf4;
        border: 1px solid #86efac;
        border-radius: var(--radius-sm);
        padding: 1rem;
        margin-top: 1.5rem;
        font-size: .875rem;
        color: #166534;
    }

    .info-frais-finals svg { flex-shrink: 0; margin-top: .125rem; }

    .payment-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, var(--border), transparent);
        margin: 1.5rem 0;
    }

    /* Actions */
    .payment-actions { display: flex; flex-direction: column; gap: .75rem; }

    .btn-pay {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem 1.5rem;
        background: linear-gradient(135deg, var(--success), var(--success-dark));
        color: white;
        border: none;
        border-radius: var(--radius);
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all .2s;
        box-shadow: 0 4px 14px rgba(16,185,129,.35);
        position: relative;
        overflow: hidden;
    }

    .btn-pay:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16,185,129,.45);
    }

    .btn-pay:disabled { opacity: .7; cursor: not-allowed; }

    .btn-content { display: flex; align-items: center; gap: .75rem; }

    .btn-loader {
        display: flex;
        align-items: center;
        justify-content: center;
        position: absolute;
        inset: 0;
    }

    .btn-spinner {
        width: 24px;
        height: 24px;
        border: 3px solid rgba(255,255,255,.3);
        border-top-color: white;
        border-radius: 50%;
        animation: spin .8s linear infinite;
    }

    @keyframes spin { to { transform: rotate(360deg); } }

    .btn-cancel-link {
        display: block;
        width: 100%;
        padding: .875rem;
        text-align: center;
        background: transparent;
        color: var(--text-muted);
        border: 1.5px solid var(--border);
        border-radius: var(--radius);
        font-size: .875rem;
        font-weight: 500;
        text-decoration: none;
        transition: all .2s;
    }

    .btn-cancel-link:hover { background: var(--background); color: var(--text); border-color: var(--text-muted); }

    .payment-security {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--border);
        font-size: .75rem;
        color: var(--text-muted);
    }

    .payment-security svg { color: var(--success); }

    /* Loading Overlay */
    .loading-overlay {
        position: fixed;
        inset: 0;
        background: rgba(255,255,255,.98);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        animation: fadeIn .3s ease;
    }

    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

    .loading-content { text-align: center; padding: 2rem; }

    .spinner {
        width: 56px;
        height: 56px;
        border: 4px solid var(--border);
        border-top-color: var(--success);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 1.5rem;
    }

    .loading-content p      { font-size: 1.125rem; font-weight: 600; color: var(--text); margin-bottom: .5rem; }
    .loading-content small  { font-size: .875rem; color: var(--text-muted); }

    /* Status Paid */
    .status-paid {
        text-align: center;
        padding: 3rem 1.5rem;
        background: var(--surface);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
    }

    .paid-icon {
        width: 88px;
        height: 88px;
        background: linear-gradient(135deg, #dcfce7, #bbf7d0);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--success);
        margin: 0 auto 1.5rem;
        box-shadow: 0 8px 24px rgba(16,185,129,.2);
    }

    .status-paid h2 { font-size: 1.5rem; font-weight: 700; color: var(--text); margin-bottom: .5rem; }
    .status-paid p  { color: var(--text-muted); margin-bottom: 2rem; }

    .paid-actions { display: flex; flex-direction: column; gap: .75rem; }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 1rem 1.5rem;
        background: linear-gradient(135deg, var(--success), var(--success-dark));
        color: white;
        border-radius: var(--radius);
        text-decoration: none;
        font-weight: 600;
        transition: all .2s;
        box-shadow: 0 4px 14px rgba(16,185,129,.35);
    }

    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(16,185,129,.45); }

    @media (max-width: 480px) {
        .payment-container { padding: 1rem; }
        .payment-card      { padding: 1.5rem; }
        .amount            { font-size: 2.5rem; }
        .step-number       { width: 28px; height: 28px; font-size: .7rem; }
        .step-line         { width: 20px; }
    }
</style>
@endsection
