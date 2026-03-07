@extends('layouts.app')

@section('title', 'Déposer des fonds')
@section('header-title', 'Nouveau dépôt')

@section('content')

<div class="deposit-container">

    {{-- Étape 1: Montant --}}
    <div class="deposit-card" id="step1">
        <div class="deposit-header">
            <div class="step-indicator">
                <span class="step-dot active"></span>
                <span class="step-line"></span>
                <span class="step-dot"></span>
            </div>
            <h2>Montant à déposer</h2>
        </div>

        <div class="amount-section">
            <div class="amount-input-wrapper">
                <input type="number" 
                       id="amountInput" 
                       class="amount-field" 
                       placeholder="0"
                       min="1000"
                       max="1000000"
                       step="1000">
                <span class="currency">FCFA</span>
            </div>
            <p class="amount-hint">Minimum 1 000 FCFA • Maximum 1 000 000 FCFA</p>

            <div class="quick-amounts">
                <button type="button" class="amount-chip" data-amount="5000">5 000</button>
                <button type="button" class="amount-chip" data-amount="10000">10 000</button>
                <button type="button" class="amount-chip" data-amount="25000">25 000</button>
                <button type="button" class="amount-chip" data-amount="50000">50 000</button>
            </div>
        </div>

        <div class="fee-calculation" id="feeCalc" style="display: none;">
            <div class="calc-row">
                <span>Montant</span>
                <strong id="calcAmount">0 FCFA</strong>
            </div>
            <div class="calc-row">
                <span>Frais (1%)</span>
                <strong id="calcFee">0 FCFA</strong>
            </div>
            <div class="calc-row total">
                <span>Total à payer</span>
                <strong id="calcTotal">0 FCFA</strong>
            </div>
        </div>

        <button type="button" class="btn btn-primary btn-block" id="btnContinue" disabled onclick="goToStep2()">
            Continuer
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
    </div>

    {{-- Étape 2: Paiement Kkiapay --}}
    <div class="deposit-card" id="step2" style="display: none;">
        <div class="deposit-header">
            <div class="step-indicator">
                <span class="step-dot completed">✓</span>
                <span class="step-line active"></span>
                <span class="step-dot active"></span>
            </div>
            <h2>Paiement sécurisé</h2>
        </div>

        <div class="payment-summary-box">
            <div class="summary-item">
                <span>Dépôt</span>
                <strong id="summaryDeposit">0 FCFA</strong>
            </div>
            <div class="summary-item">
                <span>Frais</span>
                <span id="summaryFee">0 FCFA</span>
            </div>
            <div class="summary-item total">
                <span>Total</span>
                <strong id="summaryTotal">0 FCFA</strong>
            </div>
        </div>

        <div class="kkiapay-info">
            <div class="kkiapay-logo">
                <svg viewBox="0 0 40 40" width="40" height="40">
                    <rect width="40" height="40" rx="8" fill="#1e40af"/>
                    <path d="M20 10v20M10 20h20" stroke="white" stroke-width="3" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="kkiapay-text">
                <h4>Paiement via Kkiapay</h4>
                <p>Mobile Money • Carte • Transfert</p>
            </div>
        </div>

        <div class="security-badges">
            <div class="badge-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span>Sécurisé</span>
            </div>
            <div class="badge-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <span>Instantané</span>
            </div>
        </div>

        {{-- Zone Kkiapay --}}
        <div id="kkiapayZone" style="display: none;">
            <div id="kkiapay-widget"></div>
        </div>

        <div class="payment-actions" id="paymentActions">
            <button type="button" class="btn btn-primary btn-block btn-lg" onclick="initiateKkiapay()">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a1 1 0 11-2 0 1 1 0 012 0z"/>
                </svg>
                Payer maintenant
            </button>
            <button type="button" class="btn btn-secondary btn-block" onclick="backToStep1()">
                Modifier le montant
            </button>
        </div>
    </div>

    {{-- Étape 3: Traitement --}}
    <div class="deposit-card" id="stepProcessing" style="display: none;">
        <div class="processing-state">
            <div class="spinner-large"></div>
            <h3>Traitement du paiement...</h3>
            <p>Veuillez ne pas fermer cette page</p>
        </div>
    </div>

    {{-- Étape 4: Succès --}}
    <div class="deposit-card card-success" id="stepSuccess" style="display: none;">
        <div class="success-state">
            <div class="success-icon-large">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="48" height="48">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2>Dépôt réussi !</h2>
            <p>Votre portefeuille a été crédité</p>
            
            <div class="success-details">
                <div class="detail-line">
                    <span>Montant</span>
                    <strong id="successAmount">0 FCFA</strong>
                </div>
                <div class="detail-line">
                    <span>Nouveau solde</span>
                    <strong id="successBalance">0 FCFA</strong>
                </div>
                <div class="detail-line">
                    <span>Référence</span>
                    <code id="successRef">-</code>
                </div>
            </div>

            <a href="{{ route('client.wallet.show') }}" class="btn btn-primary btn-block">
                Retour au portefeuille
            </a>
        </div>
    </div>

