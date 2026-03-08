@extends('layouts.app')

@section('title', 'Documents requis - ' . $fundingRequest->request_number)
@section('header-title', 'Documents')

@section('header-action')
<a href="{{ route('client.requests.show', $fundingRequest) }}" class="btn-back" data-transition="slide-right">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
    </svg>
</a>
@endsection

@section('content')

<div class="documents-page">

    {{-- Header --}}
    <div class="success-header">
        <div class="sh-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="32" height="32">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="sh-content">
            <h2>Paiement confirmé !</h2>
            <p>Votre demande <span class="request-number">{{ $fundingRequest->request_number }}</span> est enregistrée.</p>
            <p class="sh-sub">Finalisez votre dossier en téléchargeant les documents requis.</p>
        </div>
    </div>

    {{-- Carte récapitulatif --}}
    <div class="summary-card">
        <div class="sc-header">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <span>Récapitulatif</span>
        </div>
        <div class="sc-body">
            <div class="sc-row">
                <div class="sc-item">
                    <span class="sc-label">Financement</span>
                    <span class="sc-value">{{ $fundingRequest->typeFinancement->name }}</span>
                </div>
                <div class="sc-item">
                    <span class="sc-label">Montant</span>
                    <span class="sc-value highlight">{{ number_format($fundingRequest->amount_requested, 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="sc-item">
                    <span class="sc-label">Durée</span>
                    <span class="sc-value">{{ $fundingRequest->duration }} mois</span>
                </div>
                <div class="sc-item">
                    <span class="sc-label">Frais payés</span>
                    <span class="sc-value success">{{ number_format($fundingRequest->registration_fee_paid, 0, ',', ' ') }} FCFA</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Section documents --}}
    <div class="documents-section">
        <div class="ds-header">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="22" height="22">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3>Documents requis</h3>
        </div>

        @php
            // 🔥 CORRIGÉ : Séparer par présence de fichier, pas par statut
            $emptyDocs = $documents->whereNull('file_path');
            $filledDocs = $documents->whereNotNull('file_path');
            $progressPercent = $documents->count() > 0
                ? round(($filledDocs->count() / $documents->count()) * 100)
                : 0;
        @endphp

        {{-- Barre de progression --}}
        <div class="progress-bar">
            <div class="progress-track">
                <div class="progress-fill" style="width: {{ $progressPercent }}%"></div>
            </div>
            <div class="progress-text">
                <span>{{ $filledDocs->count() }}/{{ $documents->count() }} documents</span>
                <span>{{ $progressPercent }}% complété</span>
            </div>
        </div>

        {{-- Documents à compléter (vides) --}}
        @if($emptyDocs->count() > 0)
        <div class="docs-group">
            <div class="dg-header">
                <span class="dg-badge pending">{{ $emptyDocs->count() }}</span>
                <span class="dg-title">À compléter</span>
            </div>

            <div class="docs-list">
                @foreach($emptyDocs as $doc)
                <div class="doc-card pending" id="doc-{{ $doc->id }}">
                    <div class="doc-icon pending">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                    </div>

                    <div class="doc-info">
                        <div class="doc-title-row">
                            <h4>{{ $doc->typeDoc->name }}</h4>
                            @if($doc->typeDoc->is_required ?? true)
                                <span class="doc-badge required">Obligatoire</span>
                            @else
                                <span class="doc-badge optional">Optionnel</span>
                            @endif
                        </div>
                        <p class="doc-desc">{{ $doc->typeDoc->description ?? 'PDF, JPG, PNG (max 10MB)' }}</p>
                    </div>

                    <div class="doc-action">
                        {{--  FORMULAIRE CORRIGÉ : document_user_id + funding_request_id --}}
                        <form action="{{ route('client.documents.store') }}" method="POST"
                              enctype="multipart/form-data" class="upload-form" data-doc-id="{{ $doc->id }}">
                            @csrf
                            <input type="hidden" name="document_user_id" value="{{ $doc->id }}">
                            <input type="hidden" name="funding_request_id" value="{{ $fundingRequest->id }}">

                            <label class="btn-upload">
                                <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                       onchange="handleFileSelect(this, {{ $doc->id }})">
                                <span class="bu-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                    </svg>
                                </span>
                                <span class="bu-text" id="bu-text-{{ $doc->id }}">Choisir</span>
                            </label>

                            <div class="file-preview" id="preview-{{ $doc->id }}" style="display: none;">
                                <span class="fp-name" id="fp-name-{{ $doc->id }}"></span>
                                <button type="button" class="fp-remove" onclick="clearFile({{ $doc->id }})">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <button type="submit" class="btn-confirm" id="submit-{{ $doc->id }}" style="display: none;">
                                <span class="bc-text">Confirmer</span>
                                <span class="bc-spinner">
                                    <div class="spinner-dual small"></div>
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Documents complétés --}}
        @if($filledDocs->count() > 0)
        <div class="docs-group">
            <div class="dg-header">
                <span class="dg-badge verified">{{ $filledDocs->count() }}</span>
                <span class="dg-title">Téléchargés</span>
            </div>

            <div class="docs-list">
                @foreach($filledDocs as $doc)
                <div class="doc-card verified" id="doc-{{ $doc->id }}">
                    <div class="doc-icon verified">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>

                    <div class="doc-info">
                        <div class="doc-title-row">
                            <h4>{{ $doc->typeDoc->name }}</h4>
                            <span class="doc-badge verified">Téléchargé</span>
                        </div>
                        <p class="doc-desc verified">
                            {{ $doc->file_name }} • {{ round($doc->file_size / 1024, 1) }} Ko
                            @if($doc->status === 'verified')
                                <span style="color: #16a34a; margin-left: 0.5rem;">✓ Vérifié</span>
                            @elseif($doc->status === 'pending')
                                <span style="color: #f59e0b; margin-left: 0.5rem;">⏳ En attente</span>
                            @endif
                        </p>
                    </div>

                    <div class="doc-action">
                        <div style="display: flex; gap: 0.5rem;">
                            {{-- Voir --}}
                            <a href="{{ route('client.documents.show', $doc) }}" class="btn-view" target="_blank">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Voir
                            </a>

                            {{-- 🔥 REMPLACER : Formulaire direct avec changement auto --}}
                            <form action="{{ route('client.documents.store') }}" method="POST"
                                  enctype="multipart/form-data" style="display: inline;">
                                @csrf
                                <input type="hidden" name="document_user_id" value="{{ $doc->id }}">
                                <input type="hidden" name="funding_request_id" value="{{ $fundingRequest->id }}">

                                <label class="btn-upload" style="padding: 0.5rem;" title="Remplacer le fichier">
                                    <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                           onchange="this.form.submit()" style="display: none;">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                    </svg>
                                </label>
                            </form>

                            {{-- Supprimer --}}
                            <form action="{{ route('client.documents.destroy', $doc) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-view"
                                        style="color: #dc2626; border-color: #fecaca; background: #fee2e2;"
                                        onclick="return confirm('Supprimer ce document ?')">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Footer --}}
        <div class="ds-footer">
            @if($emptyDocs->count() === 0)
                <div class="completion-message">
                    <div class="cm-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="48" height="48">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h4>Dossier complet !</h4>
                    <p>Tous les documents ont été fournis.</p>
                    <a href="{{ route('client.requests.show', $fundingRequest) }}" class="btn-primary">
                        Voir ma demande
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            @else
                <div class="info-box">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p>Votre demande ne sera traitée qu'après réception de tous les documents obligatoires.</p>
                </div>
            @endif
        </div>
    </div>

