@extends('layouts.app')

@section('title', 'Documents - ' . $fundingRequest->request_number)
@section('header-title', 'Documents requis')

@section('content')

@php
    $canUpload = in_array($fundingRequest->status, ['draft', 'submitted']);

    $emptyDocs = $documents->whereNull('file_path');
    $filledDocs = $documents->whereNotNull('file_path');
    $progressPercent = $documents->count() > 0
        ? round(($filledDocs->count() / $documents->count()) * 100)
        : 0;

    $allCompleted = $emptyDocs->count() === 0;

    $statusLabels = [
        'draft' => 'Brouillon',
        'submitted' => 'Soumise',
        'under_review' => 'En cours d\'examen',
        'pending_committee' => 'En attente du comité',
        'approved' => 'Approuvée',
        'rejected' => 'Rejetée',
        'funded' => 'Financée',
        'completed' => 'Terminée',
        'cancelled' => 'Annulée',
    ];

    $currentStatusLabel = $statusLabels[$fundingRequest->status] ?? $fundingRequest->status;
@endphp

<div class="documents-container">

    {{-- En-tête de statut --}}
    <div class="status-card {{ $allCompleted ? 'status-complete' : 'status-pending' }}">
        <div class="status-content">
            <div class="status-indicator">
                @if($allCompleted)
                    <span class="status-check">OK</span>
                @else
                    <span class="status-pending-text">EN COURS</span>
                @endif
            </div>
            <div class="status-details">
                <h2 class="status-title">
                    @if($allCompleted)
                        Dossier complet
                    @else
                        Documents en cours de constitution
                    @endif
                </h2>
                <p class="status-subtitle">
                    Demande <span class="request-number">{{ $fundingRequest->request_number }}</span>
                    <span class="separator">|</span>
                    Statut: <strong>{{ $currentStatusLabel }}</strong>
                </p>
            </div>
        </div>
    </div>

    {{-- Alerte si verrouillé --}}
    @if(!$canUpload)
    <div class="alert alert-locked">
        <p>
            Les documents ne peuvent plus être modifiés car la demande est en <strong>{{ $currentStatusLabel }}</strong>.
        </p>
    </div>
    @endif

    {{-- Récapitulatif de la demande --}}
    <div class="card summary-card">
        <h3 class="card-title">Récapitulatif de la demande</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <span class="summary-label">Type de financement</span>
                <span class="summary-value">{{ $fundingRequest->typeFinancement->name }}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Montant demandé</span>
                <span class="summary-value amount">{{ number_format($fundingRequest->amount_requested, 0, ',', ' ') }} FCFA</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Durée</span>
                <span class="summary-value">{{ $fundingRequest->duration }} mois</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Frais d'inscription</span>
                <span class="summary-value fee">{{ number_format($fundingRequest->registration_fee_paid, 0, ',', ' ') }} FCFA</span>
            </div>
        </div>
    </div>

    {{-- Section Documents --}}
    <div class="card documents-card">
        <h3 class="card-title">Documents requis</h3>

        {{-- Barre de progression --}}
        <div class="progress-section">
            <div class="progress-bar-bg">
                <div class="progress-bar-fill" style="width: {{ $progressPercent }}%"></div>
            </div>
            <div class="progress-stats">
                <span>{{ $filledDocs->count() }} sur {{ $documents->count() }} documents fournis</span>
                <span class="progress-percent">{{ $progressPercent }}%</span>
            </div>
        </div>

        {{-- Documents à compléter --}}
        @if($emptyDocs->count() > 0)
        <div class="documents-section">
            <h4 class="section-title">
                <span class="badge badge-warning">{{ $emptyDocs->count() }}</span>
                Documents à fournir
            </h4>

            <div class="documents-list">
                @foreach($emptyDocs as $doc)
                <div class="document-item" id="doc-{{ $doc->id }}">
                    <div class="document-info">
                        <div class="document-header">
                            <span class="document-name">{{ $doc->typeDoc->name }}</span>
                            @if($doc->typeDoc->is_required ?? true)
                                <span class="badge badge-required">Obligatoire</span>
                            @else
                                <span class="badge badge-optional">Optionnel</span>
                            @endif
                        </div>
                        <p class="document-hint">{{ $doc->typeDoc->description ?? 'Formats acceptés: PDF, JPG, PNG (max 10MB)' }}</p>
                    </div>

                    <div class="document-actions">
                        @if($canUpload)
                        <form action="{{ route('client.documents.store') }}" method="POST" enctype="multipart/form-data" class="upload-form" data-doc-id="{{ $doc->id }}">
                            @csrf
                            <input type="hidden" name="document_user_id" value="{{ $doc->id }}">
                            <input type="hidden" name="funding_request_id" value="{{ $fundingRequest->id }}">
                            <input type="hidden" name="typedoc_id" value="{{ $doc->typedoc_id }}">

                            <label class="file-input-label">
                                <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" onchange="handleFileSelect(this, {{ $doc->id }})" class="file-input">
                                <span class="btn btn-secondary" id="btn-text-{{ $doc->id }}">Choisir un fichier</span>
                            </label>

                            <div class="file-preview" id="preview-{{ $doc->id }}" style="display: none;">
                                <span class="file-name" id="file-name-{{ $doc->id }}"></span>
                                <button type="button" class="btn-remove" onclick="clearFile({{ $doc->id }})">x</button>
                            </div>

                            <button type="submit" class="btn btn-primary" id="submit-{{ $doc->id }}" style="display: none;">
                                Confirmer l'envoi
                            </button>
                        </form>
                        @else
                        <span class="text-muted">Verrouillé</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Documents complétés --}}
        @if($filledDocs->count() > 0)
        <div class="documents-section">
            <h4 class="section-title">
                <span class="badge badge-success">{{ $filledDocs->count() }}</span>
                Documents fournis
            </h4>

            <div class="documents-list">
                @foreach($filledDocs as $doc)
                <div class="document-item document-completed">
                    <div class="document-info">
                        <div class="document-header">
                            <span class="document-name">{{ $doc->typeDoc->name }}</span>
                            <span class="badge badge-{{ $doc->status === 'verified' ? 'verified' : 'pending' }}">
                                {{ $doc->status === 'verified' ? 'Vérifié' : 'En attente de validation' }}
                            </span>
                        </div>
                        <p class="document-meta">
                            {{ $doc->file_name }} ({{ round($doc->file_size / 1024, 1) }} Ko)
                        </p>
                    </div>

                    <div class="document-actions">
                        <a href="{{ route('client.documents.show', $doc) }}" target="_blank" class="btn btn-view">
                            Voir
                        </a>

                        @if($canUpload)
                        <form action="{{ route('client.documents.store') }}" method="POST" enctype="multipart/form-data" class="form-inline">
                            @csrf
                            <input type="hidden" name="document_user_id" value="{{ $doc->id }}">
                            <input type="hidden" name="funding_request_id" value="{{ $fundingRequest->id }}">
                            <input type="hidden" name="typedoc_id" value="{{ $doc->typedoc_id }}">

                            <label class="btn btn-replace" title="Remplacer le document">
                                <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" onchange="this.form.submit()" style="display: none;">
                                Remplacer
                            </label>
                        </form>

                        <form action="{{ route('client.documents.destroy', $doc) }}" method="POST" class="form-inline" onsubmit="return confirm('Supprimer ce document ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-delete">Supprimer</button>
                        </form>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Footer / Actions --}}
        <div class="documents-footer">
            @if($allCompleted)
                <div class="completion-message">
                    <h4>
                        @if(in_array($fundingRequest->status, ['under_review', 'pending_committee']))
                            Dossier en cours d'examen
                        @else
                            Dossier complet
                        @endif
                    </h4>
                    <p>
                        @if(in_array($fundingRequest->status, ['under_review', 'pending_committee']))
                            Votre demande est actuellement étudiée par notre équipe.
                        @else
                            Tous les documents requis ont été fournis.
                        @endif
                    </p>
                    <a href="{{ route('client.requests.show', $fundingRequest) }}" class="btn btn-primary btn-lg">
                        Voir le détail de ma demande
                    </a>
                </div>
            @else
                <div class="info-message">
                    <p>
                        @if($canUpload)
                            Votre demande ne sera traitée qu'après réception de tous les documents obligatoires.
                        @else
                            Documents incomplets. Contactez l'administration pour plus d'informations.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>

