@extends('layouts.admin')

@section('title', 'Vérifier — ' . ($document->typeDoc->name ?? 'Document'))
@section('header-title', 'Vérification du Document')

@section('styles')
<style>
    /* ── Layout 2 cols ──────────────────── */
    .verify-grid { display: grid; grid-template-columns: 1fr 360px; gap: 1.5rem; align-items: start; }

    /* ── Card ───────────────────────────── */
    .card { background: var(--color-white); border: 1px solid var(--color-border); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 1.25rem; }
    .card-header { padding: .875rem 1.25rem; border-bottom: 1px solid var(--color-border); font-size: .9rem; font-weight: 700; color: var(--color-text); letter-spacing: -.02em; display: flex; align-items: center; justify-content: space-between; }
    .card-body { padding: 1.25rem; }

    /* ── Viewer ─────────────────────────── */
    .doc-viewer {
        width: 100%; min-height: 520px; border-radius: 10px;
        border: 1px solid var(--color-border); overflow: hidden;
        background: #f8fafc; display: flex; align-items: center; justify-content: center;
    }
    .doc-viewer iframe { width: 100%; height: 580px; border: none; }
    .doc-viewer img    { max-width: 100%; max-height: 580px; object-fit: contain; }
    .doc-viewer-placeholder { text-align: center; padding: 3rem; }
    .doc-viewer-placeholder svg { width: 48px; height: 48px; margin: 0 auto .75rem; display: block; opacity: .3; }

    /* ── Info rows ──────────────────────── */
    .info-row { display: flex; justify-content: space-between; align-items: flex-start; padding: .5rem 0; border-bottom: 1px solid #f8fafc; font-size: .84rem; gap: .75rem; }
    .info-row:last-child { border: none; }
    .info-label { color: var(--color-text-muted); font-weight: 500; flex-shrink: 0; }
    .info-value { font-weight: 600; color: var(--color-text); text-align: right; }

    /* ── Badges ─────────────────────────── */
    .badge { display: inline-flex; align-items: center; padding: .2rem .65rem; border-radius: 9999px; font-size: .7rem; font-weight: 600; white-space: nowrap; }
    .badge-yellow { background: #fef9c3; color: #854d0e; }
    .badge-green  { background: #dcfce7; color: #166534; }
    .badge-red    { background: #fee2e2; color: #991b1b; }
    .badge-blue   { background: #dbeafe; color: #1e40af; }
    .badge-gray   { background: #f1f5f9; color: #475569; }

    /* ── Boutons ────────────────────────── */
    .btn { display: inline-flex; align-items: center; gap: .375rem; padding: .5rem 1rem; border-radius: 8px; font-size: .8rem; font-weight: 600; text-decoration: none; border: none; cursor: pointer; transition: all .15s; font-family: inherit; }
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
    .btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid var(--color-border); }
    .btn-secondary:hover { background: #e2e8f0; }

    /* ── Form ───────────────────────────── */
    .form-group { margin-bottom: .875rem; }
    .form-label { display: block; font-size: .8rem; font-weight: 600; color: var(--color-text); margin-bottom: .35rem; }
    .form-control { width: 100%; padding: .5rem .75rem; border: 1px solid var(--color-border); border-radius: 8px; font-size: .875rem; font-family: inherit; outline: none; transition: border-color .15s; }
    .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.1); }

    /* ── Docs liés ──────────────────────── */
    .related-doc { display: flex; align-items: center; justify-content: space-between; padding: .625rem .875rem; border-radius: 8px; border: 1px solid var(--color-border); background: #f8fafc; margin-bottom: .5rem; }
    .related-doc-name { font-size: .82rem; font-weight: 600; }

    /* ── Déjà traité ────────────────────── */
    .already-treated { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 1rem; }
    .already-treated.rejected { background: #fef2f2; border-color: #fecaca; }

    /* ── Toolbar viewer ─────────────────── */
    .viewer-toolbar { display: flex; align-items: center; justify-content: space-between; padding: .625rem .875rem; background: #1e293b; border-radius: 10px 10px 0 0; }
    .viewer-toolbar-left { font-size: .78rem; color: #94a3b8; }
    .viewer-toolbar-right { display: flex; gap: .5rem; }
    .viewer-btn { display: inline-flex; align-items: center; gap: .25rem; padding: .25rem .65rem; background: rgba(255,255,255,.1); border: none; border-radius: 6px; color: #e2e8f0; font-size: .73rem; font-weight: 600; cursor: pointer; text-decoration: none; transition: background .15s; }
    .viewer-btn:hover { background: rgba(255,255,255,.2); color: #fff; }

    @media (max-width: 1024px) { .verify-grid { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')

<div style="margin-bottom:1rem; display:flex; align-items:center; gap:.75rem; flex-wrap:wrap;">
    <a href="{{ route('admin.documents.pending') }}" class="btn btn-secondary btn-sm">← Retour</a>
    @php
        $next = \App\Models\DocumentUser::where('status','pending')->where('id','!=',$document->id)->first();
    @endphp
    @if($next)
        <a href="{{ route('admin.documents.show', $next) }}" class="btn btn-sm btn-primary">
            Suivant →
        </a>
    @endif
    <span style="font-size:.8rem; color:var(--color-text-muted); margin-left:auto;">
        Document #{{ $document->id }}
    </span>
</div>

<div class="verify-grid">

    {{-- ── Colonne gauche : viewer ── --}}
    @php
        /*
         * CORRECTION CORS / localhost :
         * On utilise TOUJOURS la route Laravel (admin.documents.show) comme URL
         * d'affichage et admin.documents.download pour le téléchargement.
         * Cela évite que Storage::url() génère une URL avec APP_URL=localhost
         * qui serait bloquée par la politique CORS du navigateur en production.
         *
         * Si le controller passait déjà $viewUrl, on l'ignore et on recalcule.
         */
        $viewUrl     = route('admin.documents.show', $document);
        $downloadUrl = route('admin.documents.download', $document);
        $ext         = strtolower(pathinfo($document->file_name ?? '', PATHINFO_EXTENSION));
        $isImage     = in_array($ext, ['jpg','jpeg','png','gif','webp']);
        $isPdf       = $ext === 'pdf';
    @endphp
    <div>
        <div class="card">
            <div class="viewer-toolbar">
                <span class="viewer-toolbar-left">
                    {{ $document->file_name ?? 'document' }}
                </span>
                <div class="viewer-toolbar-right">
                    <a href="{{ $viewUrl }}" target="_blank" class="viewer-btn">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="12" height="12"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Ouvrir
                    </a>
                    <a href="{{ $downloadUrl }}" download class="viewer-btn">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="12" height="12"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Télécharger
                    </a>
                </div>
            </div>

            <div class="doc-viewer" style="border-radius:0 0 10px 10px; border-top:none;">
                @if($isImage)
                    <img src="{{ $viewUrl }}" alt="Document">
                @elseif($isPdf)
                    <iframe src="{{ $viewUrl }}#toolbar=1" title="Document PDF"></iframe>
                @else
                    <div class="doc-viewer-placeholder">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <p style="font-size:.875rem; color:var(--color-text-muted); margin-bottom:1rem;">Aperçu non disponible pour ce type de fichier.</p>
                        <a href="{{ $viewUrl }}" target="_blank" class="btn btn-primary btn-sm">Ouvrir le fichier</a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Documents liés à la même demande --}}
        @if($relatedDocuments->isNotEmpty())
        <div class="card">
            <div class="card-header">Autres documents de la même demande ({{ $relatedDocuments->count() }})</div>
            <div class="card-body">
                @foreach($relatedDocuments as $rel)
                <div class="related-doc">
                    <div>
                        <div class="related-doc-name">{{ $rel->typeDoc->name ?? 'Document' }}</div>
                        <div style="font-size:.72rem; color:var(--color-text-muted);">{{ $rel->file_name }}</div>
                    </div>
                    <div style="display:flex; align-items:center; gap:.5rem;">
                        <span class="badge {{ $rel->status === 'verified' ? 'badge-green' : ($rel->status === 'rejected' ? 'badge-red' : 'badge-yellow') }}">
                            {{ $rel->getStatusLabel() }}
                        </span>
                        <a href="{{ route('admin.documents.show', $rel) }}" class="btn btn-sm btn-secondary">Voir</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- ── Colonne droite : infos + actions ── --}}
    <div>

        {{-- Infos document --}}
        <div class="card">
            <div class="card-header">
                Informations
                <span class="badge {{ $document->status === 'pending' ? 'badge-yellow' : ($document->status === 'verified' ? 'badge-green' : 'badge-red') }}">
                    {{ $document->getStatusLabel() }}
                </span>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <span class="info-label">Type</span>
                    <span class="info-value">{{ $document->typeDoc->name ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Utilisateur</span>
                    <span class="info-value">
                        <a href="{{ route('admin.users.show', $document->user) }}" style="color:#3b82f6; text-decoration:none;">
                            {{ $document->user->full_name ?? '—' }}
                        </a>
                    </span>
                </div>
                @if($document->fundingRequest)
                <div class="info-row">
                    <span class="info-label">Demande</span>
                    <span class="info-value">
                        <a href="{{ route('admin.requests.show', $document->fundingRequest) }}" style="color:#3b82f6; text-decoration:none;">
                            {{ $document->fundingRequest->request_number ?? '#'.$document->fundingRequest->id }}
                        </a>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Financement</span>
                    <span class="info-value" style="font-size:.8rem;">{{ $document->fundingRequest->typeFinancement->name ?? '—' }}</span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Fichier</span>
                    <span class="info-value" style="font-size:.75rem; font-family:monospace;">{{ $document->file_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Taille</span>
                    <span class="info-value">{{ $document->file_size ? number_format($document->file_size/1024, 0).' KB' : '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Soumis le</span>
                    <span class="info-value">{{ $document->created_at->format('d/m/Y à H:i') }}</span>
                </div>
                @if($document->verified_at)
                <div class="info-row">
                    <span class="info-label">Vérifié le</span>
                    <span class="info-value">{{ $document->verified_at->format('d/m/Y à H:i') }}</span>
                </div>
                @endif
                @if($document->rejection_reason)
                <div class="info-row" style="flex-direction:column; align-items:flex-start; gap:.25rem;">
                    <span class="info-label">Motif rejet</span>
                    <span style="font-size:.82rem; color:#991b1b; background:#fef2f2; padding:.4rem .65rem; border-radius:6px; width:100%;">{{ $document->rejection_reason }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Actions --}}
        @if($document->status === 'pending')
        <div class="card">
            <div class="card-header">Décision</div>
            <div class="card-body">

                {{-- Approuver --}}
                <form method="POST" action="{{ route('admin.documents.verify', $document) }}" style="margin-bottom:.875rem;">
                    @csrf
                    <input type="hidden" name="status" value="verified">
                    <div class="form-group">
                        <label class="form-label">Note interne (optionnel)</label>
                        <textarea name="notes" class="form-control" rows="2"
                            placeholder="Commentaire visible uniquement par les admins…"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success btn-full"
                        onclick="return confirm('Approuver ce document ?')">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="15" height="15"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Approuver le document
                    </button>
                </form>

                <div style="text-align:center; font-size:.75rem; color:var(--color-text-muted); margin:.5rem 0;">— ou —</div>

                {{-- Rejeter --}}
                <form method="POST" action="{{ route('admin.documents.verify', $document) }}">
                    @csrf
                    <input type="hidden" name="status" value="rejected">
                    <div class="form-group">
                        <label class="form-label">Motif du rejet <span style="color:#ef4444;">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required
                            placeholder="Expliquez la raison du rejet au client…"
                            style="border-color:#fca5a5;"></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger btn-full">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="15" height="15"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Rejeter le document
                    </button>
                </form>

            </div>
        </div>

        {{-- Passer au suivant --}}
        @if($next)
        <form method="POST" action="{{ route('admin.documents.verify', $document) }}">
            @csrf
            <input type="hidden" name="status" value="verified">
            <input type="hidden" name="next" value="1">
            <button type="submit" class="btn btn-warning btn-full"
                onclick="return confirm('Approuver et passer au suivant ?')">
                Approuver & suivant →
            </button>
        </form>
        @endif

        @else
        {{-- Déjà traité --}}
        <div class="already-treated {{ $document->status === 'rejected' ? 'rejected' : '' }}">
            <div style="font-weight:700; font-size:.875rem; margin-bottom:.25rem;">
                {{ $document->status === 'verified' ? '✓ Document approuvé' : '✕ Document rejeté' }}
            </div>
            <div style="font-size:.8rem; color:var(--color-text-muted);">
                Traité le {{ $document->verified_at?->format('d/m/Y à H:i') ?? $document->updated_at->format('d/m/Y') }}
                @if($document->verifiedBy) par {{ $document->verifiedBy->full_name }} @endif
            </div>
        </div>
        @endif

    </div>

</div>

@endsection
