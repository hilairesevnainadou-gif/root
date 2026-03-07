@extends('layouts.app')

@section('title', 'Retirer des fonds')
@section('header-title', 'Demande de retrait')

@section('content')

<div class="withdraw-container">

    {{-- Vérification solde --}}
    @if($wallet->balance < 5000)
        <div class="alert alert-warning">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <strong>Solde insuffisant</strong>
                <p>Le montant minimum pour un retrait est de 5 000 FCFA.</p>
            </div>
        </div>
    @endif

    {{-- Étape 1: Montant --}}
    <div class="withdraw-card" id="step1">
        <div class="withdraw-header">
            <h2>Montant à retirer</h2>
            <p class="subtitle">Solde disponible: <strong>{{ number_format($wallet->balance, 0, ',', ' ') }} FCFA</strong></p>
        </div>

        <div class="amount-section">
            <div class="amount-input-wrapper">
                <input type="number" 
                       id="withdrawAmount" 
                       class="amount-field" 
                       placeholder="0"
                       min="5000"
                       max="{{ $wallet->balance }}"
                       step="1000">
                <span class="currency">FCFA</span>
            </div>
            <p class="amount-hint">Minimum 5 000 FCFA • Maximum {{ number_format($wallet->balance, 0, ',', ' ') }} FCFA</p>

            <button type="button" class="btn btn-text" onclick="setMaxAmount()">
                Utiliser le solde maximum
            </button>
        </div>

        <div class="withdraw-info">
            <div class="info-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Les frais de retrait sont de 2% (minimum 500 FCFA)</span>
            </div>
        </div>

        <button type="button" class="btn btn-primary btn-block" id="btnContinue" disabled onclick="goToStep2()">
            Continuer
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
    </div>

    {{-- Étape 2: Méthode de retrait --}}
    <div class="withdraw-card" id="step2" style="display: none;">
        <div class="withdraw-header">
            <h2>Méthode de retrait</h2>
            <p class="subtitle">Choisissez comment recevoir vos fonds</p>
        </div>

        <div class="withdraw-methods">
            <label class="method-option">
                <input type="radio" name="withdraw_method" value="mobile_money" checked>
                <div class="method-card">
                    <div class="method-icon orange">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="method-info">
                        <h4>Mobile Money</h4>
                        <p>Orange Money, Wave, Free Money</p>
                    </div>
                    <div class="method-check"></div>
                </div>
            </label>

            <label class="method-option">
                <input type="radio" name="withdraw_method" value="bank_transfer">
                <div class="method-card">
                    <div class="method-icon blue">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <div class="method-info">
                        <h4>Virement bancaire</h4>
                        <p>Compte bancaire ou carte</p>
                    </div>
                    <div class="method-check"></div>
                </div>
            </label>
        </div>

        <div class="withdraw-summary">
            <div class="summary-row">
                <span>Montant</span>
                <strong id="summaryAmount">0 FCFA</strong>
            </div>
            <div class="summary-row">
                <span>Frais (2%)</span>
                <span id="summaryFee">0 FCFA</span>
            </div>
            <div class="summary-row total">
                <span>Vous recevrez</span>
                <strong id="summaryNet">0 FCFA</strong>
            </div>
        </div>

        <button type="button" class="btn btn-primary btn-block" onclick="goToStep3()">
            Confirmer le retrait
        </button>
        <button type="button" class="btn btn-secondary btn-block" onclick="backToStep1()">
            Modifier
        </button>
    </div>

    {{-- Étape 3: Détails paiement --}}
    <div class="withdraw-card" id="step3" style="display: none;">
        <div class="withdraw-header">
            <h2>Informations de paiement</h2>
        </div>

        {{-- Mobile Money --}}
        <div id="mobileMoneyForm">
            <div class="form-group">
                <label>Opérateur</label>
                <select class="form-control" id="mobileOperator">
                    <option value="orange_money">Orange Money</option>
                    <option value="wave">Wave</option>
                    <option value="free_money">Free Money</option>
                </select>
            </div>

            <div class="form-group">
                <label>Numéro de téléphone</label>
                <input type="tel" class="form-control" id="mobileNumber" placeholder="07 XX XX XX XX">
            </div>
        </div>

        {{-- Virement bancaire --}}
        <div id="bankForm" style="display: none;">
            <div class="form-group">
                <label>Nom du bénéficiaire</label>
                <input type="text" class="form-control" id="accountName" placeholder="Nom complet">
            </div>

            <div class="form-group">
                <label>IBAN / Numéro de compte</label>
                <input type="text" class="form-control" id="accountNumber" placeholder="FR14 2004 1010 0505 0001 3M02 606">
            </div>

            <div class="form-group">
                <label>Nom de la banque</label>
                <input type="text" class="form-control" id="bankName" placeholder="Ex: Ecobank">
            </div>
        </div>

        <div class="form-group">
            <label>Motif du retrait (optionnel)</label>
            <textarea class="form-control" id="withdrawReason" rows="2" placeholder="Pourquoi retirez-vous ces fonds ?"></textarea>
        </div>

        <button type="button" class="btn btn-primary btn-block btn-lg" onclick="submitWithdrawal()">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Confirmer la demande
        </button>
        <button type="button" class="btn btn-secondary btn-block" onclick="backToStep2()">
            Retour
        </button>
    </div>

    {{-- Étape 4: Confirmation --}}
    <div class="withdraw-card card-success" id="step4" style="display: none;">
        <div class="success-icon-large">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="48" height="48">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h2>Demande envoyée !</h2>
        <p>Votre demande de retrait est en attente de validation.</p>

        <div class="withdraw-details">
            <div class="detail-row">
                <span>Montant</span>
                <strong id="finalAmount">0 FCFA</strong>
            </div>
            <div class="detail-row">
                <span>Référence</span>
                <code id="finalRef">-</code>
            </div>
            <div class="detail-row">
                <span>Statut</span>
                <span class="badge pending">En attente</span>
            </div>
        </div>

        <div class="info-box">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p>Le traitement peut prendre jusqu'à 48 heures ouvrées.</p>
        </div>

        <a href="{{ route('client.wallet.show') }}" class="btn btn-primary btn-block">
            Retour au portefeuille
        </a>
    </div>