</div>

@endsection

@section('styles')
<style>
.documents-container {
    padding-bottom: 2rem;
}

/* Status Card */
.status-card {
    padding: 1.25rem;
    border-radius: 12px;
    margin-bottom: 1rem;
    border: 1px solid transparent;
}

.status-complete {
    background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
    border-color: #86efac;
    color: #166534;
}

.status-pending {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-color: #fcd34d;
    color: #92400e;
}

.status-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.status-indicator {
    width: 48px;
    height: 48px;
    background: white;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.75rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    flex-shrink: 0;
}

.status-check {
    color: #16a34a;
}

.status-pending-text {
    color: #d97706;
    font-size: 0.625rem;
}

.status-title {
    font-size: 1.125rem;
    font-weight: 700;
    margin: 0 0 0.25rem;
}

.status-subtitle {
    margin: 0;
    font-size: 0.9375rem;
    opacity: 0.9;
}

.request-number {
    font-family: monospace;
    background: rgba(255,255,255,0.6);
    padding: 0.125rem 0.5rem;
    border-radius: 4px;
    font-weight: 600;
}

.separator {
    margin: 0 0.5rem;
    opacity: 0.5;
}

/* Alert */
.alert-locked {
    background: #fee2e2;
    border: 1px solid #fecaca;
    color: #991b1b;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.alert-locked p {
    margin: 0;
    font-size: 0.875rem;
}

