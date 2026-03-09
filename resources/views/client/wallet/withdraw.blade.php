@extends('layouts.app')

@section('title', 'Retrait - Mon portefeuille')
@section('header-title', 'Nouveau retrait')

@section('content')

<div class="withdraw-container">

    {{-- En-tête avec solde disponible --}}
    <div class="withdraw-header-card">
        <div class="available-balance">
            <span class="balance-label">Solde disponible</span>
            <div class="balance-display">
                <span class="balance-amount">{{ number_format($wallet->balance, 0, ',', ' ') }}</span>
                <span class="balance-currency">FCFA</span>
            </div>
            <span class="wallet-number">{{ $wallet->wallet_number }}</span>
        </div>
    </div>

    {{-- Formulaire de retrait --}}
    <div class="withdraw-form-card">
        <div class="form-header">
            <h2>Montant à retirer</h2>
            <p class="form-subtitle">Minimum 5 000 FCFA • Maximum 1 000 000 FCFA</p>
        </div>

        {{-- Montant --}}
        <div class="amount-section">
            <div class="amount-input-wrapper">
                <span class="amount-prefix">FCFA</span>
                <input type="number" 
                       id="withdrawAmount" 
                       class="amount-input" 
                       placeholder="0"
                       min="5000"
                       max="1000000"
                       step="1000"
                       oninput="calculateWithdrawal(this.value)">
            </div>
            <p class="amount-hint" id="amountHint">Saisissez un montant entre 5 000 et 1 000 000 FCFA</p>
        </div>

        {{-- Calcul des frais --}}
        <div class="fee-calculation" id="feeCalculation" style="display: none;">
            <div class="calc-row">
                <span>Montant demandé</span>
                <strong id="calcAmount">0 FCFA</strong>
            </div>
            <div class="calc-row">
                <span>Frais de retrait (2%)</span>
                <span id="calcFee">0 FCFA</span>
            </div>
            <div class="calc-divider"></div>
            <div class="calc-row total">
                <span>Total débité</span>
                <strong id="calcTotal" class="text-danger">0 FCFA</strong>
            </div>
            <div class="calc-row net">
                <span>Montant net reçu</span>
                <strong id="calcNet" class="text-success">0 FCFA</strong>
            </div>
        </div>

        {{-- Méthode de retrait --}}
        <div class="method-section" id="methodSection" style="display: none;">
            <h3>Méthode de retrait</h3>
            
            <div class="method-tabs">
                <button type="button" class="method-tab active" onclick="selectMethod('mobile_money')" id="tabMobile">
                    <div class="tab-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="tab-content">
                        <strong>Mobile Money</strong>
                        <span>MTN, Moov, Celtiis</span>
                    </div>
                </button>
                
                <button type="button" class="method-tab" onclick="selectMethod('bank_transfer')" id="tabBank">
                    <div class="tab-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <div class="tab-content">
                        <strong>Virement bancaire</strong>
                        <span>Compte bancaire</span>
                    </div>
                </button>
            </div>

            {{-- Formulaire Mobile Money --}}
            <div class="method-form active" id="formMobile">
                <div class="form-group">
                    <label>Opérateur</label>
                    <select id="mobileOperator" class="form-select">
                        <option value="">Choisir...</option>
                        <option value="mtn">MTN Mobile Money</option>
                        <option value="moov">Moov Money</option>
                        <option value="celtiis">Celtiis Cash</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Numéro de téléphone</label>
                    <input type="tel" 
                           id="mobileNumber" 
                           class="form-input" 
                           placeholder="Ex: 22997000000"
                           maxlength="12">
                    <span class="input-hint">Format: 229XXXXXXXX</span>
                </div>
            </div>

            {{-- Formulaire Virement Bancaire --}}
            <div class="method-form" id="formBank" style="display: none;">
                <div class="form-group">
                    <label>Nom du bénéficiaire</label>
                    <input type="text" 
                           id="bankAccountName" 
                           class="form-input" 
                           placeholder="Nom complet sur le compte">
                </div>
                
                <div class="form-group">
                    <label>Nom de la banque</label>
                    <input type="text" 
                           id="bankName" 
                           class="form-input" 
                           placeholder="Ex: Ecobank, BOA, etc.">
                </div>
                
                <div class="form-group">
                    <label>Numéro de compte</label>
                    <input type="text" 
                           id="bankAccountNumber" 
                           class="form-input" 
                           placeholder="Numéro de compte bancaire">
                </div>
            </div>

            {{-- Motif optionnel --}}
            <div class="form-group">
                <label>Motif du retrait <span class="optional">(optionnel)</span></label>
                <textarea id="withdrawReason" 
                          class="form-textarea" 
                          rows="2" 
                          placeholder="Expliquez brièvement la raison de ce retrait..."></textarea>
            </div>
        </div>

        {{-- Actions --}}
        <div class="form-actions" id="formActions" style="display: none;">
            <button type="button" class="btn-submit" id="btnSubmit" onclick="submitWithdrawal()">
                <span class="btn-content">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a1 1 0 11-2 0 1 1 0 012 0z"/>
                    </svg>
                    Confirmer le retrait
                </span>
                <span class="btn-loader" style="display: none;">
                    <span class="spinner"></span>
                </span>
            </button>
            
            <a href="{{ route('client.wallet.show') }}" class="btn-cancel">Annuler</a>
        </div>
    </div>

    {{-- Informations --}}
    <div class="info-card">
        <div class="info-header">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Informations importantes</span>
        </div>
        <ul class="info-list">
            <li>Les retraits sont traités sous 24-48h ouvrables</li>
            <li>Frais de retrait : 2% (minimum 500 FCFA)</li>
            <li>Maximum 3 demandes de retrait en attente simultanées</li>
            <li>Vérifiez attentivement vos coordonnées bancaires</li>
        </ul>
    </div>

