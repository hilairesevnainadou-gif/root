@extends('layouts.admin')

@section('title', 'Documents — ' . $typeFinancement->name)
@section('header-title', 'Documents requis')

@section('styles')
<style>
    /* ── Layout 2 colonnes ──────────────── */
    .edit-grid {
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 1.5rem;
        align-items: start;
    }

    /* ── Card générique ─────────────────── */
    .card {
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }
    .card-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--color-border);
        font-size: .9rem; font-weight: 700;
        color: var(--color-text); letter-spacing: -.02em;
    }
    .card-body { padding: 1.25rem; }

    /* ── Infos financement ──────────────── */
    .info-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: .5rem 0; border-bottom: 1px solid #f8fafc;
        font-size: .84rem;
    }
    .info-row:last-child { border: none; }
    .info-label { color: var(--color-text-muted); font-weight: 500; }
    .info-value { font-weight: 600; color: var(--color-text); }

    /* ── Badges ─────────────────────────── */
    .badge { display: inline-flex; align-items: center; padding: .2rem .65rem; border-radius: 9999px; font-size: .7rem; font-weight: 600; }
    .badge-green  { background: #dcfce7; color: #166534; }
    .badge-gray   { background: #f1f5f9; color: #475569; }
    .badge-blue   { background: #dbeafe; color: #1e40af; }
    .badge-purple { background: #f3e8ff; color: #6d28d9; }
    .badge-red    { background: #fee2e2; color: #991b1b; }

    /* ── Documents sélectionnables ──────── */
    .doc-list { display: flex; flex-direction: column; gap: .5rem; }

    .doc-item {
        display: flex; align-items: center; gap: .875rem;
        padding: .875rem 1rem;
        border: 1.5px solid var(--color-border);
        border-radius: 10px;
        cursor: pointer;
        transition: border-color .15s, background .15s;
        user-select: none;
    }
    .doc-item:hover { border-color: #93c5fd; background: #f0f9ff; }
    .doc-item.selected {
        border-color: #3b82f6;
        background: #eff6ff;
    }
    .doc-item.selected .doc-check { background: #3b82f6; border-color: #3b82f6; }
    .doc-item.selected .doc-check::after { opacity: 1; }

    .doc-check {
        width: 20px; height: 20px;
        border-radius: 6px;
        border: 2px solid #cbd5e1;
        background: #fff;
        flex-shrink: 0;
        position: relative;
        transition: all .15s;
    }
    .doc-check::after {
        content: '';
        position: absolute;
        top: 3px; left: 5px;
        width: 6px; height: 9px;
        border: 2px solid #fff;
        border-top: none; border-left: none;
        transform: rotate(45deg);
        opacity: 0;
        transition: opacity .1s;
    }

    /* Checkbox réelle cachée */
    .doc-item input[type="checkbox"] { display: none; }

    .doc-info { flex: 1; min-width: 0; }
    .doc-name { font-size: .875rem; font-weight: 600; color: var(--color-text); }
    .doc-desc { font-size: .75rem; color: var(--color-text-muted); margin-top: .1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .doc-type-badge {
        display: inline-flex; padding: .15rem .5rem;
        border-radius: 9999px; font-size: .68rem; font-weight: 600; flex-shrink: 0;
    }
    .doc-type-badge.particulier { background: #dbeafe; color: #1e40af; }
    .doc-type-badge.entreprise  { background: #f3e8ff; color: #6d28d9; }
    .doc-type-badge.admin       { background: #f1f5f9; color: #475569; }

    /* ── Section docs actuels ───────────── */
    .current-docs { display: flex; flex-direction: column; gap: .5rem; }

    .current-doc-row {
        display: flex; align-items: center; justify-content: space-between;
        padding: .75rem 1rem;
        background: #f8fafc; border-radius: 8px;
        border: 1px solid #e2e8f0;
    }
    .current-doc-name { font-size: .85rem; font-weight: 600; display: flex; align-items: center; gap: .5rem; }
    .current-doc-none { font-size: .85rem; color: #94a3b8; font-style: italic; padding: .75rem 0; text-align: center; }

    /* ── Compteur sélection ─────────────── */
    .selection-bar {
        background: #eff6ff; border: 1px solid #bfdbfe;
        border-radius: 10px; padding: .875rem 1.25rem;
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 1rem;
    }
    .selection-count { font-size: .875rem; font-weight: 700; color: #1e40af; }
    .selection-hint  { font-size: .78rem; color: #3b82f6; }

    /* ── Recherche docs ─────────────────── */
    .search-docs {
        width: 100%; padding: .55rem .875rem;
        border: 1px solid var(--color-border);
        border-radius: 8px; font-size: .875rem; font-family: inherit;
        outline: none; margin-bottom: .875rem;
        transition: border-color .15s;
    }
    .search-docs:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.1); }

    /* ── Boutons ────────────────────────── */
    .btn { display: inline-flex; align-items: center; gap: .375rem; padding: .5rem 1rem; border-radius: 8px; font-size: .85rem; font-weight: 600; text-decoration: none; border: none; cursor: pointer; transition: all .15s; font-family: inherit; }
    .btn-sm { padding: .3rem .65rem; font-size: .75rem; }
    .btn-primary  { background: #3b82f6; color: #fff; }
    .btn-primary:hover  { background: #2563eb; color: #fff; }
    .btn-danger   { background: #ef4444; color: #fff; }
    .btn-danger:hover   { background: #dc2626; color: #fff; }
    .btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid var(--color-border); }
    .btn-secondary:hover { background: #e2e8f0; }
    .btn-full { width: 100%; justify-content: center; }
    .btn-ghost { background: none; border: none; padding: .2rem .4rem; color: var(--color-text-muted); cursor: pointer; border-radius: 6px; display: inline-flex; }
    .btn-ghost:hover { background: #fee2e2; color: #ef4444; }

    /* ── Tip ────────────────────────────── */
    .tip { background: #fefce8; border: 1px solid #fde68a; border-radius: 8px; padding: .75rem 1rem; font-size: .8rem; color: #92400e; margin-bottom: 1rem; }

    @media (max-width: 900px) { .edit-grid { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')

<div style="margin-bottom:1rem; display:flex; align-items:center; gap:.75rem; flex-wrap:wrap;">
    <a href="{{ route('admin.typefinancements.documents') }}" class="btn btn-secondary btn-sm">← Retour</a>
    <span style="font-size:.8rem; color:var(--color-text-muted);">
        Configuration des documents requis pour :
        <strong style="color:var(--color-text);">{{ $typeFinancement->name }}</strong>
    </span>
</div>

<div class="edit-grid">

    {{-- ── Colonne gauche : infos + docs actuels ── --}}
    <div>

        {{-- Infos financement --}}
        <div class="card">
            <div class="card-header">Financement</div>
            <div class="card-body">
                <div class="info-row">
                    <span class="info-label">Nom</span>
                    <span class="info-value">{{ $typeFinancement->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Code</span>
                    <span class="info-value" style="font-family:monospace; font-size:.8rem;">{{ $typeFinancement->code }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Cible</span>
                    <span class="badge {{ $typeFinancement->typeusers === 'particulier' ? 'badge-blue' : ($typeFinancement->typeusers === 'entreprise' ? 'badge-purple' : 'badge-gray') }}">
                        {{ ucfirst($typeFinancement->typeusers) }}
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Statut</span>
                    <span class="badge {{ $typeFinancement->is_active ? 'badge-green' : 'badge-gray' }}">
                        {{ $typeFinancement->is_active ? 'Actif' : 'Inactif' }}
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Durée</span>
                    <span class="info-value">{{ $typeFinancement->duration_months }} mois</span>
                </div>
            </div>
        </div>

        {{-- Documents actuellement associés --}}
        <div class="card">
            <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                <span>Documents associés</span>
                <span class="badge badge-blue" id="attached-counter">{{ count($attachedIds) }}</span>
            </div>
            <div class="card-body">
                <div class="current-docs" id="current-docs-list">
                    @forelse($compatibleDocs->whereIn('id', $attachedIds) as $doc)
                        <div class="current-doc-row" id="current-{{ $doc->id }}">
                            <div class="current-doc-name">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="15" height="15" style="color:#3b82f6; flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                {{ $doc->name }}
                            </div>
                            <span class="doc-type-badge {{ $doc->typeusers }}">{{ ucfirst($doc->typeusers) }}</span>
                        </div>
                    @empty
                        <div class="current-doc-none" id="no-docs-msg">Aucun document sélectionné</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="tip">
            💡 Seuls les documents compatibles avec le type <strong>« {{ ucfirst($typeFinancement->typeusers) }} »</strong> sont affichés.
        </div>

    </div>

    {{-- ── Colonne droite : sélecteur ── --}}
    <div>
        <div class="card">
            <div class="card-header">Sélectionner les documents requis</div>
            <div class="card-body">

                <form method="POST" action="{{ route('admin.typefinancements.documents.sync', $typeFinancement) }}" id="sync-form">
                    @csrf

                    <div class="selection-bar">
                        <span class="selection-count" id="sel-count">{{ count($attachedIds) }} document(s) sélectionné(s)</span>
                        <span class="selection-hint">Cochez pour ajouter / décochez pour retirer</span>
                    </div>

                    <input type="text" class="search-docs" placeholder="Rechercher un document…" id="doc-search">

                    @if($compatibleDocs->isEmpty())
                        <div style="text-align:center; padding:2rem; color:var(--color-text-muted); font-size:.875rem;">
                            Aucun document compatible trouvé pour le type « {{ ucfirst($typeFinancement->typeusers) }} ».<br>
                            <a href="{{ route('admin.typedocs.index') }}" style="color:#3b82f6;">Créer des types de documents</a>
                        </div>
                    @else
                        <div class="doc-list" id="doc-list">
                            @foreach($compatibleDocs as $doc)
                            @php $isChecked = in_array($doc->id, $attachedIds); @endphp
                            <label class="doc-item {{ $isChecked ? 'selected' : '' }}" data-doc-name="{{ strtolower($doc->name) }}">
                                <input type="checkbox"
                                    name="typedoc_ids[]"
                                    value="{{ $doc->id }}"
                                    {{ $isChecked ? 'checked' : '' }}
                                    onchange="onDocChange(this, {{ $doc->id }}, '{{ addslashes($doc->name) }}', '{{ $doc->typeusers }}')">
                                <span class="doc-check"></span>
                                <div class="doc-info">
                                    <div class="doc-name">{{ $doc->name }}</div>
                                    @if($doc->description)
                                        <div class="doc-desc">{{ $doc->description }}</div>
                                    @endif
                                </div>
                                <span class="doc-type-badge {{ $doc->typeusers }}">{{ ucfirst($doc->typeusers) }}</span>
                            </label>
                            @endforeach
                        </div>
                    @endif

                    <div style="display:flex; gap:.75rem; margin-top:1.5rem; flex-wrap:wrap;">
                        <button type="submit" class="btn btn-primary" style="flex:1; justify-content:center;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="15" height="15"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Enregistrer les associations
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="deselectAll()">Tout désélectionner</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

</div>

@endsection

@section('scripts')
<script>
    // ── Données initiales ──────────────────────────
    const attachedIds = @json($attachedIds);
    let selectedIds   = new Set(attachedIds);

    // Noms pour le panneau gauche (pré-rempli)
    const docMeta = {};
    @foreach($compatibleDocs as $doc)
    docMeta[{{ $doc->id }}] = { name: '{{ addslashes($doc->name) }}', typeusers: '{{ $doc->typeusers }}' };
    @endforeach

    // ── Mise à jour compteur & panneau ─────────────
    function updateUI() {
        const count = selectedIds.size;
        document.getElementById('sel-count').textContent = count + ' document(s) sélectionné(s)';
        document.getElementById('attached-counter').textContent = count;
        renderCurrentDocs();
    }

    function renderCurrentDocs() {
        const container = document.getElementById('current-docs-list');
        if (selectedIds.size === 0) {
            container.innerHTML = '<div class="current-doc-none">Aucun document sélectionné</div>';
            return;
        }
        let html = '';
        selectedIds.forEach(id => {
            const m = docMeta[id];
            if (!m) return;
            html += `
                <div class="current-doc-row">
                    <div class="current-doc-name">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="15" height="15" style="color:#3b82f6;flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        ${m.name}
                    </div>
                    <span class="doc-type-badge ${m.typeusers}">${m.typeusers.charAt(0).toUpperCase() + m.typeusers.slice(1)}</span>
                </div>`;
        });
        container.innerHTML = html;
    }

    // ── Changement checkbox ────────────────────────
    function onDocChange(checkbox, id, name, typeusers) {
        const label = checkbox.closest('.doc-item');
        if (checkbox.checked) {
            selectedIds.add(id);
            label.classList.add('selected');
        } else {
            selectedIds.delete(id);
            label.classList.remove('selected');
        }
        updateUI();
    }

    // ── Désélectionner tout ────────────────────────
    function deselectAll() {
        document.querySelectorAll('#doc-list input[type="checkbox"]').forEach(cb => {
            cb.checked = false;
            cb.closest('.doc-item').classList.remove('selected');
        });
        selectedIds.clear();
        updateUI();
    }

    // ── Recherche ──────────────────────────────────
    document.getElementById('doc-search')?.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        document.querySelectorAll('#doc-list .doc-item').forEach(item => {
            const name = item.dataset.docName || '';
            item.style.display = name.includes(q) ? '' : 'none';
        });
    });
</script>
@endsection
