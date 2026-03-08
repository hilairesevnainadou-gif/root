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
            <h2>Montant à créditer</h2>
            <p class="subtitle">Entrez le montant que vous souhaitez ajouter à votre portefeuille</p>
        </div>

        <div class="amount-section">
            <div class="amount-input-wrapper">
                <span class="input-prefix">FCFA</span>
                <input type="number" 
                       id="amountInput" 
                       class="amount-field" 
                       placeholder="0"
                       min="1000"
                       max="1000000"
                       step="1000">
            </div>
            <p class="amount-hint">Minimum 1 000 FCFA • Maximum 1 000 000 FCFA</p>

            <div class="quick-amounts">
                <button type="button" class="amount-chip" data-amount="5000">5 000</button>
                <button type="button" class="amount-chip" data-amount="10000">10 000</button>
                <button type="button" class="amount-chip" data-amount="25000">25 000</button>
                <button type="button" class="amount-chip" data-amount="50000">50 000</button>
                <button type="button" class="amount-chip" data-amount="100000">100 000</button>
            </div>
        </div>

        <div class="fee-calculation" id="feeCalc" style="display: none;">
            <div class="calc-header">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <span>Détail du paiement</span>
            </div>
            <div class="calc-row">
                <span>Montant à créditer</span>
                <strong id="calcAmount">0 FCFA</strong>
            </div>
            <div class="calc-row">
                <span>Frais de service (1.9%)</span>
                <span id="calcFee">0 FCFA</span>
            </div>
            <div class="calc-divider"></div>
            <div class="calc-row total">
                <span>Total à débiter</span>
                <strong id="calcTotal">0 FCFA</strong>
            </div>
            <p class="fee-note">💡 Les frais sont ajoutés automatiquement par le processeur de paiement</p>
        </div>

        <button type="button" class="btn btn-primary btn-block" id="btnContinue" disabled onclick="goToStep2()">
            Continuer
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
    </div>

    {{-- Étape 2: Confirmation & Paiement --}}
    <div class="deposit-card" id="step2" style="display: none;">
        <div class="deposit-header">
            <div class="step-indicator">
                <span class="step-dot completed">✓</span>
                <span class="step-line active"></span>
                <span class="step-dot active"></span>
            </div>
            <h2>Confirmer le paiement</h2>
        </div>

        <div class="payment-summary-box">
            <div class="summary-header">
                <div class="wallet-icon-large">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="32" height="32">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <div class="summary-title">Créditer mon portefeuille</div>
            </div>
            
            <div class="summary-details">
                <div class="summary-line">
                    <span>Montant crédité</span>
                    <strong id="summaryDeposit" class="text-primary">0 FCFA</strong>
                </div>
                <div class="summary-line">
                    <span>Frais (1.9%)</span>
                    <span id="summaryFee">0 FCFA</span>
                </div>
                <div class="summary-line total">
                    <span>Total débité</span>
                    <strong id="summaryTotal">0 FCFA</strong>
                </div>
            </div>
        </div>

        <div class="payment-method-simple">
            <div class="method-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div class="method-text">
                <strong>Paiement sécurisé</strong>
                <span>Appuyez sur payer pour continuer</span>
            </div>
        </div>

        {{-- Zone Kkiapay cachée --}}
        <div id="kkiapayZone" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 1000; display: flex; align-items: center; justify-content: center;">
            <div style="background: white; border-radius: 20px; padding: 20px; max-width: 90%; width: 400px; position: relative;">
                <button onclick="closeKkiapay()" style="position: absolute; top: 10px; right: 10px; background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
                <div id="kkiapay-widget"></div>
            </div>
        </div>

        <div class="payment-actions" id="paymentActions">
            <button type="button" class="btn btn-primary btn-block btn-pay" id="btnPay" onclick="initiateKkiapay()">
                <span class="btn-content">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a1 1 0 11-2 0 1 1 0 012 0z"/>
                    </svg>
                    Payer <span id="btnPayAmount">0 FCFA</span>
                </span>
                <span class="btn-loader" style="display: none;">
                    <div class="spinner-small"></div>
                </span>
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
            <h3>Traitement en cours...</h3>
            <p>Veuillez patienter quelques instants</p>
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
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
                    <span>Montant crédité</span>
                    <strong id="successAmount" class="text-success">0 FCFA</strong>
                </div>
                <div class="detail-line">
                    <span>Frais</span>
                    <span id="successFee">0 FCFA</span>
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
                Voir mon portefeuille
            </a>
        </div>
    </div>

</div>

@endsection