</div>

{{-- Modal de confirmation --}}
<div id="confirmModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="closeModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="32" height="32">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h3>Confirmer le retrait</h3>
        </div>
        
        <div class="modal-body">
            <div class="confirm-detail">
                <span>Montant net</span>
                <strong id="confirmNet">0 FCFA</strong>
            </div>
            <div class="confirm-detail">
                <span>Frais (2%)</span>
                <span id="confirmFee">0 FCFA</span>
            </div>
            <div class="confirm-detail total">
                <span>Total débité</span>
                <strong id="confirmTotal" class="text-danger">0 FCFA</strong>
            </div>
            
            <div class="confirm-method" id="confirmMethod">
                {{-- Rempli dynamiquement --}}
            </div>
        </div>
        
        <div class="modal-actions">
            <button type="button" class="btn-modal-secondary" onclick="closeModal()">Modifier</button>
            <button type="button" class="btn-modal-primary" id="btnConfirmFinal" onclick="confirmWithdrawal()">
                <span class="btn-content">Confirmer</span>
                <span class="btn-loader" style="display: none;">
                    <span class="spinner"></span>
                </span>
            </button>
        </div>
    </div>
</div>

{{-- Toast notification --}}
<div id="toast" class="toast" style="display: none;">
    <span id="toastMessage">Message</span>
</div>

@endsection

@section('scripts')
<script>
let currentMethod = 'mobile_money';
let currentAmount = 0;
let currentFee = 0;
let currentTotal = 0;
let currentNet = 0;

function calculateWithdrawal(value) {
    const amount = parseInt(value) || 0;
    const minAmount = 5000;
    const maxAmount = 1000000;
    
    // Validation
    if (amount < minAmount) {
        document.getElementById('amountHint').textContent = `Minimum ${minAmount.toLocaleString('fr-FR')} FCFA requis`;
        document.getElementById('amountHint').classList.add('error');
        hideCalculation();
        return;
    }
    
    if (amount > maxAmount) {
        document.getElementById('amountHint').textContent = `Maximum ${maxAmount.toLocaleString('fr-FR')} FCFA`;
        document.getElementById('amountHint').classList.add('error');
        hideCalculation();
        return;
    }
    
    if (amount > {{ $wallet->balance }}) {
        document.getElementById('amountHint').textContent = 'Solde insuffisant';
        document.getElementById('amountHint').classList.add('error');
        hideCalculation();
        return;
    }
    
    document.getElementById('amountHint').textContent = 'Montant valide';
    document.getElementById('amountHint').classList.remove('error');
    
    // Calcul
    currentAmount = amount;
    currentFee = Math.max(Math.round(amount * 0.02), 500);
    currentTotal = amount + currentFee;
    currentNet = amount - currentFee;
    
    // Affichage
    document.getElementById('calcAmount').textContent = amount.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('calcFee').textContent = currentFee.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('calcTotal').textContent = currentTotal.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('calcNet').textContent = currentNet.toLocaleString('fr-FR') + ' FCFA';
    
    document.getElementById('feeCalculation').style.display = 'block';
    document.getElementById('methodSection').style.display = 'block';
    document.getElementById('formActions').style.display = 'flex';
}

