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

    {{-- Header de succès animé --}}
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
            $pendingDocs = $documents->where('status', 'pending');
            $verifiedDocs = $documents->where('status', 'verified');
            $progressPercent = $documents->count() > 0
                ? round(($verifiedDocs->count() / $documents->count()) * 100)
                : 0;
        @endphp

        {{-- Barre de progression --}}
        <div class="progress-bar">
            <div class="progress-track">
                <div class="progress-fill" style="width: {{ $progressPercent }}%"></div>
            </div>
            <div class="progress-text">
                <span>{{ $verifiedDocs->count() }}/{{ $documents->count() }} documents</span>
                <span>{{ $progressPercent }}% complété</span>
            </div>
        </div>

        {{-- Documents en attente --}}
        @if($pendingDocs->count() > 0)
        <div class="docs-group">
            <div class="dg-header">
                <span class="dg-badge pending">{{ $pendingDocs->count() }}</span>
                <span class="dg-title">En attente</span>
            </div>

            <div class="docs-list">
                @foreach($pendingDocs as $doc)
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
                            @if($doc->typeDoc->is_required)
                                <span class="doc-badge required">Obligatoire</span>
                            @else
                                <span class="doc-badge optional">Optionnel</span>
                            @endif
                        </div>
                        <p class="doc-desc">{{ $doc->typeDoc->description ?? 'PDF, JPG ou PNG (max 5MB)' }}</p>
                    </div>

                    <div class="doc-action">
                        <form action="{{ route('client.documents.store') }}" method="POST"
                              enctype="multipart/form-data" class="upload-form" data-doc-id="{{ $doc->id }}">
                            @csrf
                            <input type="hidden" name="document_user_id" value="{{ $doc->id }}">
                            <input type="hidden" name="funding_request_id" value="{{ $fundingRequest->id }}">

                            <label class="btn-upload">
                                <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png"
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

        {{-- Documents vérifiés --}}
        @if($verifiedDocs->count() > 0)
        <div class="docs-group">
            <div class="dg-header">
                <span class="dg-badge verified">{{ $verifiedDocs->count() }}</span>
                <span class="dg-title">Vérifiés</span>
            </div>

            <div class="docs-list">
                @foreach($verifiedDocs as $doc)
                <div class="doc-card verified">
                    <div class="doc-icon verified">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>

                    <div class="doc-info">
                        <div class="doc-title-row">
                            <h4>{{ $doc->typeDoc->name }}</h4>
                            <span class="doc-badge verified">Vérifié</span>
                        </div>
                        <p class="doc-desc verified">Document approuvé le {{ $doc->updated_at->format('d/m/Y') }}</p>
                    </div>

                    <div class="doc-action">
                        <a href="{{ route('client.documents.show', $doc) }}" class="btn-view">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Voir
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Footer action --}}
        <div class="ds-footer">
            @if($pendingDocs->count() === 0)
                <div class="completion-message">
                    <div class="cm-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="48" height="48">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h4>Dossier complet !</h4>
                    <p>Tous les documents ont été fournis. Votre demande est en cours d'étude.</p>
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
    const maxSize = 5 * 1024 * 1024; // 5MB
    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];

    if (file.size > maxSize) {
        alert('Le fichier est trop volumineux. Maximum 5MB.');
        input.value = '';
        return;
    }

    if (!allowedTypes.includes(file.type)) {
        alert('Format non supporté. Utilisez PDF, JPG ou PNG.');
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

// Soumission formulaires
document.querySelectorAll('.upload-form').forEach(form => {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const docId = this.dataset.docId;
        const submitBtn = document.getElementById('submit-' + docId);
        const fileInput = this.querySelector('input[type="file"]');

        if (!fileInput.files[0]) {
            alert('Veuillez sélectionner un fichier');
            return;
        }

        // État chargement
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;

        try {
            const formData = new FormData(this);

            const response = await fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                // Animation succès puis reload
                const card = document.getElementById('doc-' + docId);
                card.classList.add('upload-success');

                setTimeout(() => {
                    window.location.reload();
                }, 800);
            } else {
                throw new Error(data.message || 'Erreur lors de l\'upload');
            }

        } catch (error) {
            console.error('Upload error:', error);
            alert(error.message || 'Erreur lors de l\'upload. Veuillez réessayer.');

            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
        }
    });
});
</script>
@endsection

