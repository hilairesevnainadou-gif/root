@extends('layouts.app')

@section('title', 'Mon portefeuille')
@section('header-title', 'Mon portefeuille')

@section('header-action')
    <button type="button" class="btn btn-primary" onclick="openDepositModal()">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Alimenter
    </button>
@endsection

@section('content')

<div class="wallet-show">

    {{-- Carte principale du solde --}}
    <div class="balance-card">
        <div class="balance-header">
            <div class="balance-info">
                <span class="balance-label">Solde disponible</span>
                <h2 class="balance-value">{{ number_format($wallet->balance, 0, ',', ' ') }} <small>FCFA</small></h2>
                <span class="wallet-number">{{ $wallet->wallet_number }}</span>
            </div>
            <div class="balance-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="48" height="48">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
        </div>

        <div class="balance-stats">
            <div class="stat-item">
                <span class="stat-value text-success">+{{ number_format($stats['total_deposits'], 0, ',', ' ') }} FCFA</span>
                <span class="stat-label">Total dépôts</span>
            </div>
            <div class="stat-item">
                <span class="stat-value text-danger">-{{ number_format($stats['total_withdrawals'], 0, ',', ' ') }} FCFA</span>
                <span class="stat-label">Total retraits</span>
            </div>
            <div class="stat-item">
                <span class="stat-value">{{ $stats['pending_transactions'] }}</span>
                <span class="stat-label">En attente</span>
            </div>
        </div>

        <div class="balance-actions">
            <button type="button" class="btn btn-light" onclick="openDepositModal()">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Déposer
            </button>
            <a href="{{ route('client.wallet.transactions') }}" class="btn btn-secondary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                Historique complet
            </a>
        </div>
    </div>

    {{-- Informations du compte --}}
    <div class="info-grid">
        <div class="info-card">
            <h3>Informations du compte</h3>
            <div class="info-list">
                <div class="info-item">
                    <span class="info-label">Numéro de compte</span>
                    <span class="info-value">{{ $wallet->wallet_number }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Devise</span>
                    <span class="info-value">{{ $wallet->currency }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Statut</span>
                    <span class="badge badge-{{ $wallet->status }}">
                        {{ $wallet->status === 'active' ? 'Actif' : ($wallet->status === 'suspended' ? 'Suspendu' : 'Fermé') }}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Activé le</span>
                    <span class="info-value">{{ $wallet->activated_at?->format('d/m/Y') ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Dernière transaction</span>
                    <span class="info-value">{{ $wallet->last_transaction_at?->diffForHumans() ?? 'Jamais' }}</span>
                </div>
            </div>
        </div>

        {{-- Méthodes de paiement rapide --}}
        <div class="info-card">
            <h3>Modes de dépôt</h3>
            <div class="payment-methods">
                <div class="payment-method">
                    <div class="pm-icon" style="background: #ff6b00;">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="white">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                    <span>Orange Money</span>
                </div>
                <div class="payment-method">
                    <div class="pm-icon" style="background: #009688;">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="white">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                    <span>Wave</span>
                </div>
                <div class="payment-method">
                    <div class="pm-icon" style="background: #1a73e8;">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="white">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                    <span>Free Money</span>
                </div>
                <div class="payment-method">
                    <div class="pm-icon" style="background: #635bff;">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="white">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                    <span>Carte bancaire</span>
                </div>
            </div>
            <button type="button" class="btn btn-outline btn-block mt-3" onclick="openDepositModal()">
                Effectuer un dépôt
            </button>
        </div>
    </div>

    {{-- Transactions récentes --}}
    <div class="card">
        <div class="card-header">
            <h2 class="section-title">Transactions récentes</h2>
            <a href="{{ route('client.wallet.transactions') }}" class="btn btn-sm btn-secondary">
                Voir tout
            </a>
        </div>

        @if($recentTransactions->count() > 0)
            <div class="transactions-list">
                @foreach($recentTransactions as $transaction)
                <div class="transaction-item {{ $transaction->type }} {{ $transaction->status }}">
                    <div class="transaction-icon">
                        @if($transaction->type === 'credit')
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                            </svg>
                        @elseif($transaction->type === 'debit')
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                            </svg>
                        @elseif($transaction->type === 'payment')
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a1 1 0 11-2 0 1 1 0 012 0z"/>
                            </svg>
                        @else
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                        @endif
                    </div>

                    <div class="transaction-info">
                        <span class="transaction-type">{{ $transaction->getTypeLabel() }}</span>
                        <span class="transaction-desc">{{ $transaction->description ?? 'Transaction sans description' }}</span>
                        <span class="transaction-date">{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
                    </div>

                    <div class="transaction-amount">
                        <span class="amount {{ $transaction->type === 'credit' ? 'positive' : ($transaction->type === 'debit' ? 'negative' : '') }}">
                            {{ $transaction->type === 'credit' ? '+' : ($transaction->type === 'debit' ? '-' : '') }}
                            {{ number_format($transaction->amount, 0, ',', ' ') }} FCFA
                        </span>
                        @if($transaction->fee > 0)
                            <span class="fee">Frais: {{ number_format($transaction->fee, 0, ',', ' ') }} FCFA</span>
                        @endif
                    </div>

                    <div class="transaction-status">
                        <span class="badge badge-{{ $transaction->status }}">
                            {{ $transaction->getStatusLabel() }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="48" height="48">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3>Aucune transaction</h3>
                <p>Votre historique de transactions est vide.</p>
                <button type="button" class="btn btn-primary" onclick="openDepositModal()">
                    Effectuer un premier dépôt
                </button>
            </div>
        @endif
    </div>

</div>

{{-- Modal de dépôt --}}
<div id="depositModal" class="modal" style="display: none;">
    <div class="modal-overlay" onclick="closeDepositModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Alimenter mon portefeuille</h3>
            <button type="button" class="btn-close" onclick="closeDepositModal()">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <form action="{{ route('client.wallet.deposit') }}" method="POST" id="depositForm">
            @csrf
            
            <div class="form-group">
                <label class="form-label">Montant à déposer (FCFA)</label>
                <div class="amount-input-wrapper">
                    <input type="number" name="amount" id="depositAmount" class="form-control" 
                           placeholder="10000" min="1000" step="100" required>
                    <span class="amount-currency">FCFA</span>
                </div>
                <small class="form-hint">Minimum: 1 000 FCFA</small>
            </div>

            <div class="form-group">
                <label class="form-label">Mode de paiement</label>
                <div class="payment-options">
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="orange_money" checked>
                        <span class="option-content">
                            <span class="option-icon" style="background: #ff6b00;"></span>
                            <span class="option-label">Orange Money</span>
                        </span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="wave">
                        <span class="option-content">
                            <span class="option-icon" style="background: #009688;"></span>
                            <span class="option-label">Wave</span>
                        </span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="free_money">
                        <span class="option-content">
                            <span class="option-icon" style="background: #1a73e8;"></span>
                            <span class="option-label">Free Money</span>
                        </span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="card">
                        <span class="option-content">
                            <span class="option-icon" style="background: #635bff;"></span>
                            <span class="option-label">Carte bancaire</span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="fee-preview" id="feePreview">
                <div class="fee-row">
                    <span>Montant</span>
                    <strong id="previewAmount">0 FCFA</strong>
                </div>
                <div class="fee-row">
                    <span>Frais</span>
                    <strong id="previewFee">0 FCFA</strong>
                </div>
                <div class="fee-row total">
                    <span>Total à payer</span>
                    <strong id="previewTotal">0 FCFA</strong>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeDepositModal()">Annuler</button>
                <button type="submit" class="btn btn-primary btn-block">
                    Procéder au paiement
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
function openDepositModal() {
    document.getElementById('depositModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeDepositModal() {
    document.getElementById('depositModal').style.display = 'none';
    document.body.style.overflow = '';
}

// Calcul des frais en temps réel
document.getElementById('depositAmount')?.addEventListener('input', function() {
    const amount = parseFloat(this.value) || 0;
    const method = document.querySelector('input[name="payment_method"]:checked')?.value || 'orange_money';
    
    let feeRate = 0.01; // 1% par défaut
    if (method === 'card') feeRate = 0.025; // 2.5% pour carte
    
    const fee = Math.round(amount * feeRate);
    const total = amount + fee;

    document.getElementById('previewAmount').textContent = amount.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('previewFee').textContent = fee.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('previewTotal').textContent = total.toLocaleString('fr-FR') + ' FCFA';
    
    document.getElementById('feePreview').style.display = amount > 0 ? 'block' : 'none';
});

// Mettre à jour les frais quand on change de méthode
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('depositAmount')?.dispatchEvent(new Event('input'));
    });
});

// Fermer avec Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeDepositModal();
});
</script>
@endsection

@section('styles')
<style>
    .wallet-show {
        max-width: 1000px;
        margin: 0 auto;
    }

    /* Balance Card */
    .balance-card {
        background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%);
        color: white;
        border-radius: var(--radius-lg);
        padding: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 10px 40px rgba(37, 99, 235, 0.2);
    }

    .balance-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 2rem;
    }

    .balance-info {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .balance-label {
        font-size: 0.9rem;
        opacity: 0.9;
    }

    .balance-value {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0;
        line-height: 1;
    }

    .balance-value small {
        font-size: 1rem;
        font-weight: 500;
    }

    .wallet-number {
        font-family: monospace;
        font-size: 0.85rem;
        opacity: 0.8;
        background: rgba(255,255,255,0.1);
        padding: 0.25rem 0.75rem;
        border-radius: var(--radius-sm);
        margin-top: 0.5rem;
    }

    .balance-icon {
        opacity: 0.2;
    }

    .balance-stats {
        display: flex;
        gap: 2rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid rgba(255,255,255,0.2);
    }

    .stat-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .stat-item .stat-value {
        font-size: 1.1rem;
        font-weight: 600;
    }

    .stat-item .stat-value.text-success {
        color: #86efac;
    }

    .stat-item .stat-value.text-danger {
        color: #fca5a5;
    }

    .stat-item .stat-label {
        font-size: 0.75rem;
        opacity: 0.8;
    }

    .balance-actions {
        display: flex;
        gap: 0.75rem;
    }

    .btn-light {
        background: rgba(255,255,255,0.9);
        color: var(--primary);
        border: none;
    }

    .btn-light:hover {
        background: white;
    }

    /* Info Grid */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .info-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 1.5rem;
    }

    .info-card h3 {
        font-size: 1rem;
        font-weight: 600;
        margin: 0 0 1rem;
        color: var(--text);
    }

    .info-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--border);
    }

    .info-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .info-label {
        font-size: 0.85rem;
        color: var(--text-muted);
    }

    .info-value {
        font-size: 0.9rem;
        font-weight: 500;
        color: var(--text);
    }

    /* Payment Methods */
    .payment-methods {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }

    .payment-method {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        background: var(--bg);
        border-radius: var(--radius);
    }

    .pm-icon {
        width: 40px;
        height: 40px;
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .payment-method span {
        font-size: 0.9rem;
        font-weight: 500;
    }

    /* Transactions List */
    .transactions-list {
        display: flex;
        flex-direction: column;
    }

    .transaction-item {
        display: grid;
        grid-template-columns: auto 1fr auto auto;
        gap: 1rem;
        align-items: center;
        padding: 1rem;
        border-bottom: 1px solid var(--border);
        transition: background 0.2s;
    }

    .transaction-item:hover {
        background: var(--bg);
    }

    .transaction-item:last-child {
        border-bottom: none;
    }

    .transaction-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--bg);
        color: var(--text-muted);
    }

    .transaction-item.credit .transaction-icon {
        background: #dcfce7;
        color: #166534;
    }

    .transaction-item.debit .transaction-icon {
        background: #fee2e2;
        color: #991b1b;
    }

    .transaction-item.payment .transaction-icon {
        background: #dbeafe;
        color: #1e40af;
    }

    .transaction-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .transaction-type {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text);
    }

    .transaction-desc {
        font-size: 0.8rem;
        color: var(--text-muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 200px;
    }

    .transaction-date {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .transaction-amount {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.25rem;
    }

    .transaction-amount .amount {
        font-size: 1rem;
        font-weight: 600;
    }

    .transaction-amount .amount.positive {
        color: #059669;
    }

    .transaction-amount .amount.negative {
        color: #dc2626;
    }

    .transaction-amount .fee {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    /* Modal */
    .modal {
        position: fixed;
        inset: 0;
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.5);
    }

    .modal-content {
        position: relative;
        background: var(--surface);
        border-radius: var(--radius-lg);
        width: 90%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        border-bottom: 1px solid var(--border);
    }

    .modal-header h3 {
        margin: 0;
        font-size: 1.25rem;
    }

    .btn-close {
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 0.25rem;
    }

    .btn-close:hover {
        color: var(--text);
    }

    #depositForm {
        padding: 1.5rem;
    }

    .payment-options {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }

    .payment-option {
        cursor: pointer;
    }

    .payment-option input {
        display: none;
    }

    .option-content {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        border: 2px solid var(--border);
        border-radius: var(--radius);
        transition: all 0.2s;
    }

    .payment-option input:checked + .option-content {
        border-color: var(--primary);
        background: #eff6ff;
    }

    .option-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
    }

    .option-label {
        font-size: 0.9rem;
        font-weight: 500;
    }

    .fee-preview {
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 1rem;
        margin: 1.5rem 0;
        display: none;
    }

    .fee-row {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        font-size: 0.9rem;
    }

    .fee-row.total {
        border-top: 1px solid var(--border);
        margin-top: 0.5rem;
        padding-top: 0.75rem;
        font-size: 1.1rem;
        font-weight: 600;
    }

    .modal-actions {
        display: flex;
        gap: 0.75rem;
    }

    .btn-block {
        flex: 1;
    }

    @media (max-width: 640px) {
        .transaction-item {
            grid-template-columns: auto 1fr auto;
            grid-template-rows: auto auto;
        }

        .transaction-status {
            grid-column: 3;
            grid-row: 1 / 3;
        }

        .balance-stats {
            gap: 1rem;
        }

        .payment-options {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection
