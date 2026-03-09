@extends('layouts.admin')

@section('title', 'Documents requis par financement')
@section('header-title', 'Documents requis par financement')

@section('styles')
<style>
    /* ── Layout ──────────────────────────── */
    .page-intro {
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        box-shadow: var(--shadow-sm);
    }
    .page-intro p { font-size: .875rem; color: var(--color-text-muted); max-width: 600px; }

    /* ── Cards grille ───────────────────── */
    .tf-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 1.25rem;
    }

    .tf-card {
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: box-shadow .2s;
    }
    .tf-card:hover { box-shadow: var(--shadow-md); }

    .tf-card-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--color-border);
        display: flex;
        align-items: flex-start;
        gap: .75rem;
    }

    .tf-icon {
        width: 40px; height: 40px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        font-size: .8rem; font-weight: 800; color: #fff;
    }
    .tf-icon.particulier { background: linear-gradient(135deg,#3b82f6,#1d4ed8); }
    .tf-icon.entreprise  { background: linear-gradient(135deg,#8b5cf6,#6d28d9); }
    .tf-icon.admin       { background: linear-gradient(135deg,#64748b,#334155); }

    .tf-card-title { font-size: .9rem; font-weight: 700; color: var(--color-text); line-height: 1.3; }
    .tf-card-code  { font-size: .72rem; font-family: monospace; color: var(--color-text-muted); margin-top: .1rem; }

    .tf-card-body { padding: 1rem 1.25rem; flex: 1; }

    .doc-count-line {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: .75rem;
    }
    .doc-count-label { font-size: .78rem; color: var(--color-text-muted); font-weight: 500; }
    .doc-count-num {
        font-size: 1.25rem; font-weight: 800; color: var(--color-text);
        letter-spacing: -.02em;
    }
    .doc-count-num.zero { color: #94a3b8; }

    .doc-chips { display: flex; flex-wrap: wrap; gap: .375rem; min-height: 28px; }

    .doc-chip {
        display: inline-flex; align-items: center; gap: .25rem;
        padding: .2rem .6rem;
        background: #f0f9ff; color: #0369a1;
        border: 1px solid #bae6fd;
        border-radius: 9999px;
        font-size: .72rem; font-weight: 600;
    }
    .doc-chip.entreprise { background: #faf5ff; color: #6d28d9; border-color: #e9d5ff; }
    .doc-chip.admin      { background: #f8fafc; color: #475569; border-color: #cbd5e1; }
    .doc-chip-none { font-size: .78rem; color: #94a3b8; font-style: italic; }

    .tf-card-footer {
        padding: .875rem 1.25rem;
        border-top: 1px solid #f1f5f9;
        display: flex; align-items: center; justify-content: space-between;
    }

    /* ── Badges ─────────────────────────── */
    .badge { display: inline-flex; align-items: center; padding: .2rem .65rem; border-radius: 9999px; font-size: .7rem; font-weight: 600; }
    .badge-green  { background: #dcfce7; color: #166534; }
    .badge-gray   { background: #f1f5f9; color: #475569; }
    .badge-blue   { background: #dbeafe; color: #1e40af; }
    .badge-purple { background: #f3e8ff; color: #6d28d9; }

    /* ── Boutons ────────────────────────── */
    .btn { display: inline-flex; align-items: center; gap: .375rem; padding: .45rem .875rem; border-radius: 8px; font-size: .8rem; font-weight: 600; text-decoration: none; border: none; cursor: pointer; transition: all .15s; font-family: inherit; }
    .btn-sm { padding: .3rem .65rem; font-size: .75rem; }
    .btn-primary   { background: #3b82f6; color: #fff; }
    .btn-primary:hover   { background: #2563eb; color: #fff; }
    .btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid var(--color-border); }
    .btn-secondary:hover { background: #e2e8f0; }
</style>
@endsection

@section('content')

    <div class="page-intro">
        <div>
            <p>Associez les documents requis à chaque type de financement. Ces documents seront demandés automatiquement aux clients après le paiement de leur dossier.</p>
        </div>
        <a href="{{ route('admin.typefinancements.index') }}" class="btn btn-secondary">
            ← Types de financement
        </a>
    </div>

    <div class="tf-grid">
        @forelse($typeFinancements as $tf)
        @php
            $docs = $tf->requiredTypeDocs;
            $count = $docs->count();
            $initials = strtoupper(substr($tf->name, 0, 2));
        @endphp
        <div class="tf-card">
            <div class="tf-card-header">
                <div class="tf-icon {{ $tf->typeusers }}">{{ $initials }}</div>
                <div style="flex:1; min-width:0;">
                    <div class="tf-card-title">{{ $tf->name }}</div>
                    <div class="tf-card-code">{{ $tf->code }}</div>
                </div>
                @if($tf->is_active)
                    <span class="badge badge-green">Actif</span>
                @else
                    <span class="badge badge-gray">Inactif</span>
                @endif
            </div>

            <div class="tf-card-body">
                <div class="doc-count-line">
                    <span class="doc-count-label">Documents requis</span>
                    <span class="doc-count-num {{ $count === 0 ? 'zero' : '' }}">{{ $count }}</span>
                </div>
                <div class="doc-chips">
                    @forelse($docs as $doc)
                        <span class="doc-chip {{ $doc->typeusers }}">
                            <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            {{ $doc->name }}
                        </span>
                    @empty
                        <span class="doc-chip-none">Aucun document configuré</span>
                    @endforelse
                </div>
            </div>

            <div class="tf-card-footer">
                <span class="badge {{ $tf->typeusers === 'particulier' ? 'badge-blue' : ($tf->typeusers === 'entreprise' ? 'badge-purple' : 'badge-gray') }}">
                    {{ ucfirst($tf->typeusers) }}
                </span>
                <a href="{{ route('admin.typefinancements.documents.edit', $tf) }}" class="btn btn-primary btn-sm">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="13" height="13"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Configurer
                </a>
            </div>
        </div>
        @empty
            <div style="grid-column:1/-1; text-align:center; padding:3rem; color:var(--color-text-muted);">
                Aucun type de financement. <a href="{{ route('admin.typefinancements.create') }}">En créer un</a>
            </div>
        @endforelse
    </div>

@endsection