</div>

@endsection

@section('scripts')
<script src="https://cdn.kkiapay.me/k.js"></script>
<script>
let currentAmount = 0;
let currentFee = 0;
let currentTotal = 0;
let transactionData = null;

// Configuration Kkiapay depuis le backend
const config = {
    publicKey: '{{ config('services.kkiapay.public_key') }}',
    sandbox: {{ config('services.kkiapay.sandbox', true) ? 'true' : 'false' }}
};

document.addEventListener('DOMContentLoaded', function() {
    setupListeners();
    
    if (typeof addSuccessListener === 'function') {
        addSuccessListener(onKkiapaySuccess);
        addFailedListener(onKkiapayFailed);
    }
});

function setupListeners() {
    const input = document.getElementById('amountInput');
    
    input.addEventListener('input', function() {
        calculate(this.value);
    });

    document.querySelectorAll('.amount-chip').forEach(chip => {
        chip.addEventListener('click', function() {
            const amount = this.dataset.amount;
            input.value = amount;
            calculate(amount);
            
            document.querySelectorAll('.amount-chip').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
        });
    });
}

function calculate(value) {
    const amount = parseInt(value) || 0;
    const btn = document.getElementById('btnContinue');
    
    if (amount < 1000 || amount > 1000000) {
        document.getElementById('feeCalc').style.display = 'none';
        btn.disabled = true;
        return;
    }

    currentAmount = amount;
    currentFee = Math.round(amount * 0.01); // 1% frais
    currentTotal = amount + currentFee;

    document.getElementById('calcAmount').textContent = currentAmount.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('calcFee').textContent = currentFee.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('calcTotal').textContent = currentTotal.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('feeCalc').style.display = 'block';
    
    btn.disabled = false;
}

function goToStep2() {
    document.getElementById('summaryDeposit').textContent = currentAmount.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('summaryFee').textContent = currentFee.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('summaryTotal').textContent = currentTotal.toLocaleString('fr-FR') + ' FCFA';

    document.getElementById('step1').style.display = 'none';
    document.getElementById('step2').style.display = 'block';
}

function backToStep1() {
    document.getElementById('step2').style.display = 'none';
    document.getElementById('step1').style.display = 'block';
    document.getElementById('kkiapayZone').style.display = 'none';
    document.getElementById('paymentActions').style.display = 'block';
}

