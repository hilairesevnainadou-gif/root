@extends('layouts.app')

@section('title', 'Mon portefeuille')
@section('header-title', 'Mon portefeuille')

@section('content')

<div class="wallet-container">

    {{-- Carte Solde Principale --}}
    <div class="balance-card-main">
        <div class="balance-header">
            <div>
                <span class="balance-label">Solde disponible</span>
                <h1 class="balance-amount">{{ number_format($wallet->balance, 0, ',', ' ') }} <small>FCFA</small></h1>
                <span class="wallet-number">{{ $wallet->wallet_number }}</span>
            </div>
            <div class="balance-icon-large">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="48" height="48">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
        </div>

        <div class="balance-quick-stats">
            <div class="quick-stat">
                <span class="quick-stat-value text-success">+{{ number_format($stats['total_deposits'], 0, ',', ' ') }}</span>
                <span class="quick-stat-label">Dépôts</span>
            </div>
            <div class="quick-stat">
                <span class="quick-stat-value text-danger">-{{ number_format($stats['total_withdrawals'], 0, ',', ' ') }}</span>
                <span class="quick-stat-label">Retraits</span>
            </div>
            <div class="quick-stat">
                <span class="quick-stat-value">{{ $stats['pending_transactions'] }}</span>
                <span class="quick-stat-label">En attente</span>
            </div>
        </div>

        <div class="balance-actions-main">
            <a href="{{ route('client.wallet.deposit') }}" class="btn-action btn-deposit">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Déposer
            </a>
            <a href="{{ route('client.wallet.withdraw') }}" class="btn-action btn-withdraw">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                </svg>
                Retirer
            </a>
        </div>
    </div>

    {{-- Actions Rapides --}}
    <div class="quick-actions-grid">
        <a href="{{ route('client.wallet.transactions') }}" class="action-card">
            <div class="action-icon blue">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </div>
            <span>Historique</span>
        </a>
        
        <button type="button" class="action-card" onclick="copyWalletNumber()">
            <div class="action-icon green">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
            </div>
            <span>Copier N°</span>
        </button>
    </div>

    {{-- Transactions Récentes --}}
    <div class="section-card">
        <div class="section-header">
            <h2>Transactions récentes</h2>
            <a href="{{ route('client.wallet.transactions') }}" class="link-see-all">Voir tout</a>
        </div>

        @if($recentTransactions->count() > 0)
            <div class="transactions-list-compact">
                @foreach($recentTransactions->take(5) as $transaction)
                <div class="transaction-row {{ $transaction->type }} {{ $transaction->status }}">
                    <div class="transaction-icon-sm">
                        @if($transaction->type === 'credit')
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                            </svg>
                        @elseif($transaction->type === 'debit')
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                            </svg>
                        @else
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                        @endif
                    </div>
                    
                    <div class="transaction-info-sm">
                        <span class="transaction-type-sm">{{ $transaction->getTypeLabel() }}</span>
                        <span class="transaction-desc-sm">{{ Str::limit($transaction->description, 30) }}</span>
                        <span class="transaction-date-sm">{{ $transaction->created_at->diffForHumans() }}</span>
                    </div>

                    <div class="transaction-amount-sm">
                        <span class="amount {{ $transaction->type === 'credit' ? 'positive' : 'negative' }}">
                            {{ $transaction->type === 'credit' ? '+' : '-' }}{{ number_format($transaction->amount, 0, ',', ' ') }}
                        </span>
                        @if($transaction->status === 'pending')
                            <span class="status-badge pending">En attente</span>
                        @elseif($transaction->status === 'failed')
                            <span class="status-badge failed">Échoué</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="empty-state-sm">
                <p>Aucune transaction récente</p>
                <a href="{{ route('client.wallet.deposit') }}" class="btn btn-primary btn-sm">Faire un dépôt</a>
            </div>
        @endif
    </div>

    {{-- Informations du compte --}}
    <div class="section-card">
        <h2 class="section-title-sm">Informations du compte</h2>
        <div class="info-list-compact">
            <div class="info-row">
                <span>Statut</span>
                <span class="badge-status {{ $wallet->status }}">
                    {{ $wallet->status === 'active' ? 'Actif' : ($wallet->status === 'suspended' ? 'Suspendu' : 'Fermé') }}
                </span>
            </div>
            <div class="info-row">
                <span>Devise</span>
                <span>{{ $wallet->currency }}</span>
            </div>
            <div class="info-row">
                <span>Activé le</span>
                <span>{{ $wallet->activated_at?->format('d/m/Y') ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span>Dernière activité</span>
                <span>{{ $wallet->last_transaction_at?->diffForHumans() ?? 'Jamais' }}</span>
            </div>
        </div>
    </div>

</div>

{{-- Toast pour copie --}}
<div id="copyToast" class="toast" style="display: none;">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    <span>Numéro copié !</span>
</div>

@endsection

@section('scripts')
<script>
function copyWalletNumber() {
    const walletNumber = '{{ $wallet->wallet_number }}';
    navigator.clipboard.writeText(walletNumber).then(() => {
        const toast = document.getElementById('copyToast');
        toast.style.display = 'flex';
        setTimeout(() => {
            toast.style.display = 'none';
        }, 2000);
    });
}

// Gestion du paramètre success dans l'URL
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        const toast = document.getElementById('copyToast');
        const message = urlParams.get('success') === 'withdrawal' 
            ? 'Demande de retrait soumise avec succès !' 
            : 'Opération réussie !';
        toast.querySelector('span').textContent = message;
        toast.style.display = 'flex';
        setTimeout(() => {
            toast.style.display = 'none';
        }, 3000);
        
        // Nettoyer l'URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});
</script>
@endsection

@section('styles')
<style>
    .wallet-container {
        padding: 16px;
        padding-bottom: 100px;
        max-width: 600px;
        margin: 0 auto;
    }

    /* Balance Card Main */
    .balance-card-main {
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        color: white;
        border-radius: 24px;
        padding: 28px;
        margin-bottom: 20px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 40px -10px rgba(30, 64, 175, 0.4);
    }

    .balance-card-main::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        border-radius: 50%;
    }

    .balance-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 24px;
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

    .balance-amount {
        font-size: 2.5rem;
        font-weight: 800;
        margin: 0;
        line-height: 1;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .balance-amount small {
        font-size: 1.25rem;
        font-weight: 600;
        opacity: 0.9;
    }

    .wallet-number {
        display: inline-block;
        font-family: monospace;
        font-size: 0.8rem;
        opacity: 0.7;
        background: rgba(255,255,255,0.15);
        padding: 4px 12px;
        border-radius: 20px;
        margin-top: 12px;
    }

    .balance-icon-large {
        opacity: 0.3;
    }

    .balance-quick-stats {
        display: flex;
        gap: 24px;
        margin-bottom: 24px;
        padding-bottom: 20px;
        border-bottom: 1px solid rgba(255,255,255,0.2);
        position: relative;
        z-index: 1;
    }

    .quick-stat {
        display: flex;
        flex-direction: column;
    }

    .quick-stat-value {
        font-size: 1.125rem;
        font-weight: 700;
    }

    .text-success {
        color: #86efac !important;
    }

    .text-danger {
        color: #fca5a5 !important;
    }

    .quick-stat-label {
        font-size: 0.75rem;
        opacity: 0.7;
    }

    .balance-actions-main {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        position: relative;
        z-index: 1;
    }

    .btn-action {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 14px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.9375rem;
        text-decoration: none;
        transition: all 0.2s;
    }

    .btn-deposit {
        background: rgba(255,255,255,0.95);
        color: #1e40af;
    }

    .btn-deposit:active {
        background: white;
        transform: scale(0.98);
    }

    .btn-withdraw {
        background: rgba(255,255,255,0.2);
        color: white;
        border: 1px solid rgba(255,255,255,0.3);
    }

    .btn-withdraw:active {
        background: rgba(255,255,255,0.3);
    }

    /* Quick Actions */
    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        margin-bottom: 20px;
    }

    .action-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 16px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        color: #475569;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }

    .action-card:active {
        background: #f8fafc;
        transform: scale(0.98);
    }

    .action-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .action-icon.blue {
        background: #eff6ff;
        color: #3b82f6;
    }

    .action-icon.green {
        background: #f0fdf4;
        color: #22c55e;
    }

    /* Section Card */
    .section-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 16px;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .section-header h2 {
        font-size: 1.125rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }

    .section-title-sm {
        font-size: 1rem;
        font-weight: 600;
        color: #0f172a;
        margin: 0 0 16px 0;
    }

    .link-see-all {
        font-size: 0.875rem;
        color: #3b82f6;
        font-weight: 500;
        text-decoration: none;
    }

    /* Transactions List */
    .transactions-list-compact {
        display: flex;
        flex-direction: column;
    }

    .transaction-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .transaction-row:last-child {
        border-bottom: none;
    }

    .transaction-icon-sm {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        color: #64748b;
        flex-shrink: 0;
    }

    .transaction-row.credit .transaction-icon-sm {
        background: #dcfce7;
        color: #16a34a;
    }

    .transaction-row.debit .transaction-icon-sm {
        background: #fee2e2;
        color: #dc2626;
    }

    .transaction-info-sm {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
    }

    .transaction-type-sm {
        font-size: 0.9375rem;
        font-weight: 600;
        color: #0f172a;
    }

    .transaction-desc-sm {
        font-size: 0.8rem;
        color: #64748b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .transaction-date-sm {
        font-size: 0.75rem;
        color: #94a3b8;
    }

    .transaction-amount-sm {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 4px;
    }

    .transaction-amount-sm .amount {
        font-size: 1rem;
        font-weight: 700;
    }

    .transaction-amount-sm .amount.positive {
        color: #16a34a;
    }

    .transaction-amount-sm .amount.negative {
        color: #dc2626;
    }

    .status-badge {
        font-size: 0.6875rem;
        padding: 2px 8px;
        border-radius: 20px;
        font-weight: 500;
    }

    .status-badge.pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-badge.failed {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Info List */
    .info-list-compact {
        display: flex;
        flex-direction: column;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.9375rem;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-row span:first-child {
        color: #64748b;
    }

    .badge-status {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-status.active {
        background: #dcfce7;
        color: #166534;
    }

    .badge-status.suspended {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-status.closed {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Empty State */
    .empty-state-sm {
        text-align: center;
        padding: 24px;
        color: #94a3b8;
    }

    .empty-state-sm p {
        margin: 0 0 12px 0;
    }

    .btn-sm {
        padding: 8px 16px;
        font-size: 0.875rem;
    }

    /* Toast */
    .toast {
        position: fixed;
        bottom: 100px;
        left: 50%;
        transform: translateX(-50%);
        background: #0f172a;
        color: white;
        padding: 12px 20px;
        border-radius: 50px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.875rem;
        font-weight: 500;
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2);
        z-index: 1000;
        animation: slideUp 0.3s ease-out;
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateX(-50%) translateY(20px); }
        to { opacity: 1; transform: translateX(-50%) translateY(0); }
    }

    @media (max-width: 480px) {
        .wallet-container {
            padding: 12px;
        }

        .balance-card-main {
            padding: 20px;
        }

        .balance-amount {
            font-size: 2rem;
        }
    }
</style>
@endsection