@section('styles')
<style>
/* ============================================
   PAGE DOCUMENTS - STYLE COHÉRENT APP
   ============================================ */
.documents-page {
    max-width: 800px;
    margin: 0 auto;
    padding: 0 1rem;
    animation: fadeInUp 0.5s ease;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Header succès */
.success-header {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
    border: 1px solid #86efac;
    border-radius: 16px;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
    animation: slideInLeft 0.6s ease;
}

@keyframes slideInLeft {
    from { opacity: 0; transform: translateX(-20px); }
    to { opacity: 1; transform: translateX(0); }
}

.sh-icon {
    width: 48px;
    height: 48px;
    background: white;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #16a34a;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.15);
}

.sh-content h2 {
    font-size: 1.125rem;
    font-weight: 700;
    color: #166534;
    margin: 0 0 0.25rem;
}

.sh-content p {
    margin: 0;
    color: #166534;
    font-size: 0.9375rem;
}

.request-number {
    font-family: monospace;
    background: rgba(255,255,255,0.6);
    padding: 0.125rem 0.5rem;
    border-radius: 4px;
    font-weight: 600;
}

.sh-sub {
    font-size: 0.875rem !important;
    opacity: 0.8;
    margin-top: 0.25rem !important;
}

/* Carte récapitulatif */
.summary-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
}

.sc-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 1.25rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    font-size: 0.875rem;
    font-weight: 600;
    color: #475569;
}

.sc-header svg {
    color: #64748b;
}

.sc-body {
    padding: 1.25rem;
}

.sc-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

@media (min-width: 640px) {
    .sc-row {
        grid-template-columns: repeat(4, 1fr);
    }
}

.sc-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.sc-label {
    font-size: 0.75rem;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.sc-value {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #0f172a;
}

.sc-value.highlight {
    color: #2563eb;
}

.sc-value.success {
    color: #16a34a;
}

/* Section documents */
.documents-section {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
}

.ds-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1.25rem;
    border-bottom: 1px solid #e2e8f0;
}

.ds-header svg {
    color: #2563eb;
}

.ds-header h3 {
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}

/* Barre progression */
.progress-bar {
    padding: 1rem 1.25rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.progress-track {
    height: 6px;
    background: #e2e8f0;
    border-radius: 3px;
    overflow: hidden;
    margin-bottom: 0.5rem;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #2563eb, #3b82f6);
    border-radius: 3px;
    transition: width 0.6s ease;
}

.progress-text {
    display: flex;
    justify-content: space-between;
    font-size: 0.75rem;
    color: #64748b;
}

/* Groupes documents */
.docs-group {
    padding: 1.25rem;
    border-bottom: 1px solid #e2e8f0;
}

.docs-group:last-of-type {
    border-bottom: none;
}

.dg-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.dg-badge {
    min-width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 700;
    color: white;
}

.dg-badge.pending {
    background: #f59e0b;
}

.dg-badge.verified {
    background: #10b981;
}

.dg-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
}

/* Liste documents */
.docs-list {
    display: flex;
    flex-direction: column;
    gap: 0.875rem;
}

.doc-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.doc-card:hover {
    border-color: #cbd5e1;
    transform: translateY(-1px);
}

.doc-card.pending {
    background: white;
    border-color: #e2e8f0;
}

.doc-card.verified {
    background: #f0fdf4;
    border-color: #bbf7d0;
}

.doc-card.upload-success {
    animation: successPulse 0.6s ease;
}

@keyframes successPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.02); background: #dcfce7; }
    100% { transform: scale(1); }
}

.doc-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.doc-icon.pending {
    background: #dbeafe;
    color: #2563eb;
}

.doc-icon.verified {
    background: #dcfce7;
    color: #16a34a;
}

.doc-info {
    flex: 1;
    min-width: 0;
}

.doc-title-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 0.25rem;
}

.doc-info h4 {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #0f172a;
    margin: 0;
}

