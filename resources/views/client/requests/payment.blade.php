@extends('layouts.app')

@section('title', 'Paiement - ' . $fundingRequest->request_number)
@section('header-title', 'Paiement des frais')

@section('content')

<div class="payment-container">
    
    @if($fundingRequest->status === 'draft' && $fundingRequest->payment_status === 'pending')

        {{-- Progression --}}
        <div class="payment-header">
            <div class="step-indicator">
                <div class="step completed">
                    <span class="step-number">1</span>
                    <span class="step-label">Demande</span>
                </div>
                <div class="step-line"></div>
                <div class="step active">
                    <span class="step-number">2</span>
                    <span class="step-label">Paiement</span>
                </div>
                <div class="step-line"></div>
                <div class="step">
                    <span class="step-number">3</span>
                    <span class="step-label">Documents</span>
                </div>
            </div>
        </div>

        {{-- Carte principale --}}
        <div class="payment-card">
            
            {{-- Référence --}}
            <div class="payment-reference">
                <span class="ref-label">Demande</span>
                <span class="ref-value">{{ $fundingRequest->request_number }}</span>
            </div>

            {{-- Montant à payer : UNIQUEMENT frais d'inscription initiaux --}}
            <div class="payment-amount-section">
                <span class="amount-label">Frais d'inscription à régler</span>
                <div class="amount-display">
                    <span class="currency">FCFA</span>
                    <span class="amount">{{ number_format($fees['current'], 0, ',', ' ') }}</span>
                </div>
            </div>

            {{-- Info frais finals (pour information seulement) --}}
            <div class="info-frais-finals">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Des frais finals de {{ number_format($fees['final'], 0, ',', ' ') }} FCFA seront demandés lors de l'approbation</span>
            </div>

            {{-- Séparateur --}}
            <div class="payment-divider"></div>

            {{-- Actions : Payer et Annuler --}}
            <div class="payment-actions">
                <button type="button" class="btn-pay" onclick="initierPaiement()">
                    <span class="btn-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a1 1 0 11-2 0 1 1 0 012 0z"/>
                        </svg>
                    </span>
                    <span class="btn-text">Payer {{ number_format($fees['current'], 0, ',', ' ') }} FCFA</span>
                </button>

                <form action="{{ route('client.requests.destroy', $fundingRequest) }}" method="POST" class="form-cancel">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-cancel" onclick="return confirmerAnnulation()">
                        <span>Annuler la demande</span>
                    </button>
                </form>
            </div>

            {{-- Sécurité --}}
            <div class="payment-security">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span>Paiement sécurisé par Kkiapay</span>
            </div>

        </div>

        {{-- Widget Kkiapay --}}
        <div id="kkiapay-widget" class="widget-container" style="display: none;"></div>

        {{-- Loading --}}
        <div id="payment-loading" class="loading-overlay" style="display: none;">
            <div class="loading-content">
                <div class="spinner"></div>
                <p>Confirmation du paiement...</p>
            </div>
        </div>

    @else

        {{-- État non disponible --}}
        <div class="status-unavailable">
            <div class="status-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="48" height="48">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2>Paiement effectué</h2>
            <p>Les frais d'inscription ont déjà été réglés.</p>
            <a href="{{ route('client.requests.show', $fundingRequest) }}" class="btn-view">
                Continuer
            </a>
        </div>

    @endif

</div>

@endsection

@section('scripts')
<script src="https://cdn.kkiapay.me/k.js "></script>
<script>
let paiementActif = false;

function initierPaiement() {
    if (paiementActif) return;
    paiementActif = true;

    const btn = document.querySelector('.btn-pay');
    btn.classList.add('processing');
    btn.innerHTML = '<span class="btn-spinner"></span><span class="btn-text">Chargement...</span>';

    // PAYER UNIQUEMENT LES FRAIS D'INSCRIPTION (current)
    openKkiapayWidget({
        amount: {{ $fees['current'] }},
        key: '{{ config('services.kkiapay.public_key') }}',
        sandbox: {{ config('services.kkiapay.sandbox', true) ? 'true' : 'false' }},
        data: '{{ $fundingRequest->request_number }}',
        theme: '#2563eb',
        position: 'center'
    });

    document.getElementById('kkiapay-widget').style.display = 'block';
}

function confirmerAnnulation() {
    return confirm('Annuler cette demande ?\n\nLes informations saisies seront perdues.');
}

// Succès
addSuccessListener(function(response) {
    document.getElementById('kkiapay-widget').style.display = 'none';
    document.getElementById('payment-loading').style.display = 'flex';
    
    fetch('{{ route('client.payment.verify') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            transactionId: response.transactionId,
            funding_request_id: {{ $fundingRequest->id }},
            amount_paid: {{ $fees['current'] }}  // Frais d'inscription payés
        })
    })
    .then(() => window.location.href = '{{ route('client.documents.required', $fundingRequest) }}')
    .catch(() => window.location.reload());
});

