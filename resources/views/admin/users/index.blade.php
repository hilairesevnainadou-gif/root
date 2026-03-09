@extends('layouts.admin')

@section('title', 'Utilisateurs')
@section('header-title', 'Gestion des Utilisateurs')

@section('styles')
<style>
    /* ── Badges ─────────────────────────── */
    .badge { display: inline-flex; align-items: center; padding: .2rem .65rem; border-radius: 9999px; font-size: .7rem; font-weight: 600; white-space: nowrap; }
    .badge-green  { background: #dcfce7; color: #166534; }
    .badge-red    { background: #fee2e2; color: #991b1b; }
    .badge-yellow { background: #fef9c3; color: #854d0e; }
    .badge-blue   { background: #dbeafe; color: #1e40af; }
    .badge-purple { background: #f3e8ff; color: #6d28d9; }
    .badge-gray   { background: #f1f5f9; color: #475569; }
    .badge-orange { background: #fff7ed; color: #c2410c; }

    /* ── Boutons ────────────────────────── */
    .btn { display: inline-flex; align-items: center; gap: .375rem; padding: .45rem .875rem; border-radius: 8px; font-size: .8rem; font-weight: 600; text-decoration: none; border: none; cursor: pointer; transition: all .15s; font-family: inherit; }
    .btn-sm { padding: .3rem .65rem; font-size: .75rem; }
    .btn-primary   { background: #3b82f6; color: #fff; }
    .btn-primary:hover   { background: #2563eb; color: #fff; }
    .btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid var(--color-border); }
    .btn-secondary:hover { background: #e2e8f0; }

    /* ── Avatar ─────────────────────────── */
    .user-avatar {
        width: 36px; height: 36px; border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6, #8b5cf6);
        display: flex; align-items: center; justify-content: center;
        font-size: .75rem; font-weight: 700; color: #fff; flex-shrink: 0;
    }
    .user-cell { display: flex; align-items: center; gap: .75rem; }
    .user-name  { font-weight: 600; font-size: .85rem; color: var(--color-text); }
    .user-email { font-size: .75rem; color: var(--color-text-muted); }

    /* ── Stats rapides ──────────────────── */
    .quick-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
    .qs-card { background: var(--color-white); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 1.1rem 1.25rem; box-shadow: var(--shadow-sm); }
    .qs-label { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--color-text-muted); margin-bottom: .25rem; }
    .qs-value { font-size: 1.75rem; font-weight: 800; letter-spacing: -.03em; color: var(--color-text); }

    @media (max-width: 900px) { .quick-stats { grid-template-columns: repeat(2,1fr); } }
    @media (max-width: 640px) { .quick-stats { grid-template-columns: 1fr 1fr; } }
</style>
@endsection

@section('content')

    {{-- Stats rapides --}}
    <div class="quick-stats">
        <div class="qs-card">
            <div class="qs-label">Total utilisateurs</div>
            <div class="qs-value">{{ \App\Models\User::count() }}</div>
        </div>
        <div class="qs-card">
            <div class="qs-label">Non vérifiés</div>
            <div class="qs-value" style="color:#f59e0b;">{{ \App\Models\User::where('is_verified', false)->count() }}</div>
        </div>
        <div class="qs-card">
            <div class="qs-label">Suspendus</div>
            <div class="qs-value" style="color:#ef4444;">{{ \App\Models\User::where('member_status','suspended')->count() }}</div>
        </div>
        <div class="qs-card">
            <div class="qs-label">Ce mois</div>
            <div class="qs-value" style="color:#3b82f6;">{{ \App\Models\User::whereMonth('created_at', now()->month)->count() }}</div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="filter-bar">
        <form method="GET" style="display:flex; gap:.75rem; flex-wrap:wrap; align-items:center; width:100%;">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Nom, email, member ID…" class="filter-input" style="flex:1; min-width:200px;">
            <select name="member_type" class="filter-input">
                <option value="">Tous les types</option>
                <option value="particulier" {{ request('member_type') === 'particulier' ? 'selected' : '' }}>Particulier</option>
                <option value="entreprise"  {{ request('member_type') === 'entreprise'  ? 'selected' : '' }}>Entreprise</option>
                <option value="admin"       {{ request('member_type') === 'admin'       ? 'selected' : '' }}>Admin</option>
            </select>
            <select name="status" class="filter-input">
                <option value="">Tous les statuts</option>
                <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>Actif</option>
                <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>En attente</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspendu</option>
            </select>
            <button type="submit" class="btn btn-primary">Filtrer</button>
            @if(request()->hasAny(['search','member_type','status']))
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Réinitialiser</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>Utilisateur</th>
                    <th>Member ID</th>
                    <th>Type</th>
                    <th>Statut</th>
                    <th>Rôle</th>
                    <th>Demandes</th>
                    <th>Docs</th>
                    <th>Inscription</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <div class="user-cell">
                            <div class="user-avatar">{{ strtoupper(substr($user->first_name,0,1).substr($user->last_name,0,1)) }}</div>
                            <div>
                                <div class="user-name">{{ $user->full_name }}</div>
                                <div class="user-email">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-family:monospace; font-size:.75rem; color:var(--color-text-muted);">
                        {{ $user->member_id ?? '—' }}
                    </td>
                    <td>
                        @if($user->member_type === 'particulier')
                            <span class="badge badge-blue">Particulier</span>
                        @elseif($user->member_type === 'entreprise')
                            <span class="badge badge-purple">Entreprise</span>
                        @else
                            <span class="badge badge-gray">{{ ucfirst($user->member_type) }}</span>
                        @endif
                    </td>
                    <td>
                        @if(!$user->is_verified)
                            <span class="badge badge-yellow">Non vérifié</span>
                        @elseif($user->member_status === 'active')
                            <span class="badge badge-green">Actif</span>
                        @elseif($user->member_status === 'suspended')
                            <span class="badge badge-red">Suspendu</span>
                        @elseif($user->member_status === 'pending')
                            <span class="badge badge-yellow">En attente</span>
                        @else
                            <span class="badge badge-gray">{{ ucfirst($user->member_status) }}</span>
                        @endif
                    </td>
                    <td>
                        @if($user->is_admin)
                            <span class="badge badge-orange">Admin</span>
                        @elseif($user->is_moderator)
                            <span class="badge badge-purple">Modérateur</span>
                        @else
                            <span class="badge badge-gray">Membre</span>
                        @endif
                    </td>
                    <td style="font-weight:600;">{{ $user->funding_requests_count }}</td>
                    <td style="font-weight:600;">{{ $user->document_users_count }}</td>
                    <td style="font-size:.78rem; color:var(--color-text-muted);">{{ $user->created_at->format('d/m/Y') }}</td>
                    <td>
                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-primary">Voir</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center; padding:2.5rem; color:var(--color-text-muted);">Aucun utilisateur trouvé</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:1rem;">{{ $users->links() }}</div>

@endsection
