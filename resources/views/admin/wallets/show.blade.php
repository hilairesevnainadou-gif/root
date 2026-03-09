@extends('layouts.admin')

@section('title', 'Wallet — ' . ($wallet->user->full_name ?? ''))
@section('header-title', 'Détail Wallet')

@section('styles')
<style>
    .wallet-detail-grid { display: grid; grid-template-columns: 340px 1fr; gap: 1.5rem; align-items: start; }
    .card { background: var(--color-white); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-sm); margin-bottom: 1.25rem; }
    .card-title { font-size: .9rem; font-weight: 700; color: var(--color-text); margin-bottom: 1rem; letter-spacing: -.02em; padding-bottom: .75rem; border-bottom: 1px solid var(--color-border); }

    .info-row { display: flex; justify-content: space-between; align-items: center; padding: .5rem 0; border-bottom: 1px solid #f8fafc; font-size: .85rem; }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: var(--color-text-muted); font-weight: 500; }
    .info-value { font-weight: 600; color: var(--color-text); }

    .balance-display { text-align: center; padding: 1.5rem 0; }
    .balance-amount  { font-size: 2.5rem; font-weight: 800; letter-spacing: -.04em; color: #1e293b; }
    .balance-currency { font-size: 1rem; font-weight: 500; color: var(--color-text-muted); }

    .stat-mini-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; margin-bottom: 1rem; }
    .stat-mini { background: #f8fafc; border-radius: 8px; padding: .875rem; text-align: center; }
    .stat-mini-val { font-size: 1.1rem; font-weight: 800; letter-spacing: -.02em; }
    .stat-mini-lbl { font-size: .7rem; color: var(--color-text-muted); margin-top: .1rem; text-transform: uppercase; letter-spacing: .04em; }

    .badge { display: inline-flex; align-items: center; padding: .2rem .65rem; border-radius: 9999px; font-size: .7rem; font-weight: 600; }
    .badge-green  { background: #dcfce7; color: #166534; }
    .badge-red    { background: #fee2e2; color: #991b1b; }
    .badge-yellow { background: #fef9c3; color: #854d0e; }
    .badge-blue   { background: #dbeafe; color: #1e40af; }
    .badge-gray   { background: #f1f5f9; color: #475569; }

    .btn { display: inline-flex; align-items: center; gap: .375rem; padding: .45rem .875rem; border-radius: 8px; font-size: .8rem; font-weight: 600; text-decoration: none; border: none; cursor: pointer; transition: all .15s; font-family: inherit; }
    .btn-sm { padding: .3rem .65rem; font-size: .75rem; }
    .btn-primary  { background: #3b82f6; color: #fff; }
    .btn-primary:hover  { background: #2563eb; color: #fff; }
    .btn-success  { background: #10b981; color: #fff; }
    .btn-success:hover  { background: #059669; color: #fff; }
    .btn-danger   { background: #ef4444; color: #fff; }
    .btn-danger:hover   { background: #dc2626; color: #fff; }
    .btn-warning  { background: #f59e0b; color: #fff; }
    .btn-warning:hover  { background: #d97706; color: #fff; }
    .btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid var(--color-border); }
    .btn-secondary:hover { background: #e2e8f0; }
    .btn-full { width: 100%; justify-content: center; }

    .form-group { margin-bottom: .875rem; }
    .form-label { display: block; font-size: .8rem; font-weight: 600; color: var(--color-text); margin-bottom: .35rem; }
    .form-control { width: 100%; padding: .5rem .75rem; border: 1px solid var(--color-border); border-radius: 8px; font-size: .875rem; font-family: inherit; outline: none; transition: border-color .15s; }
    .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.1); }

    .tx-type-credit { color: #16a34a; font-weight: 600; }
    .tx-type-debit  { color: #ef4444; font-weight: 600; }

    @media (max-width: 1024px) { .wallet-detail-grid { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')

<div style="margin-bottom:1rem;">
    <a href="{{ route('admin.wallets.index') }}" class="btn btn-secondary btn-sm">← Retour aux wallets</a>
</div>

<div class="wallet-detail-grid">

    {{-- Colonne gauche : infos + actions --}}
    <div>

        {{-- Balance card --}}
        <div class="card">
            <div class="balance-display">
                <div class="balance-amount">{{ number_format($wallet->balance, 0, ',', ' ') }}</div>
                <div class="balance-currency">{{ $wallet->currency }}</div>
            </div>

            <div class="stat-mini-grid">
                <div class="stat-mini">
                    <div class="stat-mini-val" style="color:#16a34a;">{{ number_format($stats['total_credited'], 0, ',', ' ') }}</div>
                    <div class="stat-mini-lbl">Crédité</div>
                </div>
                <div class="stat-mini">
                    <div class="stat-mini-val" style="color:#ef4444;">{{ number_format($stats['total_debited'], 0, ',', ' ') }}</div>
                    <div class="stat-mini-lbl">Débité</div>
                </div>
                <div class="stat-mini">
                    <div class="stat-mini-val" style="color:#f59e0b;">{{ number_format($stats['pending_debit'], 0, ',', ' ') }}</div>
                    <div class="stat-mini-lbl">En attente</div>
                </div>
                <div class="stat-mini">
                    <div class="stat-mini-val">{{ $stats['tx_count'] }}</div>
                    <div class="stat-mini-lbl">Transactions</div>
                </div>
            </div>

            {{-- Statut & suspension --}}
            <div style="display:flex; align-items:center; justify-content:space-between; margin-top:.5rem;">
                @if($wallet->status === 'active')
                    <span class="badge badge-green">Actif</span>
                @elseif($wallet->status === 'suspended')
                    <span class="badge badge-red">Suspendu</span>
                @else
                    <span class="badge badge-gray">{{ ucfirst($wallet->status) }}</span>
                @endif

                <form method="POST" action="{{ route('admin.wallets.toggle', $wallet) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-sm {{ $wallet->status === 'active' ? 'btn-danger' : 'btn-success' }}"
                        onclick="return confirm('{{ $wallet->status === 'active' ? 'Suspendre' : 'Réactiver' }} ce wallet ?')">
                        {{ $wallet->status === 'active' ? 'Suspendre' : 'Réactiver' }}
                    </button>
                </form>
            </div>
        </div>

        {{-- Infos propriétaire --}}
        <div class="card">
            <div class="card-title">Propriétaire</div>
            <div class="info-row">
                <span class="info-label">Nom</span>
                <span class="info-value">{{ $wallet->user->full_name ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Email</span>
                <span class="info-value" style="font-size:.8rem;">{{ $wallet->user->email ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Téléphone</span>
                <span class="info-value">{{ $wallet->user->phone ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">N° Wallet</span>
                <span class="info-value" style="font-family:monospace; font-size:.75rem;">{{ $wallet->wallet_number }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Activé le</span>
                <span class="info-value">{{ $wallet->activated_at?->format('d/m/Y') ?? '—' }}</span>
            </div>
            <a href="{{ route('admin.users.show', $wallet->user) }}" class="btn btn-secondary btn-sm" style="margin-top:.75rem;">
                Voir le profil utilisateur
            </a>
        </div>

        {{-- Ajustement manuel --}}
        <div class="card">
            <div class="card-title">Ajustement manuel</div>
            <form method="POST" action="{{ route('admin.wallets.adjust', $wallet) }}"
                  onsubmit="return confirm('Confirmer cet ajustement de solde ?')">
                @csrf
                <div class="form-group">
                    <label class="form-label">Type d'opération</label>
                    <select name="type" class="form-control" required>
                        <option value="credit">Crédit (ajouter au solde)</option>
                        <option value="debit">Débit (retirer du solde)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Montant (FCFA)</label>
                    <input type="number" name="amount" class="form-control" min="100" max="10000000" required placeholder="ex : 25000">
                </div>
                <div class="form-group">
                    <label class="form-label">Motif (obligatoire)</label>
                    <textarea name="reason" class="form-control" rows="2" required placeholder="Ex : correction d'erreur, remboursement…"></textarea>
                </div>
                <button type="submit" class="btn btn-warning btn-full">Appliquer l'ajustement</button>
            </form>
        </div>

    </div>

    {{-- Colonne droite : transactions --}}
    <div>
        <div class="data-table">
            <div style="padding:1rem 1.25rem; border-bottom:1px solid var(--color-border); display:flex; justify-content:space-between; align-items:center;">
                <span style="font-weight:700; font-size:.9rem;">Historique des transactions</span>
                <span style="font-size:.78rem; color:var(--color-text-muted);">{{ $transactions->total() }} transactions</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Type</th>
                        <th>Montant</th>
                        <th>Frais</th>
                        <th>Méthode</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                    <tr>
                        <td style="font-family:monospace; font-size:.73rem; color:var(--color-text-muted);">{{ $tx->transaction_id }}</td>
                        <td>
                            @if($tx->type === 'credit')
                                <span class="tx-type-credit">↑ Crédit</span>
                            @elseif($tx->type === 'debit')
                                <span class="tx-type-debit">↓ Retrait</span>
                            @else
                                <span>{{ $tx->getTypeLabel() }}</span>
                            @endif
                        </td>
                        <td style="font-weight:700;">{{ number_format($tx->amount, 0, ',', ' ') }} FCFA</td>
                        <td style="font-size:.8rem; color:var(--color-text-muted);">{{ number_format($tx->fee ?? 0, 0, ',', ' ') }}</td>
                        <td style="font-size:.78rem;">{{ str_replace('_', ' ', $tx->payment_method) }}</td>
                        <td>
                            @php
                                $badgeMap = ['completed'=>'badge-green','pending'=>'badge-yellow','failed'=>'badge-red','cancelled'=>'badge-gray','processing'=>'badge-blue'];
                            @endphp
                            <span class="badge {{ $badgeMap[$tx->status] ?? 'badge-gray' }}">{{ $tx->getStatusLabel() }}</span>
                        </td>
                        <td style="font-size:.78rem; white-space:nowrap;">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                        <td style="font-size:.75rem; color:var(--color-text-muted); max-width:140px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $tx->description }}">
                            {{ $tx->description }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" style="text-align:center; padding:2rem; color:var(--color-text-muted);">Aucune transaction</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:1rem;">{{ $transactions->links() }}</div>
    </div>

</div>

@endsection
