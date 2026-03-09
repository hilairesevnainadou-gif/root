@extends('layouts.admin')

@section('title', $user->full_name)
@section('header-title', 'Détail Utilisateur')

@section('styles')
<style>
    /* ── Layout ──────────────────────────── */
    .user-detail-grid { display: grid; grid-template-columns: 300px 1fr; gap: 1.5rem; align-items: start; }

    /* ── Card ───────────────────────────── */
    .card { background: var(--color-white); border: 1px solid var(--color-border); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 1.25rem; }
    .card-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--color-border); font-size: .9rem; font-weight: 700; color: var(--color-text); letter-spacing: -.02em; display: flex; align-items: center; justify-content: space-between; }
    .card-body { padding: 1.25rem; }

    /* ── Profil ─────────────────────────── */
    .profile-top { display: flex; flex-direction: column; align-items: center; padding: 1.75rem 1.25rem 1.25rem; text-align: center; }
    .big-avatar { width: 72px; height: 72px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #8b5cf6); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 800; color: #fff; margin-bottom: 1rem; }
    .profile-name  { font-size: 1rem; font-weight: 700; color: var(--color-text); }
    .profile-email { font-size: .8rem; color: var(--color-text-muted); margin-top: .2rem; }
    .profile-member-id { font-family: monospace; font-size: .72rem; color: #3b82f6; background: #eff6ff; padding: .2rem .6rem; border-radius: 6px; margin-top: .5rem; display: inline-block; }
    .profile-badges { display: flex; gap: .375rem; flex-wrap: wrap; justify-content: center; margin-top: .875rem; }

    /* ── Info rows ──────────────────────── */
    .info-row { display: flex; justify-content: space-between; align-items: center; padding: .5rem 0; border-bottom: 1px solid #f8fafc; font-size: .84rem; }
    .info-row:last-child { border: none; }
    .info-label { color: var(--color-text-muted); font-weight: 500; }
    .info-value { font-weight: 600; color: var(--color-text); text-align: right; max-width: 60%; }

    /* ── Stats mini ─────────────────────── */
    .stat-mini-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: .75rem; margin-bottom: 1.25rem; }
    .stat-mini { background: #f8fafc; border-radius: 8px; padding: .875rem; text-align: center; }
    .stat-mini-val { font-size: 1.25rem; font-weight: 800; letter-spacing: -.02em; color: var(--color-text); }
    .stat-mini-lbl { font-size: .7rem; color: var(--color-text-muted); margin-top: .1rem; text-transform: uppercase; letter-spacing: .04em; }

    /* ── Badges ─────────────────────────── */
    .badge { display: inline-flex; align-items: center; padding: .2rem .65rem; border-radius: 9999px; font-size: .7rem; font-weight: 600; white-space: nowrap; }
    .badge-green  { background: #dcfce7; color: #166534; }
    .badge-red    { background: #fee2e2; color: #991b1b; }
    .badge-yellow { background: #fef9c3; color: #854d0e; }
    .badge-blue   { background: #dbeafe; color: #1e40af; }
    .badge-purple { background: #f3e8ff; color: #6d28d9; }
    .badge-gray   { background: #f1f5f9; color: #475569; }
    .badge-orange { background: #fff7ed; color: #c2410c; }

    /* ── Status badge demandes ──────────── */
    .status-draft             { background: #f3f4f6; color: #6b7280; }
    .status-submitted         { background: #dbeafe; color: #1e40af; }
    .status-under_review      { background: #fef3c7; color: #92400e; }
    .status-pending_committee { background: #ffedd5; color: #c2410c; }
    .status-approved          { background: #d1fae5; color: #065f46; }
    .status-funded            { background: #dcfce7; color: #166534; }
    .status-rejected          { background: #fee2e2; color: #991b1b; }
    .status-cancelled         { background: #f1f5f9; color: #475569; }

    /* ── Boutons ────────────────────────── */
    .btn { display: inline-flex; align-items: center; gap: .375rem; padding: .45rem .875rem; border-radius: 8px; font-size: .8rem; font-weight: 600; text-decoration: none; border: none; cursor: pointer; transition: all .15s; font-family: inherit; }
    .btn-sm { padding: .3rem .65rem; font-size: .75rem; }
    .btn-xs { padding: .2rem .5rem; font-size: .7rem; }
    .btn-full { width: 100%; justify-content: center; }
    .btn-primary   { background: #3b82f6; color: #fff; }
    .btn-primary:hover   { background: #2563eb; color: #fff; }
    .btn-success   { background: #10b981; color: #fff; }
    .btn-success:hover   { background: #059669; color: #fff; }
    .btn-danger    { background: #ef4444; color: #fff; }
    .btn-danger:hover    { background: #dc2626; color: #fff; }
    .btn-warning   { background: #f59e0b; color: #fff; }
    .btn-warning:hover   { background: #d97706; color: #fff; }
    .btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid var(--color-border); }
    .btn-secondary:hover { background: #e2e8f0; }

    /* ── Actions bar ────────────────────── */
    .action-section { border-top: 1px solid var(--color-border); padding: .875rem 1.25rem; display: flex; flex-direction: column; gap: .625rem; }
    .action-row { display: flex; gap: .5rem; align-items: center; }
    .action-label { font-size: .78rem; color: var(--color-text-muted); font-weight: 500; min-width: 80px; }

    /* ── Form ───────────────────────────── */
    .form-group { margin-bottom: .875rem; }
    .form-label { display: block; font-size: .8rem; font-weight: 600; color: var(--color-text); margin-bottom: .35rem; }
    .form-control { width: 100%; padding: .5rem .75rem; border: 1px solid var(--color-border); border-radius: 8px; font-size: .875rem; font-family: inherit; outline: none; transition: border-color .15s; }
    .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.1); }

    /* ── Tabs ───────────────────────────── */
    .tabs { display: flex; gap: 0; border-bottom: 2px solid var(--color-border); margin-bottom: 1.25rem; }
    .tab-btn { padding: .75rem 1.25rem; font-size: .85rem; font-weight: 600; color: var(--color-text-muted); background: none; border: none; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all .15s; font-family: inherit; }
    .tab-btn.active { color: #3b82f6; border-bottom-color: #3b82f6; }
    .tab-btn:hover:not(.active) { color: var(--color-text); }
    .tab-panel { display: none; }
    .tab-panel.active { display: block; }

    /* ── Table légère ───────────────────── */
    .inner-table { width: 100%; border-collapse: collapse; font-size: .83rem; }
    .inner-table th { background: #f8fafc; padding: .65rem .875rem; text-align: left; font-size: .7rem; text-transform: uppercase; letter-spacing: .05em; color: var(--color-text-muted); font-weight: 600; border-bottom: 1px solid var(--color-border); }
    .inner-table td { padding: .75rem .875rem; border-bottom: 1px solid #f1f5f9; }
    .inner-table tr:last-child td { border: none; }
    .inner-table tr:hover td { background: #f8fafc; }

    .empty-state { text-align: center; padding: 2.5rem; color: var(--color-text-muted); font-size: .875rem; }

    @media (max-width: 1024px) { .user-detail-grid { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')

<div style="margin-bottom:1rem;">
    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">← Retour</a>
</div>

<div class="user-detail-grid">

    {{-- ── Colonne gauche ── --}}
    <div>

        {{-- Profil --}}
        <div class="card">
            <div class="profile-top">
                <div class="big-avatar">{{ strtoupper(substr($user->first_name,0,1).substr($user->last_name,0,1)) }}</div>
                <div class="profile-name">{{ $user->full_name }}</div>
                <div class="profile-email">{{ $user->email }}</div>
                @if($user->member_id)
                    <div class="profile-member-id">{{ $user->member_id }}</div>
                @endif
                <div class="profile-badges">
                    @if(!$user->is_verified)
                        <span class="badge badge-yellow">Non vérifié</span>
                    @elseif($user->member_status === 'active')
                        <span class="badge badge-green">Actif</span>
                    @elseif($user->member_status === 'suspended')
                        <span class="badge badge-red">Suspendu</span>
                    @else
                        <span class="badge badge-gray">{{ ucfirst($user->member_status) }}</span>
                    @endif

                    @if($user->is_admin)
                        <span class="badge badge-orange">Admin</span>
                    @elseif($user->is_moderator)
                        <span class="badge badge-purple">Modérateur</span>
                    @else
                        <span class="badge badge-gray">Membre</span>
                    @endif

                    @if($user->member_type === 'particulier')
                        <span class="badge badge-blue">Particulier</span>
                    @elseif($user->member_type === 'entreprise')
                        <span class="badge badge-purple">Entreprise</span>
                    @endif
                </div>
            </div>

            {{-- Stats mini --}}
            <div style="padding: 0 1.25rem 1.25rem;">
                <div class="stat-mini-grid">
                    <div class="stat-mini">
                        <div class="stat-mini-val">{{ $stats['total_requests'] }}</div>
                        <div class="stat-mini-lbl">Demandes</div>
                    </div>
                    <div class="stat-mini">
                        <div class="stat-mini-val" style="color:#16a34a; font-size:1rem;">{{ number_format($stats['total_funded'], 0, ',', ' ') }}</div>
                        <div class="stat-mini-lbl">FCFA financés</div>
                    </div>
                    <div class="stat-mini">
                        <div class="stat-mini-val">{{ $stats['documents_count'] }}</div>
                        <div class="stat-mini-lbl">Documents</div>
                    </div>
                    <div class="stat-mini">
                        <div class="stat-mini-val" style="color:#3b82f6; font-size:1rem;">{{ number_format($stats['wallet_balance'], 0, ',', ' ') }}</div>
                        <div class="stat-mini-lbl">Solde wallet</div>
                    </div>
                </div>
            </div>

            {{-- Infos --}}
            <div style="padding: 0 1.25rem 1rem; border-top: 1px solid var(--color-border); padding-top: .875rem;">
                <div class="info-row">
                    <span class="info-label">Téléphone</span>
                    <span class="info-value">{{ $user->phone ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ville</span>
                    <span class="info-value">{{ $user->city ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Membre depuis</span>
                    <span class="info-value">{{ $user->member_since?->format('d/m/Y') ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Dernière connexion</span>
                    <span class="info-value" style="font-size:.78rem;">{{ $user->last_login_at?->diffForHumans() ?? 'Jamais' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Inscrit le</span>
                    <span class="info-value">{{ $user->created_at->format('d/m/Y') }}</span>
                </div>
            </div>

            {{-- Actions rapides --}}
            <div class="action-section">
                {{-- Vérifier --}}
                @if(!$user->is_verified)
                <form method="POST" action="{{ route('admin.users.verify', $user) }}">
                    @csrf
                    <button type="submit" class="btn btn-success btn-full btn-sm">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Vérifier le compte
                    </button>
                </form>
                @endif

                {{-- Suspendre / Réactiver --}}
                <div class="action-row">
                    <span class="action-label">Statut</span>
                    <form method="POST" action="{{ route('admin.users.toggle', $user) }}" style="flex:1;">
                        @csrf
                        <button type="submit"
                            class="btn btn-sm btn-full {{ $user->member_status === 'active' ? 'btn-danger' : 'btn-success' }}"
                            onclick="return confirm('{{ $user->member_status === 'active' ? 'Suspendre' : 'Réactiver' }} cet utilisateur ?')">
                            {{ $user->member_status === 'active' ? 'Suspendre' : 'Réactiver' }}
                        </button>
                    </form>
                </div>

                {{-- Changer rôle --}}
                <div class="action-row">
                    <span class="action-label">Rôle</span>
                    <form method="POST" action="{{ route('admin.users.role', $user) }}" style="flex:1; display:flex; gap:.5rem;">
                        @csrf
                        <select name="role" class="form-control" style="padding:.35rem .65rem; font-size:.78rem; flex:1;">
                            <option value="user"      {{ (!$user->is_admin && !$user->is_moderator) ? 'selected' : '' }}>Membre</option>
                            <option value="moderator" {{ ($user->is_moderator && !$user->is_admin)  ? 'selected' : '' }}>Modérateur</option>
                            <option value="admin"     {{ $user->is_admin ? 'selected' : '' }}>Admin</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-warning">OK</button>
                    </form>
                </div>

                {{-- Wallet --}}
                @if($user->wallet)
                <a href="{{ route('admin.wallets.show', $user->wallet) }}" class="btn btn-secondary btn-full btn-sm">
                    Voir le wallet ({{ number_format($user->wallet->balance, 0, ',', ' ') }} FCFA)
                </a>
                @endif
            </div>
        </div>

    </div>

    {{-- ── Colonne droite : onglets ── --}}
    <div>
        <div class="card">
            <div class="card-body" style="padding-bottom:0;">

                <div class="tabs">
                    <button class="tab-btn active" onclick="showTab('requests', this)">Demandes ({{ $stats['total_requests'] }})</button>
                    <button class="tab-btn" onclick="showTab('identity', this)">Identité</button>
                    <button class="tab-btn" onclick="showTab('edit', this)">Modifier</button>
                </div>

                {{-- ── Onglet Demandes ── --}}
                <div class="tab-panel active" id="tab-requests">
                    @if($user->fundingRequests->isEmpty())
                        <div class="empty-state">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="40" height="40" style="margin:0 auto .75rem; display:block; opacity:.3;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            Aucune demande de financement
                        </div>
                    @else
                        <table class="inner-table">
                            <thead>
                                <tr>
                                    <th>N° Demande</th>
                                    <th>Type</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->fundingRequests as $req)
                                @php
                                    $labels = ['draft'=>'Brouillon','submitted'=>'Soumise','under_review'=>'En révision','pending_committee'=>'Comité','approved'=>'Approuvée','funded'=>'Financée','rejected'=>'Rejetée','cancelled'=>'Annulée'];
                                @endphp
                                <tr>
                                    <td style="font-family:monospace; font-size:.75rem;">{{ $req->request_number ?? '#'.$req->id }}</td>
                                    <td style="font-size:.82rem;">{{ $req->typeFinancement->name ?? '—' }}</td>
                                    <td style="font-weight:600;">{{ number_format($req->amount_requested ?? 0, 0, ',', ' ') }} FCFA</td>
                                    <td><span class="badge status-{{ $req->status }}">{{ $labels[$req->status] ?? $req->status }}</span></td>
                                    <td style="font-size:.78rem; color:var(--color-text-muted);">{{ $req->created_at->format('d/m/Y') }}</td>
                                    <td><a href="{{ route('admin.requests.show', $req) }}" class="btn btn-xs btn-primary">Voir</a></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if($stats['total_requests'] > 10)
                            <div style="padding:.875rem 1.25rem; font-size:.8rem; color:var(--color-text-muted); border-top:1px solid #f1f5f9;">
                                Affichage des 10 dernières. <a href="{{ route('admin.requests.index') }}?user={{ $user->id }}" style="color:#3b82f6;">Voir toutes</a>
                            </div>
                        @endif
                    @endif
                </div>

                {{-- ── Onglet Identité ── --}}
                <div class="tab-panel" id="tab-identity">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; padding-bottom:1rem;">
                        <div>
                            <p style="font-size:.8rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--color-text-muted); margin-bottom:.75rem;">Informations personnelles</p>
                            <div class="info-row"><span class="info-label">Prénom</span><span class="info-value">{{ $user->first_name }}</span></div>
                            <div class="info-row"><span class="info-label">Nom</span><span class="info-value">{{ $user->last_name }}</span></div>
                            <div class="info-row"><span class="info-label">Date naissance</span><span class="info-value">{{ $user->birth_date?->format('d/m/Y') ?? '—' }}</span></div>
                            <div class="info-row"><span class="info-label">Genre</span><span class="info-value">{{ ucfirst($user->gender ?? '—') }}</span></div>
                            <div class="info-row"><span class="info-label">Adresse</span><span class="info-value">{{ $user->address ?? '—' }}</span></div>
                            <div class="info-row"><span class="info-label">Ville</span><span class="info-value">{{ $user->city ?? '—' }}</span></div>
                        </div>
                        <div>
                            <p style="font-size:.8rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--color-text-muted); margin-bottom:.75rem;">Connexion & Sécurité</p>
                            <div class="info-row"><span class="info-label">Email vérifié</span>
                                <span class="info-value">{{ $user->email_verified_at ? '✓ '.$user->email_verified_at->format('d/m/Y') : '—' }}</span>
                            </div>
                            <div class="info-row"><span class="info-label">Dernière IP</span><span class="info-value" style="font-family:monospace; font-size:.78rem;">{{ $user->last_login_ip ?? '—' }}</span></div>
                            <div class="info-row"><span class="info-label">Dernière conn.</span><span class="info-value">{{ $user->last_login_at?->format('d/m/Y H:i') ?? '—' }}</span></div>
                            <div class="info-row"><span class="info-label">Appareil</span>
                                <span class="info-value" style="font-size:.72rem; max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $user->last_login_device }}">
                                    {{ $user->last_login_device ? Str::limit($user->last_login_device, 30) : '—' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Onglet Modifier ── --}}
                <div class="tab-panel" id="tab-edit">
                    <form method="POST" action="{{ route('admin.users.update', $user) }}" style="padding-bottom:1.25rem;">
                        @csrf @method('PATCH')
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                            <div class="form-group">
                                <label class="form-label">Prénom</label>
                                <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $user->first_name) }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nom</label>
                                <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $user->last_name) }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Téléphone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Ville</label>
                                <input type="text" name="city" class="form-control" value="{{ old('city', $user->city) }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Type membre</label>
                                <select name="member_type" class="form-control">
                                    <option value="particulier" {{ $user->member_type === 'particulier' ? 'selected' : '' }}>Particulier</option>
                                    <option value="entreprise"  {{ $user->member_type === 'entreprise'  ? 'selected' : '' }}>Entreprise</option>
                                    <option value="admin"       {{ $user->member_type === 'admin'       ? 'selected' : '' }}>Admin</option>
                                </select>
                            </div>
                            <div class="form-group" style="grid-column:1/-1;">
                                <label class="form-label">Adresse</label>
                                <input type="text" name="address" class="form-control" value="{{ old('address', $user->address) }}">
                            </div>
                        </div>
                        @if($errors->any())
                            <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:8px; padding:.75rem 1rem; margin-bottom:1rem; font-size:.82rem; color:#991b1b;">
                                @foreach($errors->all() as $error) <div>• {{ $error }}</div> @endforeach
                            </div>
                        @endif
                        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                    </form>
                </div>

            </div>
        </div>
    </div>

</div>

@endsection

@section('scripts')
<script>
    function showTab(name, btn) {
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('tab-' + name).classList.add('active');
        btn.classList.add('active');
    }
</script>
@endsection