</div>

@endsection

@section('scripts')
<script>
// Gestion sélection fichier
function handleFileSelect(input, docId) {
    const file = input.files[0];
    if (!file) return;

    // Vérifications
    const maxSize = 10 * 1024 * 1024; // 10MB
    const allowedTypes = [
        'application/pdf',
        'image/jpeg', 'image/jpg', 'image/png',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];

    if (file.size > maxSize) {
        alert('Le fichier est trop volumineux. Maximum 10MB.');
        input.value = '';
        return;
    }

    if (!allowedTypes.includes(file.type)) {
        alert('Format non supporté. Utilisez PDF, JPG, PNG, DOC ou DOCX.');
        input.value = '';
        return;
    }

    // Afficher preview
    document.getElementById('fp-name-' + docId).textContent = file.name;
    document.getElementById('preview-' + docId).style.display = 'flex';
    document.getElementById('bu-text-' + docId).textContent = 'Changer';
    document.getElementById('submit-' + docId).style.display = 'inline-flex';
}

// Effacer sélection
function clearFile(docId) {
    const form = document.querySelector('#doc-' + docId + ' .upload-form');
    const fileInput = form.querySelector('input[type="file"]');
    fileInput.value = '';

    document.getElementById('preview-' + docId).style.display = 'none';
    document.getElementById('bu-text-' + docId).textContent = 'Choisir';
    document.getElementById('submit-' + docId).style.display = 'none';
}

// Soumission AJAX
document.querySelectorAll('.upload-form').forEach(form => {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const docId = this.dataset.docId;
        const submitBtn = document.getElementById('submit-' + docId);

        submitBtn.classList.add('loading');
        submitBtn.disabled = true;

        try {
            const formData = new FormData(this);

            const response = await fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                // Animation succès
                const card = document.getElementById('doc-' + docId);
                card.classList.add('upload-success');

                setTimeout(() => window.location.reload(), 600);
            } else {
                throw new Error(data.message || 'Erreur upload');
            }

        } catch (error) {
            alert(error.message || 'Erreur lors de l\'upload');
            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
        }
    });
});
</script>
@endsection
