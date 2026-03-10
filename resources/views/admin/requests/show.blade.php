@extends('layouts.admin')

@section('title', 'Demande #' . $fundingRequest->request_number)
@section('header-title', 'Détail de la demande')

@section('styles')
<style>
    /* ── Layout ─────────────────────────── */
    .show-grid { display: grid; grid-template-columns: 1fr 340px; gap: 1.5rem; align-items: start; }

    /* ── Card ───────────────────────────── */
    .card { background: var(--color-white); border: 1px solid var(--color-border); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 1.25rem; }
    .card-header { padding: .875rem 1.25rem; border-bottom: 1px solid var(--color-border); font-size: .875rem; font-weight: 700; color: var(--color-text); display: flex; align-items: center; justify-content: space-between; gap: .75rem; }
    .card-header-icon { color: #3b82f6; margin-right: .5rem; display: inline-flex; align-items: center; vertical-align: middle; }
    .card-body { padding: 1.25rem; }

    /* ── Badges ─────────────────────────── */
    .badge { display: inline-flex; align-items: center; gap: .25rem; padding: .2rem .65rem; border-radius: 9999px; font-size: .7rem; font-weight: 600; white-space: nowrap; }
    .badge-yellow  { background: #fef9c3; color: #854d0e; }
    .badge-green   { background: #dcfce7; color: #166534; }
    .badge-red     { background: #fee2e2; color: #991b1b; }
    .badge-blue    { background: #dbeafe; color: #1e40af; }
    .badge-gray    { background: #f1f5f9; color: #475569; }
    .badge-purple  { background: #f3e8ff; color: #6d28d9; }
    .badge-indigo  { background: #e0e7ff; color: #3730a3; }
    .badge-orange  { background: #ffedd5; color: #9a3412; }

    /* ── Boutons ────────────────────────── */
    .btn { display: inline-flex; align-items: center; gap: .375rem; padding: .45rem .875rem; border-radius: 8px; font-size: .8rem; font-weight: 600; text-decoration: none; border: none; cursor: pointer; transition: all .15s; font-family: inherit; }
    .btn-sm { padding: .3rem .65rem; font-size: .75rem; }
    .btn-full { width: 100%; justify-content: center; }
    .btn-primary   { background: #3b82f6; color: #fff; }
    .btn-primary:hover   { background: #2563eb; color: #fff; }
    .btn-success   { background: #10b981; color: #fff; }
    .btn-success:hover   { background: #059669; color: #fff; }
    .btn-danger    { background: #ef4444; color: #fff; }
    .btn-danger:hover    { background: #dc2626; color: #fff; }
    .btn-warning   { background: #f59e0b; color: #fff; }
    .btn-warning:hover   { background: #d97706; color: #fff; }
    .btn-indigo    { background: #6366f1; color: #fff; }
    .btn-indigo:hover    { background: #4f46e5; color: #fff; }
    .btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid var(--color-border); }
    .btn-secondary:hover { background: #e2e8f0; }
    .btn-ghost-primary { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
    .btn-ghost-primary:hover { background: #dbeafe; }

    /* ── Info rows ──────────────────────── */
    .info-row { display: flex; justify-content: space-between; align-items: flex-start; padding: .5rem 0; border-bottom: 1px solid #f1f5f9; font-size: .84rem; gap: .75rem; }
    .info-row:last-child { border: none; }
    .info-label { color: var(--color-text-muted); font-weight: 500; flex-shrink: 0; }
    .info-value { font-weight: 600; color: var(--color-text); text-align: right; word-break: break-word; }

    /* ── Demandeur ──────────────────────── */
    .user-row { display: flex; align-items: center; gap: 1rem; }
    .user-avatar { width: 56px; height: 56px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: 800; color: #fff; background: linear-gradient(135deg, #3b82f6, #6366f1); flex-shrink: 0; overflow: hidden; }
    .user-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .user-meta { font-size: .8rem; color: var(--color-text-muted); margin-top: .15rem; display: flex; flex-direction: column; gap: .1rem; }
    .user-detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--color-border); }
    .user-detail-item { font-size: .82rem; }
    .user-detail-item .lbl { color: var(--color-text-muted); font-size: .72rem; margin-bottom: .1rem; }
    .user-detail-item .val { font-weight: 600; }

    /* ── Documents ──────────────────────── */
    .docs-progress { display: flex; align-items: center; gap: .75rem; }
    .progress-track { flex: 1; height: 6px; background: #e2e8f0; border-radius: 9999px; overflow: hidden; }
    .progress-fill  { height: 100%; background: #10b981; border-radius: 9999px; transition: width .5s ease; }
    .progress-label { font-size: .75rem; font-weight: 700; color: var(--color-text-muted); white-space: nowrap; }
    .doc-cards-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }
    .doc-card { border: 1px solid var(--color-border); border-radius: 10px; padding: .875rem; transition: transform .15s, box-shadow .15s; }
    .doc-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.07); }
    .doc-card.verified { border-color: #bbf7d0; background: #f0fdf4; }
    .doc-card.pending  { border-color: #fde68a; background: #fffbeb; }
    .doc-card.rejected { border-color: #fecaca; background: #fef2f2; }
    .doc-card.missing  { border-color: #e2e8f0; background: #f8fafc; }
    .doc-status-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: .5rem; }
    .doc-card.verified .doc-status-icon { background: #dcfce7; color: #166534; }
    .doc-card.pending  .doc-status-icon { background: #fef9c3; color: #854d0e; }
    .doc-card.rejected .doc-status-icon { background: #fee2e2; color: #991b1b; }
    .doc-card.missing  .doc-status-icon { background: #f1f5f9; color: #94a3b8; }
    .doc-card-name  { font-size: .82rem; font-weight: 700; margin-bottom: .25rem; }
    .doc-card-meta  { font-size: .72rem; color: var(--color-text-muted); }

    /* ── Timeline ───────────────────────── */
    .timeline { position: relative; padding-left: 1.75rem; }
    .timeline::before { content: ''; position: absolute; left: .45rem; top: .5rem; bottom: .5rem; width: 2px; background: var(--color-border); }
    .timeline-item { position: relative; padding-bottom: 1.25rem; }
    .timeline-item:last-child { padding-bottom: 0; }
    .timeline-dot { position: absolute; left: -1.3rem; top: .25rem; width: 12px; height: 12px; border-radius: 50%; background: #cbd5e1; border: 2px solid #fff; box-shadow: 0 0 0 2px #cbd5e1; }
    .timeline-dot.done    { background: #10b981; box-shadow: 0 0 0 2px #10b981; }
    .timeline-dot.current { background: #f59e0b; box-shadow: 0 0 0 2px #f59e0b; }
    .timeline-dot.fail    { background: #ef4444; box-shadow: 0 0 0 2px #ef4444; }
    .timeline-title { font-size: .875rem; font-weight: 700; margin-bottom: .1rem; }
    .timeline-date  { font-size: .75rem; color: var(--color-text-muted); }
    .timeline-sub   { font-size: .75rem; color: var(--color-text-muted); margin-top: .15rem; }

    /* ── Action buttons ─────────────────── */
    .actions-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }
    .action-btn { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1rem; border: 1.5px solid var(--color-border); border-radius: 10px; background: #fff; cursor: pointer; text-decoration: none; font-family: inherit; transition: all .15s; gap: .4rem; }
    .action-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.07); }
    .action-btn .action-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
    .action-btn .action-label { font-size: .78rem; font-weight: 700; color: var(--color-text); text-align: center; }
    .action-btn.a-review  { border-color: #bfdbfe; } .action-btn.a-review .action-icon  { background: #dbeafe; color: #1e40af; }
    .action-btn.a-commit  { border-color: #fde68a; } .action-btn.a-commit .action-icon  { background: #fef9c3; color: #854d0e; }
    .action-btn.a-approve { border-color: #bbf7d0; } .action-btn.a-approve .action-icon { background: #dcfce7; color: #166534; }
    .action-btn.a-reject  { border-color: #fecaca; } .action-btn.a-reject .action-icon  { background: #fee2e2; color: #991b1b; }
    .action-btn.a-fund    { border-color: #d9f99d; } .action-btn.a-fund .action-icon    { background: #ecfccb; color: #365314; }
    .action-btn.a-cancel  { border-color: #e2e8f0; } .action-btn.a-cancel .action-icon  { background: #f1f5f9; color: #64748b; }
    .action-btn.a-disburse { border-color: #a5f3fc; } .action-btn.a-disburse .action-icon { background: #ecfeff; color: #164e63; }

    /* ── Amounts card ───────────────────── */
    .amounts-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: var(--radius-lg); padding: 1.25rem; color: #fff; margin-bottom: 1.25rem; }
    .amounts-label { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: rgba(255,255,255,.65); margin-bottom: .2rem; }
    .amounts-value { font-size: 1.6rem; font-weight: 800; letter-spacing: -.03em; }
    .amounts-divider { border: none; border-top: 1px solid rgba(255,255,255,.2); margin: .875rem 0; }
    .amounts-mini-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; }
    .amounts-mini-label { font-size: .72rem; color: rgba(255,255,255,.65); }
    .amounts-mini-value { font-size: .85rem; font-weight: 700; }

    /* ── Committee form ─────────────────── */
    .form-label { display: block; font-size: .8rem; font-weight: 600; margin-bottom: .35rem; color: var(--color-text); }
    .form-control { width: 100%; padding: .5rem .75rem; border: 1px solid var(--color-border); border-radius: 8px; font-size: .875rem; font-family: inherit; outline: none; transition: border-color .15s; box-sizing: border-box; }
    .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.1); }
    .form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; margin-bottom: .75rem; }

    /* ── Section title ──────────────────── */
    .section-title { font-size: .8rem; font-weight: 700; color: var(--color-text); display: flex; align-items: center; gap: .5rem; }
    .section-title svg { color: #6366f1; }

    /* ── Pending disbursement banner ──── */
    .disbursement-banner { background: #ecfeff; border: 1px solid #a5f3fc; border-radius: 10px; padding: .875rem 1rem; display: flex; align-items: flex-start; gap: .75rem; margin-bottom: .875rem; }
    .disbursement-banner-icon { width: 36px; height: 36px; border-radius: 8px; background: #cffafe; color: #164e63; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .disbursement-banner-text { font-size: .82rem; }
    .disbursement-banner-text strong { display: block; font-weight: 700; color: #164e63; margin-bottom: .15rem; }
    .disbursement-banner-text span { color: #0e7490; }

    /* ── Modal ──────────────────────────── */
    .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 500; align-items: center; justify-content: center; padding: 1rem; }
    .modal-backdrop.open { display: flex; }
    .modal-box { background: #fff; border-radius: var(--radius-lg); padding: 1.75rem; width: 100%; max-width: 460px; box-shadow: 0 25px 60px rgba(0,0,0,.2); }
    .modal-header-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; }
    .modal-title { font-size: 1rem; font-weight: 700; display: flex; align-items: center; gap: .5rem; }
    .modal-close { background: none; border: none; cursor: pointer; color: var(--color-text-muted); padding: .25rem; border-radius: 6px; }
    .modal-close:hover { background: #f1f5f9; }
    .modal-actions { display: flex; gap: .75rem; margin-top: 1.25rem; }
    .modal-alert { background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: .75rem 1rem; font-size: .82rem; color: #854d0e; margin-bottom: 1rem; display: flex; gap: .5rem; align-items: flex-start; }

    @media (max-width: 1100px) { .show-grid { grid-template-columns: 1fr; } }
    @media (max-width: 640px)  { .doc-cards-grid, .actions-grid, .form-row-2 { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')

    {{-- ── Breadcrumb & nav ── --}}
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.75rem; margin-bottom:1.25rem;">
        <div style="display:flex; align-items:center; gap:.5rem; font-size:.8rem; color:var(--color-text-muted);">
            <a href="{{ route('admin.dashboard') }}" style="color:var(--color-text-muted); text-decoration:none;">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.requests.index') }}" style="color:var(--color-text-muted); text-decoration:none;">Demandes</a>
            <span>/</span>
            <span style="color:var(--color-text); font-weight:600;">#{{ $fundingRequest->request_number }}</span>
        </div>
        <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
            <a href="{{ route('admin.requests.index') }}" class="btn btn-secondary btn-sm">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="13" height="13"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Retour
            </a>
            <button onclick="window.print()" class="btn btn-secondary btn-sm">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="13" height="13"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Imprimer
            </button>
            @if(in_array($fundingRequest->status, ['under_review','pending_committee','submitted']))
            <button type="button" class="btn btn-ghost-primary btn-sm"
                onclick="document.getElementById('modal-assign').classList.add('open')">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="13" height="13"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                Assigner
            </button>
            @endif
        </div>
    </div>

    {{-- ── Titre demande ── --}}
    <div style="background:var(--color-white); border:1px solid var(--color-border); border-radius:var(--radius-lg); padding:1.25rem 1.5rem; margin-bottom:1.5rem; box-shadow:var(--shadow-sm); display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
        <div style="flex:1; min-width:200px;">
            <div style="display:flex; align-items:center; gap:.75rem; flex-wrap:wrap; margin-bottom:.25rem;">
                <h1 style="font-size:1.15rem; font-weight:800; margin:0;">Demande #{{ $fundingRequest->request_number }}</h1>
                @php
                    $statusBadge = match($fundingRequest->status) {
                        'draft'                => ['badge-gray',   'Brouillon'],
                        'pending'              => ['badge-yellow', 'En attente'],
                        'submitted'            => ['badge-blue',   'Soumise'],
                        'under_review'         => ['badge-indigo', 'En examen'],
                        'pending_committee'    => ['badge-orange', 'Comité'],
                        'approved'             => ['badge-green',  'Approuvée'],
                        'pending_disbursement' => ['badge-blue',   'Versement en attente'],
                        'rejected'             => ['badge-red',    'Rejetée'],
                        'funded'               => ['badge-green',  'Financée'],
                        'cancelled'            => ['badge-gray',   'Annulée'],
                        default                => ['badge-gray',    ucfirst($fundingRequest->status)],
                    };
                @endphp
                <span class="badge {{ $statusBadge[0] }}" style="font-size:.75rem; padding:.3rem .8rem;">
                    {{ $statusBadge[1] }}
                </span>
            </div>
            <div style="font-size:.8rem; color:var(--color-text-muted);">
                {{ $fundingRequest->typeFinancement->name }}
                <span style="margin:0 .4rem;">·</span>
                Soumise le {{ $fundingRequest->created_at->format('d/m/Y à H:i') }}
                @if($fundingRequest->submitted_at)
                    <span style="margin:0 .4rem;">·</span>
                    {{ $fundingRequest->created_at->diffForHumans() }}
                @endif
            </div>
        </div>
    </div>

    <div class="show-grid">

        {{-- ════════════════════════════════
             COLONNE PRINCIPALE
        ════════════════════════════════ --}}
        <div>

            {{-- ── Demandeur ── --}}
            <div class="card">
                <div class="card-header">
                    <span class="section-title">
                        <svg class="card-header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Demandeur
                    </span>
                    <a href="{{ route('admin.users.show', $fundingRequest->user) }}" class="btn btn-ghost-primary btn-sm">
                        Voir le profil
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="12" height="12"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
                <div class="card-body">
                    <div class="user-row">
                        <div class="user-avatar">
                            @if($fundingRequest->user->profile_photo)
                                <img src="{{ asset('storage/'.$fundingRequest->user->profile_photo) }}" alt="">
                            @else
                                {{ strtoupper(substr($fundingRequest->user->first_name,0,1).substr($fundingRequest->user->last_name,0,1)) }}
                            @endif
                        </div>
                        <div>
                            <div style="font-weight:800; font-size:.95rem;">{{ $fundingRequest->user->full_name }}</div>
                            <div class="user-meta">
                                <span>{{ $fundingRequest->user->email }}</span>
                                @if($fundingRequest->user->phone)
                                <span>{{ $fundingRequest->user->phone }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="user-detail-grid">
                        <div class="user-detail-item">
                            <div class="lbl">Type membre</div>
                            <div class="val">{{ ucfirst($fundingRequest->user->member_type ?? 'Particulier') }}</div>
                        </div>
                        <div class="user-detail-item">
                            <div class="lbl">Membre depuis</div>
                            <div class="val">{{ $fundingRequest->user->created_at->format('d/m/Y') }}</div>
                        </div>
                        <div class="user-detail-item">
                            <div class="lbl">Statut</div>
                            <div class="val">
                                <span class="badge {{ $fundingRequest->user->is_verified ? 'badge-green' : 'badge-yellow' }}">
                                    {{ $fundingRequest->user->is_verified ? 'Vérifié' : 'Non vérifié' }}
                                </span>
                            </div>
                        </div>
                        @if($fundingRequest->user->city)
                        <div class="user-detail-item">
                            <div class="lbl">Ville</div>
                            <div class="val">{{ $fundingRequest->user->city }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── Détails projet ── --}}
            <div class="card">
                <div class="card-header">
                    <span class="section-title">
                        <svg class="card-header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Détails du projet
                    </span>
                </div>
                <div class="card-body">
                    <div style="margin-bottom:1.1rem;">
                        <div style="font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--color-text-muted); margin-bottom:.3rem;">Titre du projet</div>
                        <div style="font-size:1.1rem; font-weight:800;">{{ $fundingRequest->title }}</div>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.1rem;">
                        <div>
                            <div style="font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--color-text-muted); margin-bottom:.3rem;">Type de financement</div>
                            <div style="font-weight:700; font-size:.9rem;">{{ $fundingRequest->typeFinancement->name }}</div>
                            @if($fundingRequest->typeFinancement->description)
                            <div style="font-size:.75rem; color:var(--color-text-muted); margin-top:.15rem;">{{ $fundingRequest->typeFinancement->description }}</div>
                            @endif
                        </div>
                        <div>
                            <div style="font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--color-text-muted); margin-bottom:.3rem;">Durée souhaitée</div>
                            <div style="font-weight:700; font-size:.9rem;">{{ $fundingRequest->duration }} mois</div>
                        </div>
                    </div>

                    <div>
                        <div style="font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--color-text-muted); margin-bottom:.5rem;">Description détaillée</div>
                        <div style="background:#f8fafc; border:1px solid var(--color-border); border-radius:8px; padding:.875rem; font-size:.875rem; line-height:1.6; color:var(--color-text);">
                            {{ $fundingRequest->description ?? 'Aucune description fournie.' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Documents ── --}}
            <div class="card">
                <div class="card-header">
                    <span class="section-title">
                        <svg class="card-header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        Documents
                        <span style="font-size:.72rem; font-weight:600; color:var(--color-text-muted); margin-left:.25rem;">({{ count($documentsStatus) }})</span>
                    </span>
                    @php
                        $verifiedCount = collect($documentsStatus)->where('status','verified')->count();
                        $totalCount    = count($documentsStatus);
                        $pct           = $totalCount > 0 ? round(($verifiedCount / $totalCount) * 100) : 0;
                    @endphp
                    <div class="docs-progress">
                        <div class="progress-track">
                            <div class="progress-fill" style="width:{{ $pct }}%;" id="docProgress"></div>
                        </div>
                        <span class="progress-label">{{ $verifiedCount }}/{{ $totalCount }}</span>
                    </div>
                </div>
                <div class="card-body">
                    @if(count($documentsStatus) > 0)
                    <div class="doc-cards-grid">
                        @foreach($documentsStatus as $doc)
                        @php
                            $dcClass = match($doc['status'] ?? 'missing') {
                                'verified' => 'verified',
                                'pending'  => 'pending',
                                'rejected' => 'rejected',
                                default    => 'missing',
                            };
                        @endphp
                        <div class="doc-card {{ $dcClass }}">
                            <div class="doc-status-icon">
                                @switch($dcClass)
                                    @case('verified')
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        @break
                                    @case('pending')
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @break
                                    @case('rejected')
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                        @break
                                    @default
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                @endswitch
                            </div>
                            <div class="doc-card-name">{{ $doc['name'] }}</div>
                            @if($doc['provided'] ?? false)
                                <div class="doc-card-meta">
                                    Fourni le {{ $doc['uploaded_at']->format('d/m/Y') }}
                                    @if($doc['verified_by'] ?? false)
                                        <br>Par {{ $doc['verified_by_name'] ?? 'Admin' }}
                                    @endif
                                </div>
                                <div style="margin-top:.5rem;">
                                    <a href="{{ route('admin.documents.show', $doc['document_id'] ?? 0) }}" class="btn btn-sm btn-ghost-primary" style="font-size:.7rem; padding:.2rem .55rem;">
                                        Consulter
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="11" height="11"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </div>
                            @else
                                <div class="doc-card-meta" style="color:#ef4444;">Document manquant</div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div style="text-align:center; padding:1.5rem; color:var(--color-text-muted); font-size:.85rem;">
                        Aucun document requis pour ce type de financement.
                    </div>
                    @endif
                </div>
            </div>

            {{-- ── Timeline ── --}}
            <div class="card">
                <div class="card-header">
                    <span class="section-title">
                        <svg class="card-header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Historique de la demande
                    </span>
                </div>
                <div class="card-body">
                    <div class="timeline">

                        <div class="timeline-item">
                            <div class="timeline-dot done"></div>
                            <div class="timeline-title">Demande créée</div>
                            <div class="timeline-date">{{ $fundingRequest->created_at->format('d/m/Y à H:i') }}</div>
                        </div>

                        @if($fundingRequest->paid_at)
                        <div class="timeline-item">
                            <div class="timeline-dot done"></div>
                            <div class="timeline-title">Frais d'inscription payés</div>
                            <div class="timeline-date">{{ $fundingRequest->paid_at->format('d/m/Y à H:i') }}</div>
                            <div class="timeline-sub">Réf. : {{ $fundingRequest->kkiapay_transaction_id }}</div>
                        </div>
                        @endif

                        @if($fundingRequest->submitted_at)
                        <div class="timeline-item">
                            <div class="timeline-dot done"></div>
                            <div class="timeline-title">Demande soumise</div>
                            <div class="timeline-date">{{ $fundingRequest->submitted_at->format('d/m/Y à H:i') }}</div>
                        </div>
                        @endif

                        @if($fundingRequest->reviewed_at)
                        <div class="timeline-item">
                            <div class="timeline-dot done"></div>
                            <div class="timeline-title">Examen démarré</div>
                            <div class="timeline-date">{{ $fundingRequest->reviewed_at->format('d/m/Y à H:i') }}</div>
                            @if($fundingRequest->reviewer)
                                <div class="timeline-sub">Par : {{ $fundingRequest->reviewer->full_name }}</div>
                            @endif
                        </div>
                        @endif

                        @if($fundingRequest->committee_review_started_at)
                        <div class="timeline-item">
                            <div class="timeline-dot {{ $fundingRequest->status === 'pending_committee' ? 'current' : 'done' }}"></div>
                            <div class="timeline-title">Soumis au comité</div>
                            <div class="timeline-date">{{ $fundingRequest->committee_review_started_at->format('d/m/Y à H:i') }}</div>
                        </div>
                        @endif

                        @if($fundingRequest->committee_decision_at)
                        <div class="timeline-item">
                            <div class="timeline-dot {{ $fundingRequest->status === 'approved' ? 'done' : 'fail' }}"></div>
                            <div class="timeline-title">Décision du comité</div>
                            <div class="timeline-date">{{ $fundingRequest->committee_decision_at->format('d/m/Y à H:i') }}</div>
                            <div style="margin-top:.3rem;">
                                <span class="badge {{ $fundingRequest->status === 'approved' ? 'badge-green' : 'badge-red' }}">
                                    {{ $fundingRequest->status === 'approved' ? 'Approuvée' : 'Rejetée' }}
                                </span>
                            </div>
                        </div>
                        @endif

                        @if($fundingRequest->final_fee_paid_at ?? false)
                        <div class="timeline-item">
                            <div class="timeline-dot done"></div>
                            <div class="timeline-title">Frais de dossier réglés</div>
                            <div class="timeline-date">{{ \Carbon\Carbon::parse($fundingRequest->final_fee_paid_at)->format('d/m/Y à H:i') }}</div>
                            <div class="timeline-sub">En attente de versement admin</div>
                        </div>
                        @endif

                        @if($fundingRequest->funded_at)
                        <div class="timeline-item">
                            <div class="timeline-dot done"></div>
                            <div class="timeline-title">Financement versé</div>
                            <div class="timeline-date">{{ $fundingRequest->funded_at->format('d/m/Y à H:i') }}</div>
                        </div>
                        @endif

                        @if(in_array($fundingRequest->status, ['draft','pending','submitted','under_review','pending_committee','pending_disbursement']))
                        <div class="timeline-item">
                            <div class="timeline-dot current"></div>
                            <div class="timeline-title" style="color:#f59e0b;">En cours…</div>
                        </div>
                        @endif

                    </div>
                </div>
            </div>

            {{-- ── Actions disponibles ── --}}
            @if(!empty($availableActions))
            <div class="card">
                <div class="card-header">
                    <span class="section-title">
                        <svg class="card-header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                        Actions disponibles
                    </span>
                </div>
                <div class="card-body">

                    {{-- Bannière versement en attente --}}
                    @if($fundingRequest->status === 'pending_disbursement')
                    <div class="disbursement-banner">
                        <div class="disbursement-banner-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="disbursement-banner-text">
                            <strong>Frais de dossier réglés par le client</strong>
                            <span>Le versement du montant approuvé est en attente de votre validation.</span>
                        </div>
                    </div>
                    @endif

                    <div class="actions-grid">
                        @foreach($availableActions as $action)
                        @switch($action)
                            @case('under_review')
                                <form method="POST" action="{{ route('admin.requests.status', $fundingRequest) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="under_review">
                                    <button type="submit" class="action-btn a-review" style="width:100%;">
                                        <span class="action-icon">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        </span>
                                        <span class="action-label">Démarrer l'examen</span>
                                    </button>
                                </form>
                                @break
                            @case('pending_committee')
                                <form method="POST" action="{{ route('admin.requests.status', $fundingRequest) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="pending_committee">
                                    <button type="submit" class="action-btn a-commit" style="width:100%;">
                                        <span class="action-icon">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        </span>
                                        <span class="action-label">Envoyer au comité</span>
                                    </button>
                                </form>
                                @break
                            @case('approved')
                                <button type="button" class="action-btn a-approve"
                                    onclick="document.getElementById('modal-approve').classList.add('open')" style="width:100%;">
                                    <span class="action-icon">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </span>
                                    <span class="action-label">Approuver la demande</span>
                                </button>
                                @break
                            @case('rejected')
                                <button type="button" class="action-btn a-reject"
                                    onclick="document.getElementById('modal-reject').classList.add('open')" style="width:100%;">
                                    <span class="action-icon">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </span>
                                    <span class="action-label">Rejeter la demande</span>
                                </button>
                                @break
                            @case('funded')
                                @if($fundingRequest->status === 'pending_disbursement')
                                    {{-- Versement après paiement des frais de dossier --}}
                                    <form method="POST" action="{{ route('admin.requests.approveDisbursement', $fundingRequest) }}"
                                        onsubmit="return confirm('Confirmer le versement de {{ number_format($amounts[\'net_amount\'], 0, \',\', \' \') }} FCFA sur le portefeuille du client ?')">
                                        @csrf
                                        <button type="submit" class="action-btn a-disburse" style="width:100%;">
                                            <span class="action-icon">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </span>
                                            <span class="action-label">Valider le versement</span>
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.requests.status', $fundingRequest) }}"
                                        onsubmit="return confirm('Marquer comme financée et verser le montant ?')">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="funded">
                                        <button type="submit" class="action-btn a-fund" style="width:100%;">
                                            <span class="action-icon">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </span>
                                            <span class="action-label">Marquer comme financée</span>
                                        </button>
                                    </form>
                                @endif
                                @break
                            @case('cancelled')
                                <form method="POST" action="{{ route('admin.requests.status', $fundingRequest) }}"
                                    onsubmit="return confirm('Annuler cette demande ?')">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="cancelled">
                                    <button type="submit" class="action-btn a-cancel" style="width:100%;">
                                        <span class="action-icon">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        </span>
                                        <span class="action-label">Annuler la demande</span>
                                    </button>
                                </form>
                                @break
                        @endswitch
                        @endforeach
                    </div>

                    {{-- Formulaire décision comité --}}
                    @if($fundingRequest->status === 'pending_committee')
                    <div style="margin-top:1.25rem; padding-top:1.25rem; border-top:1px solid var(--color-border);">
                        <div style="font-size:.82rem; font-weight:700; margin-bottom:.875rem; color:var(--color-text); display:flex; align-items:center; gap:.5rem;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="15" height="15" style="color:#6366f1;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                            Saisir la décision du comité
                        </div>
                        <form method="POST" action="{{ route('admin.requests.committee', $fundingRequest) }}">
                            @csrf
                            <div class="form-row-2">
                                <div>
                                    <label class="form-label">Décision <span style="color:#ef4444;">*</span></label>
                                    <select name="decision" class="form-control" required>
                                        <option value="">Choisir…</option>
                                        <option value="approved">Approuver</option>
                                        <option value="rejected">Rejeter</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Montant approuvé (FCFA)</label>
                                    <input type="number" name="amount_approved" class="form-control"
                                        value="{{ $fundingRequest->amount_requested }}" min="0" step="1000">
                                </div>
                            </div>
                            <div style="margin-bottom:.875rem;">
                                <label class="form-label">Motivation <span style="color:#ef4444;">*</span></label>
                                <textarea name="motivation" class="form-control" rows="3" required minlength="20"
                                    placeholder="Minimum 20 caractères…"></textarea>
                            </div>
                            <button type="submit" class="btn btn-indigo">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                Enregistrer la décision
                            </button>
                        </form>
                    </div>
                    @endif

                </div>
            </div>
            @endif

        </div>

        {{-- ════════════════════════════════
             COLONNE LATÉRALE
        ════════════════════════════════ --}}
        <div>

            {{-- Résumé financier --}}
            <div class="amounts-card">
                <div class="amounts-label">Montant demandé</div>
                <div class="amounts-value">{{ number_format($amounts['requested'], 0, ',', ' ') }} FCFA</div>

                @if($amounts['approved'] ?? false)
                <hr class="amounts-divider">
                <div class="amounts-label">Montant approuvé</div>
                <div class="amounts-value" style="font-size:1.35rem; color:#a7f3d0;">{{ number_format($amounts['approved'], 0, ',', ' ') }} FCFA</div>
                @endif

                <hr class="amounts-divider">
                <div class="amounts-mini-grid">
                    <div>
                        <div class="amounts-mini-label">Frais inscription</div>
                        <div class="amounts-mini-value">{{ number_format($amounts['registration_fee'], 0, ',', ' ') }} FCFA</div>
                    </div>
                    <div>
                        <div class="amounts-mini-label">Frais dossier</div>
                        <div class="amounts-mini-value">{{ number_format($amounts['final_fee'], 0, ',', ' ') }} FCFA</div>
                    </div>
                </div>

                @if($amounts['approved'] ?? false)
                <hr class="amounts-divider">
                <div class="amounts-label">Montant à verser</div>
                <div class="amounts-value" style="font-size:1.2rem;">{{ number_format($amounts['net_amount'], 0, ',', ' ') }} FCFA</div>
                @endif
            </div>

            {{-- Paiement --}}
            <div class="card">
                <div class="card-header">
                    <span class="section-title">
                        <svg class="card-header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        Paiement
                    </span>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <span class="info-label">Statut</span>
                        <span class="badge {{ $fundingRequest->payment_status === 'paid' ? 'badge-green' : ($fundingRequest->payment_status === 'failed' ? 'badge-red' : 'badge-yellow') }}">
                            {{ $fundingRequest->getPaymentStatusLabel() }}
                        </span>
                    </div>
                    @if($fundingRequest->registration_fee_paid)
                    <div class="info-row">
                        <span class="info-label">Montant payé</span>
                        <span class="info-value">{{ number_format($fundingRequest->registration_fee_paid, 0, ',', ' ') }} FCFA</span>
                    </div>
                    @endif
                    @if($fundingRequest->final_fee_paid ?? false)
                    <div class="info-row">
                        <span class="info-label">Frais de dossier</span>
                        <span class="badge badge-green">Réglés</span>
                    </div>
                    @endif
                    @if($fundingRequest->kkiapay_transaction_id)
                    <div class="info-row">
                        <span class="info-label">Réf. transaction</span>
                        <span style="font-family:monospace; font-size:.72rem;">{{ Str::limit($fundingRequest->kkiapay_transaction_id, 22) }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Entreprise --}}
            @if($fundingRequest->company)
            <div class="card">
                <div class="card-header">
                    <span class="section-title">
                        <svg class="card-header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Entreprise
                    </span>
                </div>
                <div class="card-body">
                    <div style="font-weight:800; font-size:.9rem; margin-bottom:.2rem;">{{ $fundingRequest->company->company_name }}</div>
                    <div style="font-size:.78rem; color:var(--color-text-muted); margin-bottom:.75rem;">{{ $fundingRequest->company->getCompanyTypeLabelAttribute() }}</div>
                    <div class="info-row">
                        <span class="info-label">Secteur</span>
                        <span class="info-value">{{ $fundingRequest->company->getSectorLabelAttribute() }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Employés</span>
                        <span class="info-value">{{ $fundingRequest->company->employees_count }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Ville</span>
                        <span class="info-value">{{ $fundingRequest->company->city }}</span>
                    </div>
                </div>
            </div>
            @endif

            {{-- Assigné à --}}
            @if($fundingRequest->reviewer)
            <div class="card">
                <div class="card-header">
                    <span class="section-title">
                        <svg class="card-header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Assigné à
                    </span>
                </div>
                <div class="card-body">
                    <div style="display:flex; align-items:center; gap:.75rem;">
                        <div style="width:38px; height:38px; border-radius:50%; background:linear-gradient(135deg,#06b6d4,#3b82f6); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:800; flex-shrink:0;">
                            {{ strtoupper(substr($fundingRequest->reviewer->first_name,0,1)) }}
                        </div>
                        <div>
                            <div style="font-weight:700; font-size:.875rem;">{{ $fundingRequest->reviewer->full_name }}</div>
                            <div style="font-size:.75rem; color:var(--color-text-muted);">Depuis {{ $fundingRequest->reviewed_at->diffForHumans() }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Notes internes --}}
            <div class="card">
                <div class="card-header">
                    <span class="section-title">
                        <svg class="card-header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Notes internes
                    </span>
                </div>
                <div class="card-body">
                    <form action="#" method="POST">
                        @csrf
                        <div style="margin-bottom:.75rem;">
                            <textarea name="note" class="form-control" rows="3"
                                placeholder="Ajouter une note interne…"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-full btn-sm">Ajouter la note</button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    {{-- ── Modal Assignation ── --}}
    <div class="modal-backdrop" id="modal-assign">
        <div class="modal-box">
            <div class="modal-header-row">
                <div class="modal-title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18" style="color:#3b82f6;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    Assigner un examinateur
                </div>
                <button class="modal-close" onclick="document.getElementById('modal-assign').classList.remove('open')">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.requests.assign', $fundingRequest) }}">
                @csrf
                <div style="margin-bottom:.875rem;">
                    <label class="form-label">Examinateur <span style="color:#ef4444;">*</span></label>
                    <select name="reviewer_id" class="form-control" required>
                        <option value="">Choisir un administrateur…</option>
                        @foreach(\App\Models\User::where('is_admin', true)->orderBy('first_name')->get() as $admin)
                            <option value="{{ $admin->id }}" {{ $fundingRequest->reviewer_id == $admin->id ? 'selected' : '' }}>
                                {{ $admin->full_name }} — {{ $admin->email }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn btn-primary" style="flex:1; justify-content:center;">Confirmer l'assignation</button>
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('modal-assign').classList.remove('open')">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Modal Approbation ── --}}
    <div class="modal-backdrop" id="modal-approve">
        <div class="modal-box">
            <div class="modal-header-row">
                <div class="modal-title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18" style="color:#10b981;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Approuver la demande
                </div>
                <button class="modal-close" onclick="document.getElementById('modal-approve').classList.remove('open')">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.requests.status', $fundingRequest) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="approved">
                <div style="margin-bottom:.875rem;">
                    <label class="form-label">Montant approuvé (FCFA) <span style="color:#ef4444;">*</span></label>
                    <input type="number" name="amount_approved" class="form-control"
                        value="{{ $fundingRequest->amount_requested }}" min="0" step="1000" required>
                </div>
                <div style="margin-bottom:.875rem;">
                    <label class="form-label">Commentaire (optionnel)</label>
                    <textarea name="comment" class="form-control" rows="3" placeholder="Note pour le dossier…"></textarea>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn btn-success" style="flex:1; justify-content:center;">Confirmer l'approbation</button>
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('modal-approve').classList.remove('open')">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Modal Rejet ── --}}
    <div class="modal-backdrop" id="modal-reject">
        <div class="modal-box">
            <div class="modal-header-row">
                <div class="modal-title" style="color:#dc2626;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Rejeter la demande
                </div>
                <button class="modal-close" onclick="document.getElementById('modal-reject').classList.remove('open')">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="modal-alert">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16" style="flex-shrink:0; margin-top:.05rem;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Cette action est irréversible. Le demandeur sera notifié du rejet.
            </div>
            <form method="POST" action="{{ route('admin.requests.status', $fundingRequest) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="rejected">
                <div style="margin-bottom:.875rem;">
                    <label class="form-label">Motif du rejet <span style="color:#ef4444;">*</span></label>
                    <textarea name="comment" class="form-control" rows="3" required
                        placeholder="Expliquez le motif au demandeur…"></textarea>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn btn-danger" style="flex:1; justify-content:center;">Confirmer le rejet</button>
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('modal-reject').classList.remove('open')">Annuler</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animation progress bar documents
        const fill = document.getElementById('docProgress');
        if (fill) {
            const w = fill.style.width;
            fill.style.width = '0%';
            setTimeout(() => { fill.style.width = w; }, 150);
        }
        // Fermer modals en cliquant backdrop
        document.querySelectorAll('.modal-backdrop').forEach(b => {
            b.addEventListener('click', e => { if (e.target === b) b.classList.remove('open'); });
        });
    });
</script>
@endsection
