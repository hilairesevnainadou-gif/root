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
                    <span class="step-number">✓</span>
                    <span class="step-label">Demande</span>
                </div>
                <div class="step-line active"></div>
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
        <div class="payment-card" id="paymentCard">
            
            {{-- Référence --}}
            <div class="payment-reference">
                <span class="ref-label">Demande</span>
                <span class="ref-value">{{ $fundingRequest->request_number }}</span>
            </div>

            {{-- Montant à payer --}}
            <div class="payment-amount-section">
                <span class="amount-label">Frais d'inscription à régler</span>
                <div class="amount-display">
                    <span class="currency">FCFA</span>
                    <span class="amount" id="amountDisplay">{{ number_format($fees['current'], 0, ',', ' ') }}</span>
                </div>
                <input type="hidden" id="feeAmount" value="{{ $fees['current'] }}">
            </div>

            {{-- Info frais finals --}}
            <div class="info-frais-finals">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Des frais finals de {{ number_format($fees['final'], 0, ',', ' ') }} FCFA seront demandés lors de l'approbation</span>
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span>Paiement sécurisé par Kkiapay</span>
            </div>
        </div>

        {{-- Widget Kkiapay (caché initialement) --}}
        <div id="kkiapay-container" style="display: none; margin-top: 1.5rem;">
            <div id="kkiapay-widget"></div>
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
            <h2>Paiement déjà effectué</h2>
            <p>Votre demande est en cours de traitement.</p>
            <div class="paid-actions">
                <a href="{{ route('client.documents.required', $fundingRequest) }}" class="btn-primary">
                    Ajouter les documents
                </a>
                <a href="{{ route('client.requests.show', $fundingRequest) }}" class="btn-secondary">
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
    let currentTransaction = null;
    
    // Configuration Kkiapay
    const kkiapayConfig = {
        key: '{{ config('services.kkiapay.public_key') }}',
        sandbox: {{ config('services.kkiapay.sandbox', true) ? 'true' : 'false' }},
        theme: '#2563eb'
    };

    // Initialiser les écouteurs Kkiapay au chargement
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof addSuccessListener === 'function') {
            addSuccessListener(onKkiapaySuccess);
        }
        if (typeof addFailedListener === 'function') {
            addFailedListener(onKkiapayFailed);
        }
    });

    function initierPaiement() {
        if (isProcessing) return;
        
        const btn = document.getElementById('btnPay');
        const feeAmount = parseInt(document.getElementById('feeAmount').value);
        
        if (!feeAmount || feeAmount <= 0) {
            alert('Erreur: montant invalide');
            return;
        }

        // Désactiver le bouton et montrer le loader
        isProcessing = true;
        btn.disabled = true;
        btn.querySelector('.btn-content').style.display = 'none';
        btn.querySelector('.btn-loader').style.display = 'flex';

        // Ouvrir le widget Kkiapay
        try {
            openKkiapayWidget({
                amount: feeAmount,
                key: kkiapayConfig.key,
                sandbox: kkiapayConfig.sandbox,
                data: JSON.stringify({
                    funding_request_id: {{ $fundingRequest->id }},
                    request_number: '{{ $fundingRequest->request_number }}',
                    type: 'registration_fee'
                }),
                theme: kkiapayConfig.theme,
                position: 'center',
                callback: '{{ route('wallet.callback') }}' // Optionnel: redirection après paiement
            });

            // Afficher le conteneur du widget
            document.getElementById('kkiapay-container').style.display = 'block';
            
            // Scroll vers le widget
            document.getElementById('kkiapay-container').scrollIntoView({ behavior: 'smooth' });

        } catch (error) {
            console.error('Erreur Kkiapay:', error);
            resetButton();
            alert('Erreur lors de l\'ouverture du paiement. Veuillez réessayer.');
        }
    }

    function resetButton() {
        isProcessing = false;
        const btn = document.getElementById('btnPay');
        btn.disabled = false;
        btn.querySelector('.btn-content').style.display = 'flex';
        btn.querySelector('.btn-loader').style.display = 'none';
    }

    function onKkiapaySuccess(response) {
        console.log('Paiement réussi:', response);
        
        // Cacher le widget et montrer le loading
        document.getElementById('kkiapay-container').style.display = 'none';
        document.getElementById('payment-processing').style.display = 'flex';
        
        const feeAmount = parseInt(document.getElementById('feeAmount').value);
        
        // Vérifier le paiement côté serveur
        verifyPayment(response.transactionId, feeAmount);
    }

    function onKkiapayFailed(response) {
        console.log('Paiement échoué:', response);
        resetButton();
        
        // Cacher le widget
        document.getElementById('kkiapay-container').style.display = 'none';
        
        // Ne pas afficher d'erreur si c'est juste une fermeture
        if (response && response.transactionId) {
            alert('Le paiement a été annulé ou a échoué. Vous pouvez réessayer.');
        }
    }

    async function verifyPayment(transactionId, amountPaid) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        try {
            const response = await fetch('{{ route('client.requests.payment.verify', $fundingRequest) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    transactionId: transactionId,
                    amount_paid: amountPaid
                })
            });

            const data = await response.json();
            
            if (data.success) {
                // Redirection vers la page de succès
                window.location.href = data.redirect_url || '{{ route('client.documents.required', $fundingRequest) }}';
            } else {
                throw new Error(data.message || 'Erreur de vérification');
            }

        } catch (error) {
            console.error('Erreur vérification:', error);
            
            // Retry après 2 secondes (le webhook peut arriver en retard)
            setTimeout(() => {
                verifyPayment(transactionId, amountPaid);
            }, 2000);
        }
    }

    function confirmerAnnulation() {
        return confirm('Êtes-vous sûr de vouloir annuler cette demande ?\n\nCette action est irréversible et toutes les informations seront perdues.');
    }