@section('scripts')
<script src="https://cdn.kkiapay.me/k.js"></script>
<script>
let currentAmount = 0;      // Montant saisi par l'utilisateur (crédité)
let currentFee = 0;         // Frais 1.9% (calculé pour info)
let currentTotal = 0;       // Total estimé (montant + frais)
let transactionData = null;
let kkiapayOpen = false;

// Configuration Kkiapay
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
    
    // Cacher la modale Kkiapay au départ
    document.getElementById('kkiapayZone').style.display = 'none';
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

    // Montant saisi = ce qui sera crédité
    currentAmount = amount;
    // Frais 1.9% sur le montant (pour info seulement)
    currentFee = Math.round(amount * 0.019);
    // Total estimé (montant + frais) - Kkiapay ajoutera ses frais
    currentTotal = amount + currentFee;

    document.getElementById('calcAmount').textContent = currentAmount.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('calcFee').textContent = currentFee.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('calcTotal').textContent = currentTotal.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('feeCalc').style.display = 'block';
    
    btn.disabled = false;
}

function goToStep2() {
    // Mettre à jour le récapitulatif
    document.getElementById('summaryDeposit').textContent = currentAmount.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('summaryFee').textContent = currentFee.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('summaryTotal').textContent = currentTotal.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('btnPayAmount').textContent = currentTotal.toLocaleString('fr-FR') + ' FCFA';

    document.getElementById('step1').style.display = 'none';
    document.getElementById('step2').style.display = 'block';
    
    // Scroll en haut
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function backToStep1() {
    document.getElementById('step2').style.display = 'none';
    document.getElementById('step1').style.display = 'block';
    closeKkiapay();
}

function closeKkiapay() {
    document.getElementById('kkiapayZone').style.display = 'none';
    kkiapayOpen = false;
}

async function initiateKkiapay() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const btn = document.getElementById('btnPay');
    
    btn.classList.add('loading');
    btn.disabled = true;
    
    try {
        // CORRECTION: Envoyer seulement le montant à créditer (sans les frais)
        // Kkiapay ajoutera automatiquement ses frais de 1.9%
        const response = await fetch('{{ route('client.wallet.deposit') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                amount: currentAmount,           // Montant à créditer (sans frais)
                payment_method: 'kkiapay'
            })
        });

        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Erreur lors de la création');
        }

        transactionData = data.transaction;

        // Afficher le widget Kkiapay en modale
        document.getElementById('kkiapayZone').style.display = 'flex';
        kkiapayOpen = true;

        // IMPORTANT: Envoyer seulement le MONTANT à créditer à Kkiapay
        // Kkiapay calculera et ajoutera ses propres frais de 1.9%
        openKkiapayWidget({
            amount: currentAmount,  // Seulement le montant à créditer !
            key: config.publicKey,
            sandbox: config.sandbox,
            data: JSON.stringify({
                transaction_id: transactionData.transaction_id,
                type: 'wallet_deposit',
                amount_credited: currentAmount
            }),
            theme: '#1e40af',
            name: 'BHDM',
            // CORRECTION: Utiliser le bon callback URL pour le webhook
            callback: 'https://bdhml.novatechbenin.com/webhook/kkiapay/wallet',
            position: 'center'
        });

    } catch (error) {
        console.error('Erreur:', error);
        alert(error.message || 'Erreur lors de l\'initialisation du paiement');
        btn.classList.remove('loading');
        btn.disabled = false;
    }
}

async function onKkiapaySuccess(response) {
    console.log('Paiement réussi:', response);
    
    const kkiapayId = response.transactionId;
    
    if (!transactionData) {
        alert('Erreur interne: transaction non trouvée');
        return;
    }

    // Fermer la modale
    closeKkiapay();

    // Afficher le traitement
    document.getElementById('step2').style.display = 'none';
    document.getElementById('stepProcessing').style.display = 'block';

    // Vérifier le statut avec retry
    let verified = false;
    let attempts = 0;
    const maxAttempts = 30;
    
    while (!verified && attempts < maxAttempts) {
        attempts++;
        
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
                    internal_transaction_id: transactionData.transaction_id
                })
            });

            const result = await verifyRes.json();
            console.log('Vérification:', result);

            if (result.status === 'completed' || result.status === 'paid') {
                showSuccess(result);
                verified = true;
                return;
            }
            
            if (result.status === 'failed') {
                alert('Le paiement a échoué: ' + (result.message || 'Erreur'));
                document.getElementById('stepProcessing').style.display = 'none';
                document.getElementById('step2').style.display = 'block';
                return;
            }

        } catch (error) {
            console.error('Erreur vérification:', error);
        }
        
        // Attendre avant retry (délai croissant)
        await new Promise(r => setTimeout(r, Math.min(1000 + (attempts * 200), 3000)));
    }
    
    // Si on arrive ici sans succès, rediriger vers le wallet
    if (!verified) {
        window.location.href = '{{ route('client.wallet.show') }}?pending=1';
    }
}

