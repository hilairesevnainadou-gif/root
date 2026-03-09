@extends('layouts.app')

@section('title', 'Historique des transactions')
@section('header-title', 'Historique')

@section('content')

<div class="transactions-container">

    {{-- En-tête avec solde --}}
    <div class="header-balance">
        <div class="balance-info">
            <span class="balance-label">Solde actuel</span>
            <span class="balance-value">{{ number_format($wallet->balance, 0, ',', ' ') }} FCFA</span>
        </div>
        <a href="{{ route('client.wallet.show') }}" class="btn-back">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Retour
        </a>
    </div>

    {{-- Filtres --}}
    <div class="filters-card">
        <div class="filter-tabs">
            <button type="button" class="filter-tab active" data-filter="all" onclick="filterTransactions('all')">
                Tout
            </button>
            <button type="button" class="filter-tab" data-filter="credit" onclick="filterTransactions('credit')">
                Dépôts
            </button>
            <button type="button" class="filter-tab" data-filter="debit" onclick="filterTransactions('debit')">
                Retraits
            </button>
        </div>
    </div>

    {{-- Liste des transactions --}}
    <div class="transactions-list" id="transactionsList">
        @forelse($transactions as $transaction)
        <div class="transaction-card {{ $transaction->type }} {{ $transaction->status }}" data-type="{{ $transaction->type }}">
            
            {{-- En-tête de la transaction --}}
            <div class="transaction-header">
                <div class="transaction-icon">
                    @if($transaction->type === 'credit')
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                        </svg>
                    @elseif($transaction->type === 'debit')
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                        </svg>
                    @else
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                    @endif
                </div>
                
                <div class="transaction-main">
                    <div class="transaction-title-row">
                        <span class="transaction-type">{{ $transaction->getTypeLabel() }}</span>
                        <span class="transaction-amount {{ $transaction->type === 'credit' ? 'positive' : 'negative' }}">
                            {{ $transaction->type === 'credit' ? '+' : '-' }}{{ number_format($transaction->amount, 0, ',', ' ') }} FCFA
                        </span>
                    </div>
                    <span class="transaction-desc">{{ $transaction->description }}</span>
                </div>
            </div>

            {{-- Détails --}}
            <div class="transaction-details">
                <div class="detail-row">
                    <span class="detail-label">Date</span>
                    <span class="detail-value">{{ $transaction->created_at->format('d/m/Y à H:i') }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Référence</span>
                    <span class="detail-value code">{{ $transaction->transaction_id }}</span>
                </div>

                @if($transaction->fee > 0)
                <div class="detail-row">
                    <span class="detail-label">Frais</span>
                    <span class="detail-value">{{ number_format($transaction->fee, 0, ',', ' ') }} FCFA</span>
                </div>
                @endif

                <div class="detail-row">
                    <span class="detail-label">Statut</span>
                    <span class="status-pill {{ $transaction->status }}">
                        @if($transaction->status === 'completed')
                            Complété
                        @elseif($transaction->status === 'pending')
                            En attente
                        @elseif($transaction->status === 'failed')
                            Échoué
                        @elseif($transaction->status === 'cancelled')
                            Annulé
                        @else
                            {{ $transaction->status }}
                        @endif
                    </span>
                </div>

                @if($transaction->reference)
                <div class="detail-row">
                    <span class="detail-label">Réf. externe</span>
                    <span class="detail-value code">{{ $transaction->reference }}</span>
                </div>
                @endif
            </div>

            {{-- Actions si en attente --}}
            @if($transaction->status === 'pending' && $transaction->type === 'debit')
            <div class="transaction-actions">
                <form action="{{ route('client.wallet.withdraw.cancel', $transaction) }}" method="POST" class="cancel-form">
                    @csrf
                    <button type="submit" class="btn-cancel-transaction" onclick="return confirm('Annuler cette demande de retrait ?')">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Annuler la demande
                    </button>
                </form>
            </div>
            @endif
        </div>
        @empty
        <div class="empty-state">
            <div class="empty-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="64" height="64">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <h3>Aucune transaction</h3>
            <p>Votre historique est vide pour le moment.</p>
            <a href="{{ route('client.wallet.deposit') }}" class="btn-primary">Faire un dépôt</a>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($transactions->hasPages())
    <div class="pagination-container">
        {{ $transactions->links() }}
    </div>
    @endif

</div>

@endsection

@section('scripts')
<script>
function filterTransactions(type) {
    // Mettre à jour les tabs
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.classList.remove('active');
        if (tab.dataset.filter === type) {
            tab.classList.add('active');
        }
    });

    // Filtrer les transactions
    const cards = document.querySelectorAll('.transaction-card');
    cards.forEach(card => {
        if (type === 'all' || card.dataset.type === type) {
            card.style.display = 'block';
            card.style.animation = 'fadeIn 0.3s ease';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>
@endsection

@section('styles')
<style>
    .transactions-container {
        padding: 16px;
        padding-bottom: 100px;
        max-width: 600px;
        margin: 0 auto;
    }

    /* Header Balance */
    .header-balance {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding: 20px;
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        border-radius: 20px;
        color: white;
    }

    .balance-info {
        display: flex;
        flex-direction: column;
    }

    .balance-label {
        font-size: 0.875rem;
        opacity: 0.85;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .balance-value {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .btn-back {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 10px 16px;
        background: rgba(255,255,255,0.2);
        color: white;
        border-radius: 10px;
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.2s;
    }

    .btn-back:hover {
        background: rgba(255,255,255,0.3);
    }

    /* Filters */
    .filters-card {
        background: white;
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .filter-tabs {
        display: flex;
        gap: 8px;
    }

    .filter-tab {
        flex: 1;
        padding: 12px;
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        font-size: 0.875rem;
        font-weight: 500;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s;
    }

    .filter-tab:hover {
        border-color: #bfdbfe;
    }

    .filter-tab.active {
        background: #eff6ff;
        border-color: #3b82f6;
        color: #1e40af;
    }

    /* Transaction Cards */
    .transactions-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .transaction-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 1px solid #e2e8f0;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .transaction-header {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 16px;
        padding-bottom: 16px;
        border-bottom: 1px solid #f1f5f9;
    }

    .transaction-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        color: #64748b;
        flex-shrink: 0;
    }

    .transaction-card.credit .transaction-icon {
        background: #dcfce7;
        color: #16a34a;
    }

    .transaction-card.debit .transaction-icon {
        background: #fee2e2;
        color: #dc2626;
    }

    .transaction-main {
        flex: 1;
        min-width: 0;
    }

    .transaction-title-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 4px;
    }

    .transaction-type {
        font-size: 1rem;
        font-weight: 600;
        color: #0f172a;
    }

    .transaction-amount {
        font-size: 1.125rem;
        font-weight: 700;
    }

    .transaction-amount.positive {
        color: #16a34a;
    }

    .transaction-amount.negative {
        color: #dc2626;
    }

    .transaction-desc {
        font-size: 0.875rem;
        color: #64748b;
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Transaction Details */
    .transaction-details {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.875rem;
    }

    .detail-label {
        color: #64748b;
    }

    .detail-value {
        color: #0f172a;
        font-weight: 500;
    }

    .detail-value.code {
        font-family: 'SF Mono', monospace;
        font-size: 0.75rem;
        background: #f1f5f9;
        padding: 4px 8px;
        border-radius: 6px;
    }

    .status-pill {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-pill.completed {
        background: #dcfce7;
        color: #166534;
    }

    .status-pill.pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-pill.failed {
        background: #fee2e2;
        color: #991b1b;
    }

    .status-pill.cancelled {
        background: #f3f4f6;
        color: #6b7280;
    }

    /* Transaction Actions */
    .transaction-actions {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid #f1f5f9;
    }

    .btn-cancel-transaction {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 12px;
        background: #fef2f2;
        color: #dc2626;
        border: 1.5px solid #fecaca;
        border-radius: 10px;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-cancel-transaction:hover {
        background: #fee2e2;
        border-color: #dc2626;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .empty-icon {
        color: #cbd5e1;
        margin-bottom: 20px;
    }

    .empty-state h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 8px;
    }

    .empty-state p {
        color: #64748b;
        margin-bottom: 24px;
    }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        padding: 12px 24px;
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: white;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
    }

    /* Pagination */
    .pagination-container {
        margin-top: 24px;
        display: flex;
        justify-content: center;
    }

    .pagination-container nav {
        display: flex;
        gap: 8px;
    }

    .pagination-container a,
    .pagination-container span {
        padding: 8px 16px;
        border-radius: 10px;
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s;
    }

    .pagination-container a {
        background: white;
        color: #3b82f6;
        border: 1px solid #e2e8f0;
    }

    .pagination-container a:hover {
        background: #eff6ff;
        border-color: #3b82f6;
    }

    .pagination-container span {
        background: #3b82f6;
        color: white;
    }

    @media (max-width: 480px) {
        .transactions-container {
            padding: 12px;
        }

        .header-balance {
            flex-direction: column;
            gap: 16px;
            text-align: center;
        }

        .filter-tabs {
            flex-wrap: wrap;
        }

        .filter-tab {
            flex: 1;
            min-width: 80px;
        }
    }
</style>
@endsection