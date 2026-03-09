@extends('layouts.admin')

@section('title', 'Wallets')
@section('header-title', 'Gestion des Wallets')

@section('styles')
<style>
    .wallet-stats { display: grid; grid-template-columns: repeat(5, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
    .wallet-stat  { background: var(--color-white); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 1.25rem 1.5rem; box-shadow: var(--shadow-sm); }
    .wallet-stat-label { font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--color-text-muted); margin-bottom: .375rem; }
    .wallet-stat-value { font-size: 1.6rem; font-weight: 800; color: var(--color-text); letter-spacing: -.03em; }
    .wallet-stat-value.sm { font-size: 1.1rem; }
    .wallet-stat-sub { font-size: .75rem; color: var(--color-text-muted); margin-top: .25rem; }

    .badge { display: inline-flex; align-items: center; gap: .25rem; padding: .2rem .65rem; border-radius: 9999px; font-size: .7rem; font-weight: 600; }
    .badge-green    { background: #dcfce7; color: #166534; }
    .badge-red      { background: #fee2e2; color: #991b1b; }
    .badge-yellow   { background: #fef9c3; color: #854d0e; }
    .badge-gray     { background: #f1f5f9; color: #475569; }

    .btn { display: inline-flex; align-items: center; gap: .375rem; padding: .45rem .875rem; border-radius: 8px; font-size: .8rem; font-weight: 600; text-decoration: none; border: none; cursor: pointer; transition: all .15s; font-family: inherit; }
    .btn-sm { padding: .3rem .65rem; font-size: .75rem; }
    .btn-primary   { background: #3b82f6; color: #fff; }
    .btn-primary:hover   { background: #2563eb; color: #fff; }
    .btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid var(--color-border); }
    .btn-secondary:hover { background: #e2e8f0; }
    .btn-warning   { background: #f59e0b; color: #fff; }
    .btn-warning:hover   { background: #d97706; color: #fff; }

    .wallet-balance { font-weight: 700; color: var(--color-text); }
    .wallet-balance.high { color: #16a34a; }
    .wallet-balance.zero { color: #94a3b8; }

    @media (max-width: 1100px) { .wallet-stats { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 640px)  { .wallet-stats { grid-template-columns: 1fr 1fr; } }
</style>
@endsection

@section('content')

    {{-- Stats --}}
    <div class="wallet-stats">
        <div class="wallet-stat">
            <div class="wallet-stat-label">Wallets total</div>
            <div class="wallet-stat-value">{{ number_format($stats['total_wallets']) }}</div>
            <div class="wallet-stat-sub">{{ $stats['active_wallets'] }} actifs</div>
        </div>
        <div class="wallet-stat">
            <div class="wallet-stat-label">Solde total</div>
            <div class="wallet-stat-value sm">{{ number_format($stats['total_balance'], 0, ',', ' ') }}</div>
            <div class="wallet-stat-sub">FCFA en circulation</div>
        </div>
        <div class="wallet-stat">
            <div class="wallet-stat-label">Retraits en attente</div>
            <div class="wallet-stat-value" style="color:#f59e0b">{{ $stats['pending_withdrawals'] }}</div>
            <div class="wallet-stat-sub">demandes</div>
        </div>
        <div class="wallet-stat">
            <div class="wallet-stat-label">Montant retraits</div>
            <div class="wallet-stat-value sm" style="color:#ef4444">{{ number_format($stats['pending_amount'], 0, ',', ' ') }}</div>
            <div class="wallet-stat-sub">FCFA à décaisser</div>
        </div>
        <div class="wallet-stat" style="background:#fef9c3; border-color:#fde68a;">
            <div class="wallet-stat-label" style="color:#92400e;">Action rapide</div>
            <a href="{{ route('admin.wallets.withdrawals') }}" class="btn btn-warning" style="margin-top:.5rem; width:100%; justify-content:center;">
                Traiter les retraits
            </a>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="filter-bar">
        <form method="GET" style="display:flex; gap:.75rem; flex-wrap:wrap; align-items:center; width:100%;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un wallet, utilisateur…" class="filter-input" style="flex:1; min-width:200px;">
            <select name="status" class="filter-input">
                <option value="">Tous les statuts</option>
                <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>Actif</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspendu</option>
                <option value="closed"    {{ request('status') === 'closed'    ? 'selected' : '' }}>Fermé</option>
            </select>
            <button type="submit" class="btn btn-primary">Filtrer</button>
            @if(request()->hasAny(['search','status']))
                <a href="{{ route('admin.wallets.index') }}" class="btn btn-secondary">Réinitialiser</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>Utilisateur</th>
                    <th>N° Wallet</th>
                    <th>Solde</th>
                    <th>Crédité</th>
                    <th>Débité</th>
                    <th>Tx</th>
                    <th>Statut</th>
                    <th>Dernière opération</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($wallets as $wallet)
                <tr>
                    <td>
                        <div style="font-weight:600; font-size:.85rem;">{{ $wallet->user->full_name ?? '—' }}</div>
                        <div style="font-size:.75rem; color:var(--color-text-muted);">{{ $wallet->user->email ?? '' }}</div>
                    </td>
                    <td style="font-family:monospace; font-size:.78rem; color:var(--color-text-muted);">{{ $wallet->wallet_number }}</td>
                    <td>
                        @php $bal = (float)$wallet->balance; @endphp
                        <span class="wallet-balance {{ $bal > 10000 ? 'high' : ($bal == 0 ? 'zero' : '') }}">
                            {{ number_format($bal, 0, ',', ' ') }} FCFA
                        </span>
                    </td>
                    <td style="font-size:.82rem; color:#16a34a;">{{ number_format($wallet->total_credited ?? 0, 0, ',', ' ') }}</td>
                    <td style="font-size:.82rem; color:#ef4444;">{{ number_format($wallet->total_debited ?? 0, 0, ',', ' ') }}</td>
                    <td style="font-size:.82rem;">{{ $wallet->transactions_count }}</td>
                    <td>
                        @if($wallet->status === 'active')
                            <span class="badge badge-green">Actif</span>
                        @elseif($wallet->status === 'suspended')
                            <span class="badge badge-red">Suspendu</span>
                        @else
                            <span class="badge badge-gray">{{ ucfirst($wallet->status) }}</span>
                        @endif
                    </td>
                    <td style="font-size:.78rem; color:var(--color-text-muted);">
                        {{ $wallet->last_transaction_at?->diffForHumans() ?? 'Jamais' }}
                    </td>
                    <td>
                        <a href="{{ route('admin.wallets.show', $wallet) }}" class="btn btn-sm btn-primary">Détails</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center; padding:2rem; color:var(--color-text-muted);">Aucun wallet trouvé</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:1rem;">{{ $wallets->links() }}</div>

@endsection