function showSuccess(data) {
    document.getElementById('stepProcessing').style.display = 'none';
    document.getElementById('stepSuccess').style.display = 'block';
    
    document.getElementById('successAmount').textContent = currentAmount.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('successFee').textContent = currentFee.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('successBalance').textContent = (data.new_balance || 0).toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('successRef').textContent = transactionData?.transaction_id || '-';
    
    // Scroll en haut
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function onKkiapayFailed(response) {
    console.error('Échec:', response);
    closeKkiapay();
    
    const btn = document.getElementById('btnPay');
    btn.classList.remove('loading');
    btn.disabled = false;
    
    // Ne pas afficher d'alerte si c'est juste une fermeture
    if (response && response.transactionId) {
        alert('Le paiement a été annulé ou a échoué. Veuillez réessayer.');
    }
}
</script>
@endsection

@section('styles')
<style>
    .deposit-container {
        max-width: 480px;
        margin: 0 auto;
        padding: 16px;
        padding-bottom: 100px;
    }

    .deposit-card {
        background: white;
        border-radius: 24px;
        padding: 28px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        animation: slideUp 0.4s ease-out;
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .deposit-header {
        margin-bottom: 28px;
        text-align: center;
    }

    .step-indicator {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .step-dot {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        color: #64748b;
        font-weight: 600;
        transition: all 0.3s;
    }

    .step-dot.active {
        background: #1e40af;
        color: white;
        box-shadow: 0 4px 12px rgba(30, 64, 175, 0.3);
    }

    .step-dot.completed {
        background: #10b981;
        color: white;
    }

    .step-line {
        width: 40px;
        height: 3px;
        background: #e2e8f0;
        border-radius: 2px;
    }

    .step-line.active {
        background: #1e40af;
    }

    .deposit-header h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 8px 0;
    }

    .subtitle {
        font-size: 0.9375rem;
        color: #64748b;
        margin: 0;
    }

    /* Amount Section */
    .amount-section {
        margin-bottom: 24px;
    }

    .amount-input-wrapper {
        position: relative;
        margin-bottom: 12px;
    }

    .input-prefix {
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.125rem;
        font-weight: 600;
        color: #64748b;
    }

    .amount-field {
        width: 100%;
        padding: 24px 20px 24px 80px;
        font-size: 2.5rem;
        font-weight: 700;
        color: #0f172a;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        background: #f8fafc;
        text-align: left;
        transition: all 0.2s;
    }

    .amount-field:focus {
        outline: none;
        border-color: #1e40af;
        background: white;
        box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.1);
    }

    .amount-hint {
        font-size: 0.875rem;
        color: #94a3b8;
        margin: 0 0 20px 4px;
    }

    .quick-amounts {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .amount-chip {
        padding: 10px 18px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 9999px;
        font-size: 0.9375rem;
        font-weight: 500;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s;
    }

    .amount-chip:hover, .amount-chip.active {
        background: #1e40af;
        color: white;
        border-color: #1e40af;
        transform: scale(1.05);
    }

    /* Fee Calculation */
    .fee-calculation {
        background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
        border: 1px solid #7dd3fc;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 24px;
    }

    .calc-header {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        color: #0369a1;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px dashed #7dd3fc;
    }

    .calc-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        font-size: 0.9375rem;
        color: #475569;
    }

    .calc-row strong {
        color: #0f172a;
        font-weight: 600;
    }

    .calc-row.total {
        font-size: 1.125rem;
    }

    .calc-row.total strong {
        font-size: 1.25rem;
        color: #1e40af;
    }

    .calc-divider {
        height: 1px;
        background: #7dd3fc;
        margin: 8px 0;
    }

    .fee-note {
        font-size: 0.8125rem;
        color: #64748b;
        margin: 12px 0 0 0;
        font-style: italic;
    }

    /* Payment Summary Box */
    .payment-summary-box {
        background: linear-gradient(135deg, #f0fdf4, #dcfce7);
        border: 1px solid #86efac;
        border-radius: 20px;
        padding: 24px;
        margin-bottom: 24px;
    }

    .summary-header {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px dashed #86efac;
    }

    .wallet-icon-large {
        width: 56px;
        height: 56px;
        background: white;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #16a34a;
        box-shadow: 0 4px 12px rgba(22, 163, 74, 0.15);
    }

    .summary-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: #166534;
    }

    .summary-details {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .summary-line {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.9375rem;
    }

    .summary-line span {
        color: #64748b;
    }

    .summary-line strong {
        color: #0f172a;
        font-weight: 600;
    }

    .summary-line.total {
        padding-top: 12px;
        border-top: 2px solid #86efac;
        font-size: 1.125rem;
    }

    .summary-line.total strong {
        font-size: 1.375rem;
        color: #166534;
    }

    .text-primary {
        color: #1e40af !important;
    }

    .text-success {
        color: #16a34a !important;
    }

    /* Payment Method Simple */
    .payment-method-simple {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px;
        background: #f8fafc;
        border: 2px dashed #cbd5e1;
        border-radius: 16px;
        margin-bottom: 24px;
    }

    .method-icon {
        width: 48px;
        height: 48px;
        background: #e2e8f0;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #475569;
    }

    .method-text {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .method-text strong {
        font-size: 1rem;
        color: #0f172a;
    }

    .method-text span {
        font-size: 0.875rem;
        color: #64748b;
    }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 16px 24px;
        border-radius: 14px;
        font-weight: 600;
        font-size: 1rem;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary {
        background: linear-gradient(135deg, #1e40af, #3b82f6);
        color: white;
        box-shadow: 0 4px 16px rgba(37, 99, 235, 0.35);
    }

    .btn-primary:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.45);
    }

    .btn-primary:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .btn-secondary {
        background: #f1f5f9;
        color: #475569;
    }

    .btn-secondary:hover {
        background: #e2e8f0;
    }

    .btn-lg {
        padding: 18px 24px;
        font-size: 1.0625rem;
    }

    .btn-block {
        width: 100%;
    }

    .btn-pay {
        position: relative;
        overflow: hidden;
    }

    .btn-pay .btn-content,
    .btn-pay .btn-loader {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: opacity 0.2s;
    }

    .btn-pay .btn-loader {
        position: absolute;
        inset: 0;
        opacity: 0;
    }

    .btn-pay.loading .btn-content {
        opacity: 0;
    }

    .btn-pay.loading .btn-loader {
        opacity: 1;
    }

    .spinner-small {
        width: 20px;
        height: 20px;
        border: 2px solid rgba(255,255,255,0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    /* Processing State */
    .processing-state {
        text-align: center;
        padding: 40px 20px;
    }

    .spinner-large {
        width: 64px;
        height: 64px;
        border: 4px solid #e2e8f0;
        border-top-color: #3b82f6;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 24px;
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
        margin: 0 0 24px 0;
    }

    .progress-bar {
        width: 100%;
        height: 4px;
        background: #e2e8f0;
        border-radius: 2px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        width: 30%;
        background: linear-gradient(90deg, #3b82f6, #1e40af);
        border-radius: 2px;
        animation: progress 2s ease-in-out infinite;
    }

    @keyframes progress {
        0% { width: 0%; margin-left: 0; }
        50% { width: 70%; margin-left: 15%; }
        100% { width: 0%; margin-left: 100%; }
    }

    /* Success State */
    .card-success {
        text-align: center;
        padding: 40px 24px;
    }

    .success-icon-large {
        width: 88px;
        height: 88px;
        background: linear-gradient(135deg, #dcfce7, #bbf7d0);
        color: #16a34a;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        box-shadow: 0 8px 24px rgba(22, 163, 74, 0.2);
    }

    .success-state h2 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 8px 0;
    }

    .success-state > p {
        color: #64748b;
        margin: 0 0 28px 0;
        font-size: 1rem;
    }

    .success-details {
        background: #f8fafc;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 28px;
        text-align: left;
    }

    .detail-line {
        display: flex;
        justify-content: space-between;
        padding: 14px 0;
        border-bottom: 1px solid #e2e8f0;
        font-size: 0.9375rem;
    }

    .detail-line:last-child {
        border-bottom: none;
    }

    .detail-line span {
        color: #64748b;
    }

    .detail-line strong {
        color: #0f172a;
        font-weight: 600;
    }

    .detail-line code {
        font-family: 'SF Mono', monospace;
        background: #eff6ff;
        color: #1e40af;
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 0.875rem;
    }

    /* Payment Actions */
    .payment-actions {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    /* Kkiapay Zone */
    #kkiapayZone {
        backdrop-filter: blur(4px);
    }

    #kkiapay-widget {
        min-height: 450px;
    }

    /* Responsive */
    @media (max-width: 480px) {
        .deposit-container {
            padding: 12px;
        }
        
        .deposit-card {
            padding: 20px;
            border-radius: 20px;
        }
        
        .amount-field {
            font-size: 2rem;
            padding: 20px 16px 20px 70px;
        }
        
        .quick-amounts {
            gap: 8px;
        }
        
        .amount-chip {
            padding: 8px 14px;
            font-size: 0.875rem;
        }
    }
</style>
@endsection