/* Cards */
.card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1rem;
}

.card-title {
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #f1f5f9;
}

/* Summary Grid */
.summary-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.summary-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.summary-label {
    font-size: 0.75rem;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.summary-value {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #0f172a;
}

.summary-value.amount {
    color: #2563eb;
}

.summary-value.fee {
    color: #16a34a;
}

/* Progress */
.progress-section {
    margin-bottom: 1.5rem;
}

.progress-bar-bg {
    height: 6px;
    background: #e2e8f0;
    border-radius: 3px;
    overflow: hidden;
    margin-bottom: 0.5rem;
}

.progress-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #2563eb, #3b82f6);
    border-radius: 3px;
    transition: width 0.6s ease;
}

.progress-stats {
    display: flex;
    justify-content: space-between;
    font-size: 0.75rem;
    color: #64748b;
}

.progress-percent {
    font-weight: 600;
    color: #2563eb;
}

/* Documents Sections */
.documents-section {
    margin-bottom: 1.5rem;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin: 0 0 1rem;
}

.badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 24px;
    height: 24px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 0 0.5rem;
}

.badge-warning {
    background: #f59e0b;
    color: white;
}

.badge-success {
    background: #10b981;
    color: white;
}

.badge-required {
    background: #fee2e2;
    color: #991b1b;
    font-size: 0.625rem;
    text-transform: uppercase;
}

.badge-optional {
    background: #e2e8f0;
    color: #475569;
    font-size: 0.625rem;
    text-transform: uppercase;
}

.badge-verified {
    background: #dcfce7;
    color: #166534;
    font-size: 0.625rem;
}

.badge-pending {
    background: #fef3c7;
    color: #92400e;
    font-size: 0.625rem;
}

/* Document Items */
.documents-list {
    display: flex;
    flex-direction: column;
    gap: 0.875rem;
}

.document-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    transition: border-color 0.2s;
}

.document-item:hover {
    border-color: #cbd5e1;
}

.document-completed {
    background: #f0fdf4;
    border-color: #bbf7d0;
}

.document-info {
    flex: 1;
    min-width: 0;
}

.document-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 0.25rem;
}

.document-name {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #0f172a;
}

.document-hint {
    font-size: 0.8125rem;
    color: #64748b;
    margin: 0;
}