function hideCalculation() {
    document.getElementById('feeCalculation').style.display = 'none';
    document.getElementById('methodSection').style.display = 'none';
    document.getElementById('formActions').style.display = 'none';
}

function selectMethod(method) {
    currentMethod = method;
    
    // Tabs
    document.querySelectorAll('.method-tab').forEach(tab => tab.classList.remove('active'));
    document.getElementById(method === 'mobile_money' ? 'tabMobile' : 'tabBank').classList.add('active');
    
    // Forms
    document.getElementById('formMobile').style.display = method === 'mobile_money' ? 'block' : 'none';
    document.getElementById('formBank').style.display = method === 'bank_transfer' ? 'block' : 'none';
}

function submitWithdrawal() {
    // Validation
    if (!currentAmount || currentAmount < 5000) {
        showToast('Veuillez saisir un montant valide');
        return;
    }
    
    let paymentDetails = {};
    let isValid = false;
    
    if (currentMethod === 'mobile_money') {
        const operator = document.getElementById('mobileOperator').value;
        const number = document.getElementById('mobileNumber').value.trim();
        
        if (!operator) {
            showToast('Veuillez sélectionner un opérateur');
            return;
        }
        if (!number || number.length < 10) {
            showToast('Veuillez saisir un numéro valide');
            return;
        }
        
        paymentDetails = { operator, number };
        isValid = true;
        
        // Afficher dans le modal
        document.getElementById('confirmMethod').innerHTML = `
            <div class="method-display">
                <span class="method-label">Mobile Money</span>
                <span class="method-value">${operator.toUpperCase()} - ${number}</span>
            </div>
        `;
        
    } else {
        const accountName = document.getElementById('bankAccountName').value.trim();
        const bankName = document.getElementById('bankName').value.trim();
        const accountNumber = document.getElementById('bankAccountNumber').value.trim();
        
        if (!accountName || !bankName || !accountNumber) {
            showToast('Veuillez remplir tous les champs bancaires');
            return;
        }
        
        paymentDetails = { account_name: accountName, bank_name: bankName, account_number: accountNumber };
        isValid = true;
        
        // Afficher dans le modal
        document.getElementById('confirmMethod').innerHTML = `
            <div class="method-display">
                <span class="method-label">Virement bancaire</span>
                <span class="method-value">${bankName}</span>
                <span class="method-subvalue">${accountName} - ${accountNumber}</span>
            </div>
        `;
    }
    
    if (!isValid) return;
    
    // Remplir le modal
    document.getElementById('confirmNet').textContent = currentNet.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('confirmFee').textContent = currentFee.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('confirmTotal').textContent = currentTotal.toLocaleString('fr-FR') + ' FCFA';
    
    // Afficher le modal
    document.getElementById('confirmModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('confirmModal').style.display = 'none';
}

async function confirmWithdrawal() {
    const btn = document.getElementById('btnConfirmFinal');
    btn.disabled = true;
    btn.querySelector('.btn-content').style.display = 'none';
    btn.querySelector('.btn-loader').style.display = 'flex';
    
    let paymentDetails = {};
    
    if (currentMethod === 'mobile_money') {
        paymentDetails = {
            operator: document.getElementById('mobileOperator').value,
            number: document.getElementById('mobileNumber').value.trim()
        };
    } else {
        paymentDetails = {
            account_name: document.getElementById('bankAccountName').value.trim(),
            bank_name: document.getElementById('bankName').value.trim(),
            account_number: document.getElementById('bankAccountNumber').value.trim()
        };
    }
    
    try {
        const response = await fetch('{{ route('client.wallet.withdraw.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                amount: currentAmount,
                method: currentMethod,
                payment_details: paymentDetails,
                reason: document.getElementById('withdrawReason').value.trim()
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Demande de retrait soumise avec succès !');
            setTimeout(() => {
                window.location.href = '{{ route('client.wallet.show') }}?success=withdrawal';
            }, 1500);
        } else {
            throw new Error(data.message || 'Erreur lors de la soumission');
        }
        
    } catch (error) {
        showToast(error.message || 'Erreur de connexion');
        btn.disabled = false;
        btn.querySelector('.btn-content').style.display = 'block';
        btn.querySelector('.btn-loader').style.display = 'none';
    }
}

function showToast(message) {
    const toast = document.getElementById('toast');
    document.getElementById('toastMessage').textContent = message;
    toast.style.display = 'flex';
    setTimeout(() => {
        toast.style.display = 'none';
    }, 3000);
}

// Fermer le modal avec Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>
@endsection

@section('styles')
<style>
    .withdraw-container {
        padding: 16px;
        padding-bottom: 100px;
        max-width: 600px;
        margin: 0 auto;
    }

    /* Header Card */
    .withdraw-header-card {
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        border-radius: 20px;
        padding: 24px;
        margin-bottom: 20px;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .withdraw-header-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        border-radius: 50%;
    }

    .available-balance {
        position: relative;
        z-index: 1;
    }

    .balance-label {
        font-size: 0.875rem;
        opacity: 0.85;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: block;
        margin-bottom: 8px;
    }

    .balance-display {
        display: flex;
        align-items: baseline;
        gap: 8px;
        margin-bottom: 8px;
    }

    .balance-amount {
        font-size: 2.5rem;
        font-weight: 800;
        line-height: 1;
    }

    .balance-currency {
        font-size: 1.125rem;
        font-weight: 600;
        opacity: 0.9;
    }

    .wallet-number {
        font-family: monospace;
        font-size: 0.75rem;
        opacity: 0.7;
        background: rgba(255,255,255,0.15);
        padding: 4px 12px;
        border-radius: 20px;
    }

    /* Form Card */
    .withdraw-form-card {
        background: white;
        border-radius: 20px;
        padding: 24px;
        margin-bottom: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .form-header {
        margin-bottom: 24px;
    }

    .form-header h2 {
        font-size: 1.25rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 4px 0;
    }

    .form-subtitle {
        font-size: 0.875rem;
        color: #64748b;
        margin: 0;
    }

    /* Amount Section */
    .amount-section {
        margin-bottom: 20px;
    }

    .amount-input-wrapper {
        position: relative;
        margin-bottom: 8px;
    }

    .amount-prefix {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.125rem;
        font-weight: 600;
        color: #64748b;
    }

    .amount-input {
        width: 100%;
        padding: 20px 16px 20px 70px;
        font-size: 2rem;
        font-weight: 700;
        color: #0f172a;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        background: #f8fafc;
        transition: all 0.2s;
    }

    .amount-input:focus {
        outline: none;
        border-color: #3b82f6;
        background: white;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }

    .amount-hint {
        font-size: 0.875rem;
        color: #64748b;
        margin: 0;
        transition: color 0.2s;
    }

    .amount-hint.error {
        color: #dc2626;
    }

    /* Fee Calculation */
    .fee-calculation {
        background: #f8fafc;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 24px;
        border: 1px solid #e2e8f0;
    }

    .calc-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        font-size: 0.9375rem;
    }

    .calc-row span {
        color: #64748b;
    }

    .calc-row strong {
        color: #0f172a;
        font-weight: 600;
    }

    .calc-row.total {
        border-top: 2px solid #e2e8f0;
        margin-top: 8px;
        padding-top: 16px;
    }

    .calc-row.total strong {
        font-size: 1.125rem;
    }

    .calc-row.net strong {
        color: #16a34a;
    }

    .text-danger {
        color: #dc2626 !important;
    }

    .text-success {
        color: #16a34a !important;
    }

    .calc-divider {
        height: 1px;
        background: #e2e8f0;
        margin: 8px 0;
    }

    /* Method Section */
    .method-section h3 {
        font-size: 1rem;
        font-weight: 600;
        color: #0f172a;
        margin: 0 0 16px 0;
    }

    .method-tabs {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 20px;
    }

    .method-tab {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px;
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        cursor: pointer;
        transition: all 0.2s;
        text-align: left;
    }

    .method-tab:hover {
        border-color: #bfdbfe;
    }

    .method-tab.active {
        border-color: #3b82f6;
        background: #eff6ff;
    }

    .tab-icon {
        width: 48px;
        height: 48px;
        background: #f1f5f9;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        flex-shrink: 0;
    }

    .method-tab.active .tab-icon {
        background: #3b82f6;
        color: white;
    }

    .tab-content {
        flex: 1;
    }

    .tab-content strong {
        display: block;
        font-size: 1rem;
        color: #0f172a;
        margin-bottom: 4px;
    }

    .tab-content span {
        font-size: 0.875rem;
        color: #64748b;
    }

    /* Form Groups */
    .method-form {
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-group label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }

    .form-group label .optional {
        font-weight: 400;
        color: #9ca3af;
    }

    .form-select,
    .form-input,
    .form-textarea {
        width: 100%;
        padding: 14px 16px;
        font-size: 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        background: white;
        color: #0f172a;
        transition: all 0.2s;
    }

    .form-select:focus,
    .form-input:focus,
    .form-textarea:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }

    .form-textarea {
        resize: vertical;
        min-height: 80px;
    }

    .input-hint {
        display: block;
        font-size: 0.75rem;
        color: #9ca3af;
        margin-top: 6px;
    }

    /* Form Actions */
    .form-actions {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 24px;
    }

    .btn-submit {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 16px 24px;
        background: linear-gradient(135deg, #dc2626, #b91c1c);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 4px 14px rgba(220, 38, 38, 0.35);
        position: relative;
        overflow: hidden;
    }

    .btn-submit:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(220, 38, 38, 0.45);
    }

    .btn-submit:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .btn-content {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-loader {
        display: flex;
        align-items: center;
        justify-content: center;
        position: absolute;
        inset: 0;
    }

    .spinner {
        width: 24px;
        height: 24px;
        border: 3px solid rgba(255,255,255,0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .btn-cancel {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 14px 24px;
        background: #f8fafc;
        color: #64748b;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s;
    }

    .btn-cancel:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #475569;
    }

    /* Info Card */
    .info-card {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 16px;
        padding: 20px;
    }

    .info-header {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
        color: #1e40af;
        font-weight: 600;
        font-size: 0.9375rem;
    }

    .info-list {
        margin: 0;
        padding-left: 20px;
        font-size: 0.875rem;
        color: #1e40af;
    }

    .info-list li {
        margin-bottom: 8px;
        line-height: 1.5;
    }

    .info-list li:last-child {
        margin-bottom: 0;
    }

    /* Modal */
    .modal {
        position: fixed;
        inset: 0;
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
    }

    .modal-content {
        position: relative;
        background: white;
        border-radius: 24px;
        padding: 24px;
        width: 100%;
        max-width: 400px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        animation: slideUp 0.3s ease;
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .modal-header {
        text-align: center;
        margin-bottom: 20px;
    }

    .modal-icon {
        width: 64px;
        height: 64px;
        background: #fef3c7;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #f59e0b;
        margin: 0 auto 12px;
    }

    .modal-header h3 {
        font-size: 1.25rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }

    .modal-body {
        margin-bottom: 24px;
    }

    .confirm-detail {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        font-size: 0.9375rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .confirm-detail span {
        color: #64748b;
    }

    .confirm-detail strong {
        color: #0f172a;
        font-weight: 600;
    }

    .confirm-detail.total {
        border-top: 2px solid #e2e8f0;
        border-bottom: none;
        margin-top: 8px;
        padding-top: 16px;
    }

    .confirm-detail.total strong {
        font-size: 1.125rem;
    }

    .confirm-method {
        background: #f8fafc;
        border-radius: 12px;
        padding: 16px;
        margin-top: 16px;
    }

    .method-display {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .method-label {
        font-size: 0.75rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .method-value {
        font-size: 1rem;
        font-weight: 600;
        color: #0f172a;
    }

    .method-subvalue {
        font-size: 0.875rem;
        color: #64748b;
    }

    .modal-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .btn-modal-secondary {
        padding: 14px 20px;
        background: #f8fafc;
        color: #64748b;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        font-size: 0.9375rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-modal-secondary:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    .btn-modal-primary {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 14px 20px;
        background: linear-gradient(135deg, #16a34a, #15803d);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 0.9375rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        position: relative;
        overflow: hidden;
    }

    .btn-modal-primary:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(22, 163, 74, 0.35);
    }

    .btn-modal-primary:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    /* Toast */
    .toast {
        position: fixed;
        bottom: 100px;
        left: 50%;
        transform: translateX(-50%);
        background: #0f172a;
        color: white;
        padding: 14px 24px;
        border-radius: 50px;
        font-size: 0.9375rem;
        font-weight: 500;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2);
        z-index: 1001;
        animation: slideUpToast 0.3s ease;
    }

    @keyframes slideUpToast {
        from { opacity: 0; transform: translateX(-50%) translateY(20px); }
        to { opacity: 1; transform: translateX(-50%) translateY(0); }
    }

    @media (max-width: 480px) {
        .withdraw-container {
            padding: 12px;
        }

        .withdraw-header-card,
        .withdraw-form-card {
            padding: 20px;
            border-radius: 16px;
        }

        .balance-amount {
            font-size: 2rem;
        }

        .amount-input {
            font-size: 1.5rem;
            padding: 16px 16px 16px 60px;
        }

        .modal-content {
            padding: 20px;
        }
    }
</style>
@endsection