async function initiateKkiapay() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    try {
        // Créer la transaction côté serveur
        const response = await fetch('{{ route('client.wallet.deposit') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                amount: currentAmount,
                payment_method: 'kkiapay'
            })
        });

        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message);
        }

        transactionData = data.transaction;

        // Masquer les boutons, afficher le widget
        document.getElementById('paymentActions').style.display = 'none';
        document.getElementById('kkiapayZone').style.display = 'block';

        // Ouvrir Kkiapay
        openKkiapayWidget({
            amount: currentTotal,
            key: config.publicKey,
            sandbox: config.sandbox,
            data: JSON.stringify({
                transaction_id: transactionData.transaction_id,
                type: 'wallet_deposit'
            }),
            theme: '#1e40af',
            name: 'BHDM',
            position: 'center'
        });

    } catch (error) {
        alert(error.message || 'Erreur lors de l\'initialisation');
        console.error(error);
    }
}

async function onKkiapaySuccess(response) {
    console.log('Paiement réussi:', response);
    
    const kkiapayId = response.transactionId;
    
    if (!transactionData) {
        alert('Erreur interne');
        return;
    }

    // Afficher le traitement
    document.getElementById('step2').style.display = 'none';
    document.getElementById('stepProcessing').style.display = 'block';

    // Vérifier le statut
    try {
        const verifyRes = await fetch('{{ route('client.payment.verify') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                transactionId: kkiapayId,
                funding_request_id: null, // Pas de funding request pour un dépôt wallet
                internal_transaction_id: transactionData.transaction_id
            })
        });

        const result = await verifyRes.json();

        if (result.status === 'paid' || result.status === 'completed') {
            showSuccess(result);
        } else {
            // Polling si pas encore confirmé
            pollStatus(kkiapayId);
        }

    } catch (error) {
        console.error('Erreur vérification:', error);
        pollStatus(kkiapayId);
    }
}

async function pollStatus(kkiapayId) {
    const maxAttempts = 15;
    let attempts = 0;
    
    while (attempts < maxAttempts) {
        attempts++;
        await new Promise(r => setTimeout(r, 2000));
        
        try {
            const res = await fetch('{{ route('client.payment.verify') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    transactionId: kkiapayId,
                    internal_transaction_id: transactionData.transaction_id
                })
            });
            
            const data = await res.json();
            
            if (data.status === 'paid' || data.status === 'completed') {
                showSuccess(data);
                return;
            }
        } catch (e) {
            console.error('Polling error:', e);
        }
    }
    
    // Timeout - rediriger quand même
    window.location.href = '{{ route('client.wallet.show') }}?pending=1';
}

function showSuccess(data) {
    document.getElementById('stepProcessing').style.display = 'none';
    document.getElementById('stepSuccess').style.display = 'block';
    
    document.getElementById('successAmount').textContent = currentAmount.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('successBalance').textContent = (data.new_balance || 0).toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('successRef').textContent = transactionData?.transaction_id || '-';
}

function onKkiapayFailed(response) {
    console.error('Échec:', response);
    alert('Le paiement a échoué ou été annulé.');
    backToStep1();
}
</script>
@endsection