.document-meta {
    font-size: 0.8125rem;
    color: #16a34a;
    margin: 0;
}

.document-actions {
    display: flex;
    gap: 0.5rem;
    flex-shrink: 0;
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
    text-decoration: none;
}

.btn-primary {
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.btn-secondary {
    background: #eff6ff;
    border: 1.5px solid #bfdbfe;
    color: #2563eb;
}

.btn-secondary:hover {
    background: #dbeafe;
}

.btn-view {
    background: white;
    border: 1.5px solid #e2e8f0;
    color: #475569;
}

.btn-view:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
}

.btn-replace {
    background: #eff6ff;
    border: 1.5px solid #bfdbfe;
    color: #2563eb;
    cursor: pointer;
}

.btn-delete {
    background: #fee2e2;
    border: 1.5px solid #fecaca;
    color: #dc2626;
}

.btn-delete:hover {
    background: #fecaca;
}

.btn-lg {
    padding: 0.875rem 1.5rem;
    font-size: 1rem;
}

/* File Input */
.file-input-label {
    cursor: pointer;
    display: inline-block;
}

.file-input {
    display: none;
}

.file-preview {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.5rem;
    font-size: 0.75rem;
}

.file-name {
    color: #166534;
    max-width: 150px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.btn-remove {
    width: 20px;
    height: 20px;
    border: none;
    background: #fee2e2;
    color: #dc2626;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    line-height: 1;
}

.form-inline {
    display: inline;
}

/* Footer */
.documents-footer {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #f1f5f9;
}

.completion-message {
    text-align: center;
}

.completion-message h4 {
    font-size: 1.125rem;
    font-weight: 700;
    color: #166534;
    margin: 0 0 0.5rem;
}

.completion-message p {
    color: #64748b;
    margin: 0 0 1rem;
}

.info-message {
    padding: 1rem;
    background: #f8fafc;
    border-radius: 8px;
    color: #1e40af;
    font-size: 0.875rem;
}

.info-message p {
    margin: 0;
}

.text-muted {
    color: #94a3b8;
    font-size: 0.75rem;
    font-style: italic;
}

/* Responsive */
@media (max-width: 640px) {
    .summary-grid {
        grid-template-columns: 1fr;
    }

    .document-item {
        flex-direction: column;
        align-items: flex-start;
    }

    .document-actions {
        width: 100%;
        justify-content: flex-end;
    }
}
</style>
@endsection

@section('scripts')
<script>
function handleFileSelect(input, docId) {
    const file = input.files[0];
    if (!file) return;

    const maxSize = 10 * 1024 * 1024;
    const allowedTypes = [
        'application/pdf',
        'image/jpeg',
        'image/jpg',
        'image/png',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];

    if (file.size > maxSize) {
        alert('Le fichier est trop volumineux. Maximum 10Mo.');
        input.value = '';
        return;
    }

    if (!allowedTypes.includes(file.type)) {
        alert('Format non supporte. Utilisez PDF, JPG, PNG, DOC ou DOCX.');
        input.value = '';
        return;
    }

    document.getElementById('file-name-' + docId).textContent = file.name;
    document.getElementById('preview-' + docId).style.display = 'flex';
    document.getElementById('btn-text-' + docId).textContent = 'Changer';
    document.getElementById('submit-' + docId).style.display = 'inline-flex';
}

function clearFile(docId) {
    const form = document.querySelector('#doc-' + docId + ' .upload-form');
    const fileInput = form.querySelector('input[type="file"]');
    fileInput.value = '';

    document.getElementById('preview-' + docId).style.display = 'none';
    document.getElementById('btn-text-' + docId).textContent = 'Choisir un fichier';
    document.getElementById('submit-' + docId).style.display = 'none';
}

// Soumission AJAX
document.querySelectorAll('.upload-form').forEach(form => {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Envoi en cours...';
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
                window.location.reload();
            } else {
                throw new Error(data.message || 'Erreur lors de l\'envoi');
            }
        } catch (error) {
            alert(error.message || 'Erreur lors de l\'envoi du document');
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    });
});
</script>
@endsection
