@extends('layouts.admin')

@section('title', 'Documents en attente')
@section('header-title', 'Vérification des Documents')

@section('styles')
<style>
    /* ── Stats ───────────────────────────── */
    .doc-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
    .doc-stat  { background: var(--color-white); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 1.1rem 1.25rem; box-shadow: var(--shadow-sm); }
    .doc-stat-label { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--color-text-muted); margin-bottom: .25rem; }
    .doc-stat-value { font-size: 1.75rem; font-weight: 800; letter-spacing: -.03em; }

    /* ── Badges ─────────────────────────── */
    .badge { display: inline-flex; align-items: center; padding: .2rem .65rem; border-radius: 9999px; font-size: .7rem; font-weight: 600; white-space: nowrap; }
    .badge-yellow { background: #fef9c3; color: #854d0e; }
    .badge-green  { background: #dcfce7; color: #166534; }
    .badge-red    { background: #fee2e2; color: #991b1b; }
    .badge-blue   { background: #dbeafe; color: #1e40af; }
    .badge-gray   { background: #f1f5f9; color: #475569; }

    /* ── Boutons ────────────────────────── */
    .btn { display: inline-flex; align-items: center; gap: .375rem; padding: .45rem .875rem; border-radius: 8px; font-size: .8rem; font-weight: 600; text-decoration: none; border: none; cursor: pointer; transition: all .15s; font-family: inherit; }
    .btn-sm { padding: .3rem .65rem; font-size: .75rem; }
    .btn-primary   { background: #3b82f6; color: #fff; }
    .btn-primary:hover   { background: #2563eb; color: #fff; }
    .btn-success   { background: #10b981; color: #fff; }
    .btn-success:hover   { background: #059669; color: #fff; }
    .btn-danger    { background: #ef4444; color: #fff; }
    .btn-danger:hover    { background: #dc2626; color: #fff; }
    .btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid var(--color-border); }
    .btn-secondary:hover { background: #e2e8f0; }

    /* ── Bulk bar ───────────────────────── */
    .bulk-bar {
        background: #eff6ff; border: 1px solid #bfdbfe;
        border-radius: 10px; padding: .875rem 1.25rem;
        display: none; align-items: center; justify-content: space-between;
        margin-bottom: 1rem; gap: 1rem; flex-wrap: wrap;
    }
    .bulk-bar.visible { display: flex; }
    .bulk-info { font-size: .875rem; font-weight: 700; color: #1e40af; }
    .bulk-actions { display: flex; gap: .5rem; align-items: center; flex-wrap: wrap; }

    /* ── Filtre type ────────────────────── */
    .type-pills { display: flex; gap: .375rem; flex-wrap: wrap; margin-bottom: 1.25rem; }
    .type-pill { display: inline-flex; align-items: center; gap: .3rem; padding: .3rem .75rem; border-radius: 9999px; font-size: .75rem; font-weight: 600; text-decoration: none; border: 1px solid var(--color-border); background: var(--color-white); color: var(--color-text-muted); transition: all .15s; }
    .type-pill:hover, .type-pill.active { background: #3b82f6; border-color: #3b82f6; color: #fff; }
    .type-pill-count { background: rgba(0,0,0,.12); border-radius: 9999px; padding: 0 .4rem; font-size: .68rem; }

    /* ── Checkbox col ───────────────────── */
    .check-col { width: 40px; }
    input[type="checkbox"].doc-check-row { width: 16px; height: 16px; cursor: pointer; accent-color: #3b82f6; }

    /* ── File icon ──────────────────────── */
    .file-icon {
        width: 36px; height: 36px; border-radius: 8px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: .65rem; font-weight: 700;
    }
    .file-icon.pdf   { background: #fee2e2; color: #991b1b; }
    .file-icon.image { background: #e0f2fe; color: #0369a1; }
    .file-icon.word  { background: #dbeafe; color: #1e40af; }
    .file-icon.other { background: #f1f5f9; color: #475569; }

    .doc-cell { display: flex; align-items: center; gap: .75rem; }
    .doc-name  { font-weight: 600; font-size: .85rem; color: var(--color-text); }
    .doc-user  { font-size: .75rem; color: var(--color-text-muted); }

    /* ── Modal rejet ────────────────────── */
    .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 500; align-items: center; justify-content: center; }
    .modal-backdrop.open { display: flex; }
    .modal-box { background: #fff; border-radius: var(--radius-lg); padding: 1.75rem; width: 100%; max-width: 440px; box-shadow: 0 20px 60px rgba(0,0,0,.15); }
    .modal-title { font-size: 1rem; font-weight: 700; margin-bottom: 1rem; }
    .modal-label { display: block; font-size: .8rem; font-weight: 600; margin-bottom: .35rem; }
    .modal-textarea { width: 100%; padding: .5rem .75rem; border: 1px solid var(--color-border); border-radius: 8px; font-size: .875rem; font-family: inherit; outline: none; resize: vertical; }
    .modal-textarea:focus { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,.1); }
    .modal-actions { display: flex; gap: .75rem; margin-top: 1.25rem; }

    @media (max-width: 900px) { .doc-stats { grid-template-columns: repeat(2, 1fr); } }
</style>
@endsection

@section('content')

    {{-- Stats --}}
    <div class="doc-stats">
        <div class="doc-stat">
            <div class="doc-stat-label">En attente</div>
            <div class="doc-stat-value" style="color:#f59e0b;">{{ $stats['total_pending'] }}</div>
        </div>
        <div class="doc-stat">
            <div class="doc-stat-label">Types différents</div>
            <div class="doc-stat-value">{{ $stats['by_type']->count() }}</div>
        </div>
        <div class="doc-stat">
            <div class="doc-stat-label">Vérifiés aujourd'hui</div>
            <div class="doc-stat-value" style="color:#10b981;">
                {{ \App\Models\DocumentUser::where('status','verified')->whereDate('verified_at', today())->count() }}
            </div>
        </div>
        <div class="doc-stat">
            <div class="doc-stat-label">Rejetés aujourd'hui</div>
            <div class="doc-stat-value" style="color:#ef4444;">
                {{ \App\Models\DocumentUser::where('status','rejected')->whereDate('updated_at', today())->count() }}
            </div>
        </div>
    </div>

    {{-- Filtres par type de doc --}}
    @if($stats['by_type']->isNotEmpty())
    <div class="type-pills">
        <a href="{{ route('admin.documents.pending') }}"
           class="type-pill {{ !request('typedoc_id') ? 'active' : '' }}">
            Tous
            <span class="type-pill-count">{{ $stats['total_pending'] }}</span>
        </a>
        @foreach($stats['by_type'] as $byType)
        <a href="{{ route('admin.documents.pending', ['typedoc_id' => $byType->typedoc_id]) }}"
           class="type-pill {{ request('typedoc_id') == $byType->typedoc_id ? 'active' : '' }}">
            {{ $byType->typeDoc->name ?? 'Type #'.$byType->typedoc_id }}
            <span class="type-pill-count">{{ $byType->count }}</span>
        </a>
        @endforeach
    </div>
    @endif

    {{-- Bulk bar --}}
    <div class="bulk-bar" id="bulkBar">
        <span class="bulk-info" id="bulkCount">0 sélectionné(s)</span>
        <div class="bulk-actions">
            <form method="POST" action="{{ route('admin.documents.bulk') }}" id="bulkApproveForm">
                @csrf
                <input type="hidden" name="status" value="verified">
                <div id="bulkApproveIds"></div>
                <button type="submit" class="btn btn-sm btn-success"
                    onclick="return confirm('Approuver tous les documents sélectionnés ?')">
                    ✓ Approuver la sélection
                </button>
            </form>
            <button type="button" class="btn btn-sm btn-danger" onclick="openBulkRejectModal()">
                ✕ Rejeter la sélection
            </button>
            <button type="button" class="btn btn-sm btn-secondary" onclick="deselectAll()">Annuler</button>
        </div>
    </div>

    {{-- Table --}}
    <div class="data-table">
        <div style="padding:.875rem 1.25rem; border-bottom:1px solid var(--color-border); display:flex; align-items:center; gap:.75rem;">
            <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                style="width:16px; height:16px; cursor:pointer; accent-color:#3b82f6;">
            <span style="font-size:.82rem; color:var(--color-text-muted);">Tout sélectionner</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th class="check-col"></th>
                    <th>Document</th>
                    <th>Utilisateur</th>
                    <th>Demande</th>
                    <th>Taille</th>
                    <th>Soumis le</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $doc)
                @php
                    $ext = strtolower(pathinfo($doc->file_name ?? '', PATHINFO_EXTENSION));
                    $iconClass = match(true) {
                        in_array($ext, ['pdf'])              => 'pdf',
                        in_array($ext, ['jpg','jpeg','png','gif','webp']) => 'image',
                        in_array($ext, ['doc','docx'])       => 'word',
                        default => 'other'
                    };
                    $iconLabel = match($iconClass) { 'pdf'=>'PDF', 'image'=>'IMG', 'word'=>'DOC', default=>'FIL' };
                @endphp
                <tr id="row-{{ $doc->id }}">
                    <td class="check-col">
                        <input type="checkbox" class="doc-check-row" value="{{ $doc->id }}"
                            onchange="onRowCheck()">
                    </td>
                    <td>
                        <div class="doc-cell">
                            <div class="file-icon {{ $iconClass }}">{{ $iconLabel }}</div>
                            <div>
                                <div class="doc-name">{{ $doc->typeDoc->name ?? 'Document' }}</div>
                                <div class="doc-user" style="font-size:.72rem;">{{ $doc->file_name }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="font-weight:600; font-size:.85rem;">{{ $doc->user->full_name ?? '—' }}</div>
                        <div style="font-size:.75rem; color:var(--color-text-muted);">{{ $doc->user->email ?? '' }}</div>
                    </td>
                    <td style="font-size:.8rem;">
                        @if($doc->fundingRequest)
                            <a href="{{ route('admin.requests.show', $doc->fundingRequest) }}"
                               style="color:#3b82f6; font-weight:600; text-decoration:none;">
                                {{ $doc->fundingRequest->request_number ?? '#'.$doc->fundingRequest->id }}
                            </a>
                            <div style="font-size:.72rem; color:var(--color-text-muted);">{{ $doc->fundingRequest->typeFinancement->name ?? '' }}</div>
                        @else
                            <span style="color:#94a3b8;">—</span>
                        @endif
                    </td>
                    <td style="font-size:.78rem; color:var(--color-text-muted);">
                        {{ $doc->file_size ? number_format($doc->file_size / 1024, 0) . ' KB' : '—' }}
                    </td>
                    <td style="font-size:.78rem; color:var(--color-text-muted);">{{ $doc->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <div style="display:flex; gap:.375rem;">
                            <a href="{{ route('admin.documents.show', $doc) }}" class="btn btn-sm btn-primary">Voir</a>

                            <form method="POST" action="{{ route('admin.documents.verify', $doc) }}">
                                @csrf
                                <input type="hidden" name="status" value="verified">
                                <button type="submit" class="btn btn-sm btn-success"
                                    onclick="return confirm('Approuver ce document ?')">✓</button>
                            </form>

                            <button type="button" class="btn btn-sm btn-danger"
                                onclick="openRejectModal({{ $doc->id }})">✕</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding:3rem; color:var(--color-text-muted);">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="40" height="40" style="margin:0 auto .75rem; display:block; opacity:.3;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p style="font-weight:600; margin-bottom:.25rem;">Aucun document en attente</p>
                        <p style="font-size:.8rem;">Tous les documents ont été traités.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:1rem;">{{ $documents->links() }}</div>

    {{-- Modals de rejet individuels --}}
    @foreach($documents as $doc)
    <div class="modal-backdrop" id="modal-reject-{{ $doc->id }}">
        <div class="modal-box">
            <div class="modal-title">Rejeter le document</div>
            <p style="font-size:.85rem; color:var(--color-text-muted); margin-bottom:1rem;">
                Document : <strong>{{ $doc->typeDoc->name ?? 'Document' }}</strong><br>
                Utilisateur : <strong>{{ $doc->user->full_name ?? '—' }}</strong>
            </p>
            <form method="POST" action="{{ route('admin.documents.verify', $doc) }}">
                @csrf
                <input type="hidden" name="status" value="rejected">
                <div style="margin-bottom:.875rem;">
                    <label class="modal-label">Motif du rejet <span style="color:#ef4444;">*</span></label>
                    <textarea name="rejection_reason" class="modal-textarea" rows="3" required
                        placeholder="Expliquez pourquoi ce document est rejeté…"></textarea>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn btn-danger" style="flex:1; justify-content:center;">Confirmer le rejet</button>
                    <button type="button" class="btn btn-secondary" onclick="closeRejectModal({{ $doc->id }})">Annuler</button>
                </div>
            </form>
        </div>
    </div>
    @endforeach

    {{-- Modal rejet en masse --}}
    <div class="modal-backdrop" id="modal-bulk-reject">
        <div class="modal-box">
            <div class="modal-title">Rejeter la sélection</div>
            <form method="POST" action="{{ route('admin.documents.bulk') }}" id="bulkRejectForm">
                @csrf
                <input type="hidden" name="status" value="rejected">
                <div id="bulkRejectIds"></div>
                <div style="margin-bottom:.875rem;">
                    <label class="modal-label">Motif du rejet <span style="color:#ef4444;">*</span></label>
                    <textarea name="rejection_reason" class="modal-textarea" rows="3" required
                        placeholder="Ce motif sera appliqué à tous les documents sélectionnés…"></textarea>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn btn-danger" style="flex:1; justify-content:center;">Rejeter tous</button>
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('modal-bulk-reject').classList.remove('open')">Annuler</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    // ── Sélection ──────────────────────────────
    function getChecked() {
        return [...document.querySelectorAll('.doc-check-row:checked')].map(cb => cb.value);
    }

    function onRowCheck() {
        const ids = getChecked();
        const bar = document.getElementById('bulkBar');
        document.getElementById('bulkCount').textContent = ids.length + ' sélectionné(s)';
        bar.classList.toggle('visible', ids.length > 0);
        document.getElementById('selectAll').indeterminate =
            ids.length > 0 && ids.length < document.querySelectorAll('.doc-check-row').length;
        document.getElementById('selectAll').checked =
            ids.length === document.querySelectorAll('.doc-check-row').length;
    }

    function toggleSelectAll(master) {
        document.querySelectorAll('.doc-check-row').forEach(cb => cb.checked = master.checked);
        onRowCheck();
    }

    function deselectAll() {
        document.querySelectorAll('.doc-check-row').forEach(cb => cb.checked = false);
        document.getElementById('selectAll').checked = false;
        onRowCheck();
    }

    function buildHiddenIds(containerId) {
        const ids  = getChecked();
        const cont = document.getElementById(containerId);
        cont.innerHTML = ids.map(id => `<input type="hidden" name="document_ids[]" value="${id}">`).join('');
    }

    // ── Bulk approve ──────────────────────────
    document.getElementById('bulkApproveForm').addEventListener('submit', function() {
        buildHiddenIds('bulkApproveIds');
    });

    // ── Modals rejet ──────────────────────────
    function openRejectModal(id) { document.getElementById('modal-reject-' + id).classList.add('open'); }
    function closeRejectModal(id){ document.getElementById('modal-reject-' + id).classList.remove('open'); }

    function openBulkRejectModal() {
        buildHiddenIds('bulkRejectIds');
        document.getElementById('modal-bulk-reject').classList.add('open');
    }

    document.querySelectorAll('.modal-backdrop').forEach(b => {
        b.addEventListener('click', e => { if (e.target === b) b.classList.remove('open'); });
    });
</script>
@endsection