</div>

@endsection

@section('scripts')
<script>
let withdrawAmount = 0;
let withdrawFee = 0;
let withdrawNet = 0;
let withdrawMethod = 'mobile_money';

document.addEventListener('DOMContentLoaded', function() {
    setupListeners();
});

function setupListeners() {
    const input = document.getElementById('withdrawAmount');
    
    input.addEventListener('input', function() {
        calculateWithdrawal(this.value);
    });

    // Toggle méthodes
    document.querySelectorAll('input[name="withdraw_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            withdrawMethod = this.value;
            togglePaymentForm();
        });
    });
}

function calculateWithdrawal(value) {
    const amount = parseInt(value) || 0;
    const btn = document.getElementById('btnContinue');
    const maxBalance = {{ $wallet->balance }};
    
    if (amount < 5000 || amount > maxBalance) {
        btn.disabled = true;
        return;
    }

    withdrawAmount = amount;
    // Frais: 2% avec minimum 500 FCFA
    withdrawFee = Math.max(Math.round(amount * 0.02), 500);
    withdrawNet = amount - withdrawFee;

    btn.disabled = false;
}

function setMaxAmount() {
    const max = {{ $wallet->balance }};
    document.getElementById('withdrawAmount').value = max;
    calculateWithdrawal(max);
}

function goToStep2() {
    document.getElementById('summaryAmount').textContent = withdrawAmount.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('summaryFee').textContent = withdrawFee.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('summaryNet').textContent = withdrawNet.toLocaleString('fr-FR') + ' FCFA';

    document.getElementById('step1').style.display = 'none';
    document.getElementById('step2').style.display = 'block';
}

function backToStep1() {
    document.getElementById('step2').style.display = 'none';
    document.getElementById('step1').style.display = 'block';
}

function goToStep3() {
    document.getElementById('step2').style.display = 'none';
    document.getElementById('step3').style.display = 'block';
    togglePaymentForm();
}

function backToStep2() {
    document.getElementById('step3').style.display = 'none';
    document.getElementById('step2').style.display = 'block';
}

function togglePaymentForm() {
    const isMobile = withdrawMethod === 'mobile_money';
    document.getElementById('mobileMoneyForm').style.display = isMobile ? 'block' : 'none';
    document.getElementById('bankForm').style.display = isMobile ? 'none' : 'block';
}