.doc-badge {
    font-size: 0.625rem;
    font-weight: 700;
    padding: 0.125rem 0.5rem;
    border-radius: 9999px;
    text-transform: uppercase;
}

.doc-badge.required {
    background: #fee2e2;
    color: #991b1b;
}

.doc-badge.optional {
    background: #e2e8f0;
    color: #475569;
}

.doc-badge.verified {
    background: #dcfce7;
    color: #166534;
}

.doc-desc {
    font-size: 0.8125rem;
    color: #64748b;
    margin: 0;
}

.doc-desc.verified {
    color: #16a34a;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

/* Actions */
.doc-action {
    flex-shrink: 0;
}

/* Upload */
.btn-upload {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1rem;
    background: #eff6ff;
    border: 1.5px solid #bfdbfe;
    border-radius: 8px;
    color: #2563eb;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-upload:hover {
    background: #dbeafe;
    border-color: #3b82f6;
}

.btn-upload input {
    display: none;
}

.file-preview {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.5rem;
    padding: 0.5rem;
    background: #f0fdf4;
    border-radius: 6px;
}

.fp-name {
    font-size: 0.75rem;
    color: #166534;
    max-width: 120px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.fp-remove {
    width: 20px;
    height: 20px;
    border: none;
    background: #fee2e2;
    color: #dc2626;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    padding: 0;
}

.btn-confirm {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 0.5rem;
    padding: 0.625rem 1.25rem;
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    position: relative;
    overflow: hidden;
}

.btn-confirm:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.btn-confirm.loading .bc-text {
    opacity: 0;
}

.btn-confirm.loading .bc-spinner {
    opacity: 1;
}

.bc-spinner {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s;
}

/* Bouton voir */
.btn-view {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1rem;
    background: white;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    color: #475569;
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-view:hover {
    border-color: #2563eb;
    color: #2563eb;
}

/* Footer */
.ds-footer {
    padding: 1.25rem;
    background: #f8fafc;
}

.completion-message {
    text-align: center;
    padding: 1.5rem;
}

.cm-icon {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, #dcfce7, #bbf7d0);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #16a34a;
    margin: 0 auto 1rem;
    animation: bounce 1s ease infinite;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}

.completion-message h4 {
    font-size: 1.125rem;
    font-weight: 700;
    color: #166534;
    margin: 0 0 0.5rem;
}

.completion-message p {
    color: #64748b;
    margin: 0 0 1.5rem;
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 1.5rem;
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    color: white;
    border-radius: 10px;
    font-size: 0.9375rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(37, 99, 235, 0.45);
}

.info-box {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 1rem;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 10px;
    color: #1e40af;
    font-size: 0.875rem;
}

.info-box svg {
    flex-shrink: 0;
    margin-top: 0.125rem;
}

.info-box p {
    margin: 0;
}

/* Spinner */
.spinner-dual {
    width: 20px;
    height: 20px;
    position: relative;
}

.spinner-dual.small {
    width: 16px;
    height: 16px;
}

.spinner-dual::before,
.spinner-dual::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: 2px solid transparent;
}

.spinner-dual::before {
    border-top-color: currentColor;
    border-right-color: currentColor;
    animation: spin 1s linear infinite;
}

.spinner-dual::after {
    border-bottom-color: currentColor;
    border-left-color: currentColor;
    animation: spin 1.5s linear infinite reverse;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Dark mode */
@media (prefers-color-scheme: dark) {
    .success-header {
        background: linear-gradient(135deg, #166534 0%, #15803d 100%);
        border-color: #22c55e;
    }

    .sh-content h2,
    .sh-content p {
        color: white;
    }

    .request-number {
        background: rgba(0,0,0,0.2);
        color: white;
    }

    .summary-card,
    .documents-section {
        background: #1e293b;
        border-color: #334155;
    }

    .sc-header,
    .progress-bar,
    .ds-footer {
        background: #0f172a;
        border-color: #334155;
    }

    .doc-card {
        background: #0f172a;
        border-color: #334155;
    }

    .doc-card.verified {
        background: #064e3b;
        border-color: #059669;
    }

    .doc-info h4 {
        color: #f8fafc;
    }

    .doc-desc {
        color: #94a3b8;
    }
}
</style>
@endsection