</script>
@endsection

@section('styles')
<style>
    :root {
        --primary: #2563eb;
        --primary-dark: #1d4ed8;
        --success: #10b981;
        --danger: #ef4444;
        --warning: #f59e0b;
        --surface: #ffffff;
        --background: #f8fafc;
        --text: #0f172a;
        --text-muted: #64748b;
        --border: #e2e8f0;
        --radius: 12px;
        --radius-sm: 8px;
        --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .payment-container {
        max-width: 480px;
        margin: 0 auto;
        padding: 1.5rem 1rem;
    }

    /* Step Indicator */
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
        gap: 0.5rem;
    }

    .step-number {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        font-weight: 700;
        background: var(--border);
        color: var(--text-muted);
        transition: all 0.3s ease;
    }

    .step.completed .step-number {
        background: var(--success);
        color: white;
    }

    .step.active .step-number {
        background: var(--primary);
        color: white;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.2);
        animation: pulse-ring 2s infinite;
    }

    @keyframes pulse-ring {
        0%, 100% { box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.2); }
        50% { box-shadow: 0 0 0 8px rgba(37, 99, 235, 0.1); }
    }

    .step-label {
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--text-muted);
    }

    .step.completed .step-label,
    .step.active .step-label {
        color: var(--text);
        font-weight: 600;
    }

    .step-line {
        width: 40px;
        height: 2px;
        background: var(--border);
        border-radius: 1px;
        transition: all 0.3s ease;
    }

    .step-line.active {
        background: var(--primary);
    }

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
        font-size: 0.75rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.25rem;
    }

    .ref-value {
        font-family: 'SF Mono', monospace;
        font-size: 1rem;
        color: var(--text);
        font-weight: 700;
    }

    /* Amount Section */
    .payment-amount-section {
        text-align: center;
        margin-bottom: 1rem;
    }

    .amount-label {
        display: block;
        font-size: 0.875rem;
        color: var(--text-muted);
        margin-bottom: 0.75rem;
    }

    .amount-display {
        display: flex;
        align-items: baseline;
        justify-content: center;
        gap: 0.5rem;
    }

    .currency {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--text-muted);
    }

    .amount {
        font-size: 3.5rem;
        font-weight: 800;
        color: var(--primary);
        line-height: 1;
        letter-spacing: -0.02em;
    }

    .info-frais-finals {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: var(--radius-sm);
        padding: 1rem;
        margin-top: 1.5rem;
        font-size: 0.875rem;
        color: #1e40af;
    }

    .info-frais-finals svg {
        flex-shrink: 0;
        margin-top: 0.125rem;
    }

    .payment-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, var(--border), transparent);
        margin: 1.5rem 0;
    }

    /* Actions */
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
        padding: 1rem 1.5rem;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        border: none;
        border-radius: var(--radius);
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
        position: relative;
        overflow: hidden;
    }

    .btn-pay:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.45);
    }

    .btn-pay:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .btn-content {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

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
        border: 3px solid rgba(255, 255, 255, 0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .form-cancel {
        width: 100%;
    }

    .btn-cancel {
        width: 100%;
        padding: 0.875rem;
        background: transparent;
        color: var(--text-muted);
        border: 1.5px solid var(--border);
        border-radius: var(--radius);
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-cancel:hover {
        background: #fef2f2;
        color: var(--danger);
        border-color: var(--danger);
    }

    .payment-security {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--border);
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .payment-security svg {
        color: var(--success);
    }

    /* Loading Overlay */
    .loading-overlay {
        position: fixed;
        inset: 0;
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .loading-content {
        text-align: center;
        padding: 2rem;
    }

    .spinner {
        width: 56px;
        height: 56px;
        border: 4px solid var(--border);
        border-top-color: var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 1.5rem;
    }

    .loading-content p {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--text);
        margin-bottom: 0.5rem;
    }

    .loading-content small {
        font-size: 0.875rem;
        color: var(--text-muted);
    }

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
        box-shadow: 0 8px 24px rgba(16, 185, 129, 0.2);
    }

    .status-paid h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 0.5rem;
    }

    .status-paid p {
        color: var(--text-muted);
        margin-bottom: 2rem;
    }

    .paid-actions {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 1rem 1.5rem;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        border-radius: var(--radius);
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.45);
    }

    .btn-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 1rem 1.5rem;
        background: var(--background);
        color: var(--text-muted);
        border: 1.5px solid var(--border);
        border-radius: var(--radius);
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s;
    }

    .btn-secondary:hover {
        background: var(--surface);
        color: var(--text);
        border-color: var(--text-muted);
    }

    /* Kkiapay Container */
    #kkiapay-container {
        background: var(--surface);
        border-radius: var(--radius);
        box-shadow: var(--shadow-lg);
        overflow: hidden;
        border: 1px solid var(--border);
    }

    #kkiapay-widget {
        min-height: 450px;
    }

    @media (max-width: 480px) {
        .payment-container {
            padding: 1rem;
        }

        .payment-card {
            padding: 1.5rem;
        }

        .amount {
            font-size: 2.5rem;
        }

        .step-number {
            width: 32px;
            height: 32px;
            font-size: 0.75rem;
        }

        .step-line {
            width: 24px;
        }
    }
</style>
@endsection