async function submitWithdrawal() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    // Validation
    let paymentDetails = {};
    
    if (withdrawMethod === 'mobile_money') {
        const operator = document.getElementById('mobileOperator').value;
        const number = document.getElementById('mobileNumber').value;
        if (!number || number.length < 10) {
            alert('Veuillez saisir un numéro de téléphone valide');
            return;
        }
        paymentDetails = { operator, number };
    } else {
        const accountName = document.getElementById('accountName').value;
        const accountNumber = document.getElementById('accountNumber').value;
        const bankName = document.getElementById('bankName').value;
        if (!accountName || !accountNumber) {
            alert('Veuillez remplir tous les champs bancaires');
            return;
        }
        paymentDetails = { account_name: accountName, account_number: accountNumber, bank_name: bankName };
    }

    try {
        const response = await fetch('{{ route('client.wallet.withdraw') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                amount: withdrawAmount,
                method: withdrawMethod,
                payment_details: paymentDetails,
                reason: document.getElementById('withdrawReason').value
            })
        });

        const data = await response.json();

        if (data.success) {
            document.getElementById('finalAmount').textContent = withdrawNet.toLocaleString('fr-FR') + ' FCFA';
            document.getElementById('finalRef').textContent = data.reference;
            
            document.getElementById('step3').style.display = 'none';
            document.getElementById('step4').style.display = 'block';
        } else {
            alert(data.message || 'Erreur lors de la demande');
        }

    } catch (error) {
        console.error(error);
        alert('Erreur de connexion');
    }
}
</script>
@endsection

@section('styles')
<style>
    .withdraw-container {
        max-width: 500px;
        margin: 0 auto;
        padding: 16px;
        padding-bottom: 100px;
    }

    .alert {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 16px;
        border-radius: 12px;
        margin-bottom: 16px;
    }

    .alert-warning {
        background: #fef3c7;
        border: 1px solid #fcd34d;
        color: #92400e;
    }

    .withdraw-card {
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

    .withdraw-header {
        margin-bottom: 24px;
    }

    .withdraw-header h2 {
        font-size: 1.25rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 8px 0;
    }

    .subtitle {
        font-size: 0.9375rem;
        color: #64748b;
        margin: 0;
    }

    .subtitle strong {
        color: #0f172a;
    }

    /* Amount Section */
    .amount-section {
        margin-bottom: 20px;
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
    }

    .amount-field:focus {
        outline: none;
        border-color: #3b82f6;
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
        margin: 0 0 12px 0;
    }

    .btn-text {
        background: none;
        border: none;
        color: #3b82f6;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        padding: 0;
    }

    .withdraw-info {
        background: #eff6ff;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 24px;
    }

    .info-item {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 0.875rem;
        color: #1e40af;
    }

    /* Withdraw Methods */
    .withdraw-methods {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 24px;
    }

    .method-option {
        cursor: pointer;
    }

    .method-option input {
        display: none;
    }

    .method-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        transition: all 0.2s;
    }

    .method-option input:checked + .method-card {
        border-color: #3b82f6;
        background: #eff6ff;
    }

    .method-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .method-icon.orange {
        background: #fff7ed;
        color: #ea580c;
    }

    .method-icon.blue {
        background: #eff6ff;
        color: #2563eb;
    }

    .method-info h4 {
        font-size: 1rem;
        font-weight: 600;
        color: #0f172a;
        margin: 0 0 4px 0;
    }

    .method-info p {
        font-size: 0.875rem;
        color: #64748b;
        margin: 0;
    }

    .method-check {
        margin-left: auto;
        width: 24px;
        height: 24px;
        border: 2px solid #e2e8f0;
        border-radius: 50%;
    }

    .method-option input:checked + .method-card .method-check {
        background: #3b82f6;
        border-color: #3b82f6;
    }

    /* Summary */
    .withdraw-summary {
        background: #f8fafc;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 24px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        font-size: 0.9375rem;
        color: #64748b;
    }

    .summary-row.total {
        border-top: 1px solid #e2e8f0;
        margin-top: 8px;
        padding-top: 12px;
    }

    .summary-row.total strong {
        font-size: 1.25rem;
        color: #059669;
    }

    /* Forms */
    .form-group {
        margin-bottom: 16px;
    }

    .form-group label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 6px;
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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
        width: 100%;
        margin-bottom: 12px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #1e40af, #3b82f6);
        color: white;
    }

    .btn-secondary {
        background: #f1f5f9;
        color: #475569;
    }

    .btn-lg {
        padding: 16px 24px;
    }

    .btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Success */
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

    .card-success h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 8px 0;
    }

    .card-success > p {
        color: #64748b;
        margin: 0 0 24px 0;
    }

    .withdraw-details {
        background: #f8fafc;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        text-align: left;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #e2e8f0;
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge.pending {
        background: #fef3c7;
        color: #92400e;
    }

    .info-box {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #eff6ff;
        padding: 16px;
        border-radius: 12px;
        margin-bottom: 20px;
        font-size: 0.875rem;
        color: #1e40af;
    }
</style>
@endsection