@section('styles')
<style>
    .deposit-container {
        max-width: 500px;
        margin: 0 auto;
        padding: 16px;
        padding-bottom: 100px;
    }

    .deposit-card {
        background: white;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        animation: slideUp 0.4s ease-out;
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .deposit-header {
        margin-bottom: 24px;
    }

    .step-indicator {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 16px;
    }

    .step-dot {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        color: #64748b;
    }

    .step-dot.active {
        background: #1e40af;
        color: white;
    }

    .step-dot.completed {
        background: #10b981;
        color: white;
    }

    .step-line {
        flex: 1;
        height: 2px;
        background: #e2e8f0;
    }

    .step-line.active {
        background: #1e40af;
    }

    .deposit-header h2 {
        font-size: 1.25rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }

    /* Amount Section */
    .amount-section {
        margin-bottom: 24px;
    }

    .amount-input-wrapper {
        position: relative;
        margin-bottom: 8px;
    }

    .amount-field {
        width: 100%;
        padding: 20px 80px 20px 20px;
        font-size: 2rem;
        font-weight: 700;
        color: #0f172a;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        background: #f8fafc;
        text-align: right;
        transition: all 0.2s;
    }

    .amount-field:focus {
        outline: none;
        border-color: #3b82f6;
        background: white;
    }

    .currency {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1rem;
        font-weight: 600;
        color: #64748b;
    }

    .amount-hint {
        font-size: 0.875rem;
        color: #94a3b8;
        margin: 0 0 16px 0;
    }

    .quick-amounts {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .amount-chip {
        padding: 8px 16px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 9999px;
        font-size: 0.875rem;
        font-weight: 500;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s;
    }

    .amount-chip:hover, .amount-chip.active {
        background: #1e40af;
        color: white;
        border-color: #1e40af;
    }

    /* Fee Calculation */
    .fee-calculation {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 24px;
    }

    .calc-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        font-size: 0.9375rem;
        color: #64748b;
    }

    .calc-row.total {
        border-top: 1px solid #e2e8f0;
        margin-top: 8px;
        padding-top: 12px;
    }

    .calc-row.total strong {
        font-size: 1.125rem;
        color: #0f172a;
    }

    /* Payment Summary */
    .payment-summary-box {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 20px;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        font-size: 0.9375rem;
    }

    .summary-item.total {
        border-top: 1px solid #bbf7d0;
        margin-top: 8px;
        padding-top: 12px;
    }

    .summary-item.total strong {
        font-size: 1.25rem;
        color: #166534;
    }

    /* Kkiapay Info */
    .kkiapay-info {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 12px;
        margin-bottom: 16px;
    }

    .kkiapay-text h4 {
        font-size: 1rem;
        font-weight: 600;
        color: #0f172a;
        margin: 0 0 4px 0;
    }

    .kkiapay-text p {
        font-size: 0.875rem;
        color: #64748b;
        margin: 0;
    }

    .security-badges {
        display: flex;
        justify-content: center;
        gap: 24px;
        margin-bottom: 24px;
    }

    .badge-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.875rem;
        color: #64748b;
    }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 14px 24px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 1rem;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary {
        background: linear-gradient(135deg, #1e40af, #3b82f6);
        color: white;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    .btn-primary:hover:not(:disabled) {
        transform: translateY(-2px);
    }

    .btn-primary:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .btn-secondary {
        background: #f1f5f9;
        color: #475569;
    }

    .btn-lg {
        padding: 16px 24px;
        font-size: 1.0625rem;
    }

    .btn-block {
        width: 100%;
    }

    /* Processing State */
    .processing-state {
        text-align: center;
        padding: 40px 20px;
    }

    .spinner-large {
        width: 56px;
        height: 56px;
        border: 4px solid #e2e8f0;
        border-top-color: #3b82f6;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 20px;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .processing-state h3 {
        font-size: 1.25rem;
        color: #0f172a;
        margin: 0 0 8px 0;
    }

    .processing-state p {
        color: #64748b;
        margin: 0;
    }

    /* Success State */
    .card-success {
        text-align: center;
        padding: 40px 24px;
    }

    .success-icon-large {
        width: 80px;
        height: 80px;
        background: #dcfce7;
        color: #16a34a;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
    }

    .success-state h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 8px 0;
    }

    .success-state > p {
        color: #64748b;
        margin: 0 0 24px 0;
    }

    .success-details {
        background: #f8fafc;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 24px;
        text-align: left;
    }

    .detail-line {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #e2e8f0;
    }

    .detail-line:last-child {
        border-bottom: none;
    }

    .detail-line span {
        color: #64748b;
        font-size: 0.9375rem;
    }

    .detail-line strong {
        color: #0f172a;
        font-size: 1rem;
    }

    .detail-line code {
        font-family: monospace;
        background: #eff6ff;
        color: #1e40af;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.875rem;
    }

    #kkiapay-widget {
        min-height: 400px;
    }
</style>
@endsection