// Échec
addFailedListener(function() {
    paiementActif = false;
    const btn = document.querySelector('.btn-pay');
    btn.classList.remove('processing');
    btn.innerHTML = `<span class="btn-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a1 1 0 11-2 0 1 1 0 012 0z"/></svg></span><span class="btn-text">Payer {{ number_format($fees['current'], 0, ',', ' ') }} FCFA</span>`;
    
    alert('Paiement annulé. Vous pouvez réessayer.');
});
</script>
@endsection

@section('styles')
<style>
    .payment-container {
        max-width: 480px;
        margin: 0 auto;
        padding: 1rem;
    }

    .payment-header {
        margin-bottom: 2rem;
    }

    .step-indicator {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.25rem;
    }

    .step-number {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        font-weight: 600;
        background: var(--border);
        color: var(--text-muted);
    }

    .step.completed .step-number {
        background: var(--primary);
        color: white;
    }

    .step.active .step-number {
        background: var(--primary);
        color: white;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.2);
    }

    .step-label {
        font-size: 0.75rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    .step.completed .step-label,
    .step.active .step-label {
        color: var(--primary);
        font-weight: 600;
    }

    .step-line {
        width: 40px;
        height: 2px;
        background: var(--border);
    }

    .payment-card {
        background: var(--surface);
        border-radius: var(--radius);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        padding: 2rem;
    }

    .payment-reference {
        text-align: center;
        margin-bottom: 1.5rem;
    }

    .ref-label {
        display: block;
        font-size: 0.75rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.25rem;
    }

    .ref-value {
        font-family: monospace;
        font-size: 0.875rem;
        color: var(--text);
        font-weight: 600;
    }

    .payment-amount-section {
        text-align: center;
        margin-bottom: 1rem;
    }

    .amount-label {
        display: block;
        font-size: 0.875rem;
        color: var(--text-muted);
        margin-bottom: 0.5rem;
    }

    .amount-display {
        display: flex;
        align-items: baseline;
        justify-content: center;
        gap: 0.5rem;
    }

    .currency {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text);
    }

    .amount {
        font-size: 3rem;
        font-weight: 700;
        color: var(--primary);
        line-height: 1;
    }

    .info-frais-finals {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: #eff6ff;
        border-radius: var(--radius-sm);
        padding: 0.75rem 1rem;
        margin-top: 1rem;
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    .info-frais-finals svg {
        flex-shrink: 0;
        color: var(--primary);
    }

    .payment-divider {
        height: 1px;
        background: var(--border);
        margin: 1.5rem 0;
    }

    .payment-actions {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .btn-pay {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        padding: 1rem 1.5rem;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: var(--radius);
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-pay:hover {
        background: #1d4ed8;
        transform: translateY(-1px);
        box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);
    }

    .btn-pay.processing {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .btn-icon {
        display: flex;
    }

    .btn-spinner {
        width: 20px;
        height: 20px;
        border: 2px solid rgba(255,255,255,0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    .form-cancel {
        width: 100%;
    }

    .btn-cancel {
        width: 100%;
        padding: 0.875rem;
        background: transparent;
        color: var(--text-muted);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-cancel:hover {
        background: #fef2f2;
        color: #ef4444;
        border-color: #ef4444;
    }

    .payment-security {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 1.5rem;
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .widget-container {
        margin-top: 1.5rem;
        min-height: 400px;
    }

    .loading-overlay {
        position: fixed;
        inset: 0;
        background: rgba(255, 255, 255, 0.95);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 100;
    }

    .loading-content {
        text-align: center;
    }

    .loading-content p {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text);
        margin-top: 1rem;
    }

    .spinner {
        width: 48px;
        height: 48px;
        border: 4px solid var(--border);
        border-top-color: var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .status-unavailable {
        text-align: center;
        padding: 3rem 1.5rem;
    }

    .status-icon {
        width: 80px;
        height: 80px;
        background: #dcfce7;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #16a34a;
        margin: 0 auto 1.5rem;
    }

    .status-unavailable h2 {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text);
        margin-bottom: 0.5rem;
    }

    .status-unavailable p {
        color: var(--text-muted);
        margin-bottom: 1.5rem;
    }

    .btn-view {
        display: inline-flex;
        padding: 0.875rem 1.5rem;
        background: var(--primary);
        color: white;
        border-radius: var(--radius);
        text-decoration: none;
        font-weight: 500;
    }

    @media (max-width: 480px) {
        .payment-card {
            padding: 1.5rem;
        }
        .amount {
            font-size: 2.5rem;
        }
    }
</style>
@endsection