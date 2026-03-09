@extends('layouts.admin')

@section('title', 'Retraits en attente')
@section('header-title', 'Retraits à traiter')

@section('styles')
<style>
    .totals-bar { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
    .totals-card { background: var(--color-white); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 1.25rem 1.5rem; box-shadow: var(--shadow-sm); }
    .totals-label { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--color-text-muted); margin-bottom: .375rem; }
    .totals-value { font-size: 1.75rem; font-weight: 800; letter-spacing: -.03em; }

    .badge { display: inline-flex; align-items: center; padding: .2rem .65rem; border-radius: 9999px; font-size: .7rem; font-weight: 600; }
    .badge-yellow { background: #fef9c3; color: #854d0e; }
    .badge-blue   { background: #dbeafe; color: #1e40af; }
    .badge-green  { background: #dcfce7; color: #166534; }

    .btn { display: inline-flex; align-items: center; gap: .375rem; padding: .45rem .875rem; border-radius: 8px; font-size: .8rem; font-weight: 600; text-decoration: none; border: none; cursor: pointer; transition: all .15s; font-family: inherit; }
    .btn-sm { padding: .3rem .65rem; font-size: .75rem; }
    .btn-success  { background: #10b981; color: #fff; }
    .btn-success:hover  { background: #059669; color: #fff; }
    .btn-danger   { background: #ef4444; color: #fff; }
    .btn-danger:hover   { background: #dc2626; color: #fff; }
    .btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid var(--color-border); }
    .btn-secondary:hover { background: #e2e8f0; }
    .btn-primary  { background: #3b82f6; color: #fff; }
    .btn-primary:hover { background: #2563eb; color: #fff; }

    .withdrawal-card { background: var(--color-white); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 1.25rem; box-shadow: var(--shadow-sm); margin-bottom: 1rem; }
    .withdrawal-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; }
    .withdrawal-user { font-weight: 700; font-size: .95rem; }
    .withdrawal-ref  { font-family: monospace; font-size: .72rem; color: var(--color-text-muted); margin-top: .1rem; }
    .withdrawal-body { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1rem; }
    .withdrawal-field-label { font-size: .7rem; text-transform: uppercase; letter-spacing: .05em; color: var(--color-text-muted); font-weight: 600; margin-bottom: .2rem; }
    .withdrawal-field-value { font-size: .875rem; font-weight: 600; color: var(--color-text); }
    .withdrawal-actions { display: flex; gap: .625rem; border-top: 1px solid #f1f5f9; padding-top: 1rem; align-items: center; flex-wrap: wrap; }

    .form-inline { display: flex; gap: .5rem; align-items: center; flex: 1; }
    .form-control-sm { padding: .35rem .65rem; border: 1px solid var(--color-border); border-radius: 8px; font-size: .8rem; font-family: inherit; outline: none; }
    .form-control-sm:focus { border-color: #3b82f6; }

    /* Modal */
    .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 500; align-items: center; justify-content: center; }
    .modal-backdrop.open { display: flex; }
    .modal-box { background: #fff; border-radius: var(--radius-lg); padding: 1.75rem; width: 100%; max-width: 440px; box-shadow: 0 20px 60px rgba(0,0,0,.15); }
    .modal-title { font-size: 1rem; font-weight: 700; margin-bottom: 1rem; }
    .modal-form-group { margin-bottom: .875rem; }
    .modal-label { display: block; font-size: .8rem; font-weight: 600; margin-bottom: .35rem; }
    .modal-input { width: 100%; padding: .5rem .75rem; border: 1px solid var(--color-border); border-radius: 8px; font-size: .875rem; font-family: inherit; outline: none; }
    .modal-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.1); }
    .modal-actions { display: flex; gap: .75rem; margin-top: 1.25rem; }

    .empty-state { text-align: center; padding: 3rem; color: var(--color-text-muted); }
    .empty-state svg { width: 48px; height: 48px; margin: 0 auto 1rem; display: block; opacity: .3; }
</style>
@endsection

@section('content')

    {{-- Totaux --}}
    <div class="totals-bar">
        <div class="totals-card">
            <div class="totals-label">Retraits en attente</div>
            <div class="totals-value" style="color:#f59e0b;">{{ $totals['pending_count'] }}</div>
        </div>
        <div class="totals-card">
            <div class="totals-label">Montant total à décaisser</div>
            <div class="totals-value" style="color:#ef4444; font-size:1.4rem;">{{ number_format($totals['pending_amount'], 0, ',', ' ') }} FCFA</div>
        </div>
        <div class="totals-card" style="background:#f0fdf4; border-color:#bbf7d0;">
            <div class="totals-label" style="color:#166534;">Traiter rapidement</div>
            <p style="font-size:.8rem; color:#166534; margin-top:.25rem;">Approuvez ou rejetez chaque demande ci-dessous. Les fonds sont déjà réservés sur le wallet.</p>
        </div>
    </div>

    {{-- Filtre --}}
    <div class="filter-bar" style="margin-bottom:1.25rem;">
        <form method="GET" style="display:flex; gap:.75rem; flex-wrap:wrap; width:100%;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, email, référence…" class="filter-input" style="flex:1; min-width:200px;">
            <button type="submit" class="btn btn-primary">Rechercher</button>
            @if(request()->filled('search'))
                <a href="{{ route('admin.wallets.withdrawals') }}" class="btn btn-secondary">Réinitialiser</a>
            @endif
        </form>
    </div>

    {{-- Liste des retraits --}}
    @forelse($withdrawals as $tx)
    @php
        $details = $tx->metadata['payment_details'] ?? [];
        $method  = $tx->payment_method;
    @endphp
    <div class="withdrawal-card">
        <div class="withdrawal-header">
            <div>
                <div class="withdrawal-user">{{ $tx->wallet->user->full_name ?? '—' }}</div>
                <div class="withdrawal-ref">{{ $tx->transaction_id }}</div>
            </div>
            <span class="badge {{ $tx->status === 'pending' ? 'badge-yellow' : 'badge-blue' }}">
                {{ $tx->status === 'pending' ? 'En attente' : 'En cours' }}
            </span>
        </div>

        <div class="withdrawal-body">
            <div>
                <div class="withdrawal-field-label">Montant demandé</div>
                <div class="withdrawal-field-value" style="font-size:1.1rem; color:#ef4444;">{{ number_format($tx->amount, 0, ',', ' ') }} FCFA</div>
            </div>
            <div>
                <div class="withdrawal-field-label">Frais</div>
                <div class="withdrawal-field-value">{{ number_format($tx->fee, 0, ',', ' ') }} FCFA</div>
            </div>
            <div>
                <div class="withdrawal-field-label">Méthode</div>
                <div class="withdrawal-field-value">{{ $method === 'mobile_money' ? 'Mobile Money' : 'Virement bancaire' }}</div>
            </div>
            <div>
                <div class="withdrawal-field-label">Demandé le</div>
                <div class="withdrawal-field-value">{{ $tx->created_at->format('d/m/Y H:i') }}</div>
            </div>
        </div>

        {{-- Détails paiement --}}
        @if($method === 'mobile_money')
        <div style="background:#f8fafc; border-radius:8px; padding:.75rem 1rem; font-size:.82rem; margin-bottom:.75rem; display:flex; gap:1.5rem; flex-wrap:wrap;">
            <div><span style="color:var(--color-text-muted);">Opérateur :</span> <strong>{{ $details['operator'] ?? '—' }}</strong></div>
            <div><span style="color:var(--color-text-muted);">Numéro :</span> <strong>{{ $details['number'] ?? '—' }}</strong></div>
        </div>
        @elseif($method === 'bank_transfer')
        <div style="background:#f8fafc; border-radius:8px; padding:.75rem 1rem; font-size:.82rem; margin-bottom:.75rem; display:flex; gap:1.5rem; flex-wrap:wrap;">
            <div><span style="color:var(--color-text-muted);">Banque :</span> <strong>{{ $details['bank_name'] ?? '—' }}</strong></div>
            <div><span style="color:var(--color-text-muted);">Titulaire :</span> <strong>{{ $details['account_name'] ?? '—' }}</strong></div>
            <div><span style="color:var(--color-text-muted);">N° compte :</span> <strong>{{ $details['account_number'] ?? '—' }}</strong></div>
        </div>
        @endif

        {{-- Actions --}}
        <div class="withdrawal-actions">
            {{-- Approuver --}}
            <form method="POST" action="{{ route('admin.wallets.withdrawals.approve', $tx) }}" class="form-inline">
                @csrf
                <input type="text" name="reference" class="form-control-sm" placeholder="Référence paiement (optionnel)" style="min-width:200px;">
                <button type="submit" class="btn btn-success btn-sm"
                    onclick="return confirm('Confirmer l\'approbation de ce retrait ?')">
                    ✓ Approuver
                </button>
            </form>

            {{-- Rejeter (modal) --}}
            <button type="button" class="btn btn-danger btn-sm" onclick="openRejectModal('{{ $tx->id }}')">
                ✕ Rejeter
            </button>

            {{-- Lien wallet --}}
            <a href="{{ route('admin.wallets.show', $tx->wallet) }}" class="btn btn-secondary btn-sm" style="margin-left:auto;">
                Voir wallet
            </a>
        </div>
    </div>

    {{-- Modal rejet --}}
    <div class="modal-backdrop" id="modal-reject-{{ $tx->id }}">
        <div class="modal-box">
            <div class="modal-title">Rejeter le retrait</div>
            <p style="font-size:.85rem; color:var(--color-text-muted); margin-bottom:1rem;">
                Le montant sera recrédité sur le wallet de <strong>{{ $tx->wallet->user->full_name ?? '—' }}</strong>.
            </p>
            <form method="POST" action="{{ route('admin.wallets.withdrawals.reject', $tx) }}">
                @csrf
                <div class="modal-form-group">
                    <label class="modal-label">Motif du rejet (obligatoire)</label>
                    <textarea name="reason" class="modal-input" rows="3" required
                        placeholder="Ex : Informations de paiement incorrectes, demande frauduleuse…"></textarea>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn btn-danger" style="flex:1; justify-content:center;">Confirmer le rejet</button>
                    <button type="button" class="btn btn-secondary" onclick="closeRejectModal('{{ $tx->id }}')">Annuler</button>
                </div>
            </form>
        </div>
    </div>
    @empty
    <div class="empty-state">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p style="font-size:1rem; font-weight:600; margin-bottom:.25rem;">Aucun retrait en attente</p>
        <p style="font-size:.875rem;">Tous les retraits ont été traités.</p>
    </div>
    @endforelse

    <div style="margin-top:1rem;">{{ $withdrawals->links() }}</div>

@endsection

@section('scripts')
<script>
    function openRejectModal(id)  { document.getElementById('modal-reject-' + id).classList.add('open'); }
    function closeRejectModal(id) { document.getElementById('modal-reject-' + id).classList.remove('open'); }

    // Fermer en cliquant sur le backdrop
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
        backdrop.addEventListener('click', e => {
            if (e.target === backdrop) backdrop.classList.remove('open');
        });
    });
</script>
@endsection
