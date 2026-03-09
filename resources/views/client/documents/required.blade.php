@extends('layouts.app')

@section('title', 'Documents - ' . $fundingRequest->request_number)
@section('header-title', 'Documents requis')

@section('header-action')
<a href="{{ route('client.requests.show', $fundingRequest) }}" class="btn-back" data-transition="slide-right">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
    </svg>
</a>
@endsection

@section('content')

@php
    $canUpload = in_array($fundingRequest->status, ['draft', 'submitted', 'under_review']);

    $emptyDocs = $documents->whereNull('file_path');
    $filledDocs = $documents->whereNotNull('file_path');
    $progressPercent = $documents->count() > 0
        ? round(($filledDocs->count() / $documents->count()) * 100)
        : 0;

    $allCompleted = $emptyDocs->count() === 0;

    $statusLabels = [
        'draft' => 'Brouillon',
        'submitted' => 'Soumise',
        'under_review' => 'En examen',
        'pending_committee' => 'Comité',
        'approved' => 'Approuvée',
        'rejected' => 'Rejetée',
        'funded' => 'Financée',
        'completed' => 'Terminée',
        'cancelled' => 'Annulée',
    ];

    $currentStatusLabel = $statusLabels[$fundingRequest->status] ?? $fundingRequest->status;

    $statusColors = [
        'draft' => ['bg' => '#f3f4f6', 'text' => '#374151', 'border' => '#d1d5db'],
        'submitted' => ['bg' => '#dbeafe', 'text' => '#1e40af', 'border' => '#3b82f6'],
        'under_review' => ['bg' => '#ede9fe', 'text' => '#5b21b6', 'border' => '#8b5cf6'],
        'pending_committee' => ['bg' => '#ede9fe', 'text' => '#5b21b6', 'border' => '#8b5cf6'],
        'approved' => ['bg' => '#d1fae5', 'text' => '#065f46', 'border' => '#10b981'],
        'funded' => ['bg' => '#a7f3d0', 'text' => '#064e3b', 'border' => '#059669'],
        'rejected' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'border' => '#ef4444'],
    ];

    $colors = $statusColors[$fundingRequest->status] ?? $statusColors['draft'];
@endphp

<div class="documents-mobile">

    {{-- Header Navigation --}}
    <div class="documents-header-nav">
        <a href="{{ route('client.requests.index') }}" class="back-link" data-transition="slide-right">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Mes demandes</span>
        </a>
    </div>

    {{-- Hero Card avec statut --}}
    <div class="documents-hero-card" style="border-left-color: {{ $colors['border'] }}">
        <div class="documents-hero-header">
            <div class="status-icon-large" style="background: {{ $colors['bg'] }}; color: {{ $colors['text'] }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                    @if($allCompleted)
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    @endif
                </svg>
            </div>
            <div class="documents-hero-info">
                <span class="request-number-badge">{{ $fundingRequest->request_number }}</span>
                <h1 class="documents-title-mobile">
                    @if($allCompleted)
                        Dossier complet
                    @else
                        Documents requis
                    @endif
                </h1>
                <div class="documents-meta-row">
                    <span class="status-badge-hero" style="background: {{ $colors['bg'] }}; color: {{ $colors['text'] }}; border: 1px solid {{ $colors['border'] }}">
                        {{ $currentStatusLabel }}
                    </span>
                    <span class="progress-badge {{ $allCompleted ? 'complete' : 'pending' }}">
                        {{ $filledDocs->count() }}/{{ $documents->count() }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Barre de progression --}}
        <div class="progress-section-mobile">
            <div class="progress-bar-bg">
                <div class="progress-bar-fill" style="width: {{ $progressPercent }}%"></div>
            </div>
            <div class="progress-text">
                <span>{{ $progressPercent }}% complété</span>
            </div>
        </div>
    </div>

    {{-- Alerte si verrouillé --}}
    @if(!$canUpload)
    <div class="status-card status-locked">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        <p>Documents verrouillés - Demande en <strong>{{ $currentStatusLabel }}</strong></p>
    </div>
    @endif

    {{-- Récapitulatif de la demande --}}
    <div class="info-section">
        <div class="section-header">
            <div class="section-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <h2 class="section-title">Récapitulatif</h2>
        </div>

        <div class="details-card">
            <div class="detail-row">
                <span class="detail-label">Type</span>
                <span class="detail-value">{{ $fundingRequest->typeFinancement->name ?? 'Non spécifié' }}</span>
            </div>
            <div class="detail-row highlight">
                <span class="detail-label">Montant</span>
                <span class="detail-value amount">{{ number_format($fundingRequest->amount_requested, 0, ',', ' ') }} FCFA</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Durée</span>
                <span class="detail-value">{{ $fundingRequest->duration }} mois</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Frais payés</span>
                <span class="detail-value fee">{{ number_format($fundingRequest->registration_fee_paid ?? 0, 0, ',', ' ') }} FCFA</span>
            </div>
        </div>
    </div>

    {{-- Documents à compléter --}}
    @if($emptyDocs->count() > 0)
    <div class="info-section">
        <div class="section-header">
            <div class="section-icon section-icon-warning">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h2 class="section-title">À compléter</h2>
            <span class="section-badge badge-warning">{{ $emptyDocs->count() }}</span>
        </div>

        <div class="documents-list">
            @foreach($emptyDocs as $doc)
            <div class="document-upload-card" id="doc-{{ $doc->id }}">
                <div class="document-upload-header">
                    <div class="document-icon-upload">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                    </div>
                    <div class="document-upload-info">
                        <div class="document-name-row">
                            <span class="document-name">{{ $doc->typeDoc->name }}</span>
                            @if($doc->typeDoc->is_required ?? true)
                                <span class="badge-required">Requis</span>
                            @endif
                        </div>
                        <p class="document-hint">{{ $doc->typeDoc->description ?? 'PDF, JPG, PNG (max 10MB)' }}</p>
                    </div>
                </div>

                @if($canUpload)
                <form action="{{ route('client.documents.store') }}" method="POST" enctype="multipart/form-data" class="upload-form-mobile" data-doc-id="{{ $doc->id }}">
                    @csrf
                    <input type="hidden" name="document_user_id" value="{{ $doc->id }}">
                    <input type="hidden" name="funding_request_id" value="{{ $fundingRequest->id }}">
                    <input type="hidden" name="typedoc_id" value="{{ $doc->typedoc_id }}">

                    <div class="upload-zone" id="upload-zone-{{ $doc->id }}">
                        <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" 
                               onchange="handleFileSelect(this, {{ $doc->id }})" 
                               class="file-input-hidden" id="file-{{ $doc->id }}">
                        
                        <label for="file-{{ $doc->id }}" class="upload-label" id="label-{{ $doc->id }}">
                            <div class="upload-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="28" height="28">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                            </div>
                            <span class="upload-text">Touchez pour ajouter</span>
                            <span class="upload-subtext">ou glissez-déposez</span>
                        </label>

                        <div class="file-preview-mobile" id="preview-{{ $doc->id }}" style="display: none;">
                            <div class="file-info">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="file-name" id="file-name-{{ $doc->id }}"></span>
                            </div>
                            <button type="button" class="btn-remove-file" onclick="clearFile({{ $doc->id }})">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-submit-upload" id="submit-{{ $doc->id }}" style="display: none;">
                        <span class="btn-text">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Confirmer l'envoi
                        </span>
                        <span class="btn-loader">
                            <div class="spinner-dual small"></div>
                        </span>
                    </button>
                </form>
                @else
                <div class="upload-locked">
                    <span>Verrouillé</span>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Documents complétés --}}
    @if($filledDocs->count() > 0)
    <div class="info-section">
        <div class="section-header">
            <div class="section-icon section-icon-success">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="section-title">Fournis</h2>
            <span class="section-badge badge-success">{{ $filledDocs->count() }}</span>
        </div>

        <div class="documents-list-completed">
            @foreach($filledDocs as $doc)
            <div class="document-completed-card">
                <div class="document-completed-header">
                    <div class="document-status-icon {{ $doc->status }}">
                        @if($doc->status === 'verified')
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @endif
                    </div>
                    <div class="document-completed-info">
                        <span class="document-name-completed">{{ $doc->typeDoc->name }}</span>
                        <span class="document-meta-completed">{{ $doc->file_name }} • {{ round($doc->file_size / 1024, 1) }} Ko</span>
                    </div>
                    <span class="status-mini-badge {{ $doc->status }}">
                        {{ $doc->status === 'verified' ? 'Vérifié' : 'En attente' }}
                    </span>
                </div>

                <div class="document-completed-actions">
                    <a href="{{ route('client.documents.show', $doc) }}" target="_blank" class="btn btn-view-doc">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Voir
                    </a>

                    @if($canUpload)
                    <form action="{{ route('client.documents.store') }}" method="POST" enctype="multipart/form-data" class="form-replace">
                        @csrf
                        <input type="hidden" name="document_user_id" value="{{ $doc->id }}">
                        <input type="hidden" name="funding_request_id" value="{{ $fundingRequest->id }}">
                        <input type="hidden" name="typedoc_id" value="{{ $doc->typedoc_id }}">

                        <label class="btn btn-replace-doc" title="Remplacer">
                            <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" onchange="this.form.submit()" style="display: none;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Remplacer
                        </label>
                    </form>

                    <form action="{{ route('client.documents.destroy', $doc) }}" method="POST" class="form-delete" onsubmit="return confirm('Supprimer ce document ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-delete-doc">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Footer Actions --}}
    <div class="documents-footer-mobile">
        @if($allCompleted)
            <div class="completion-card">
                <div class="completion-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="48" height="48">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3>Dossier complet !</h3>
                <p>
                    @if(in_array($fundingRequest->status, ['under_review', 'pending_committee', 'approved']))
                        Votre demande est en cours de traitement.
                    @else
                        Tous les documents sont fournis. Soumettez votre demande.
                    @endif
                </p>
                <a href="{{ route('client.requests.show', $fundingRequest) }}" class="btn btn-primary btn-lg btn-full">
                    Voir ma demande
                </a>
            </div>
        @else
            <div class="info-card">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p>Complétez tous les documents obligatoires pour finaliser votre demande.</p>
            </div>
        @endif
    </div>

    {{-- Contact --}}
    <div class="contact-section">
        <div class="contact-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>
        <div class="contact-content">
            <h3>Besoin d'aide ?</h3>
            <p>Contactez-nous pour l'upload de documents</p>
        </div>
        <a href="mailto:support@bhdm.com" class="btn btn-outline btn-sm">Contacter</a>
    </div>

</div>

@endsection

@section('styles')
<style>
/* ============================================
   DOCUMENTS - Mobile First (cohérent avec app.blade.php)
   ============================================ */

.documents-mobile {
    padding: 16px;
    padding-bottom: 100px;
    max-width: 100%;
    overflow-x: hidden;
    background-color: var(--bg-primary, #ffffff);
    min-height: 100vh;
}

/* Header Nav */
.documents-header-nav {
    margin-bottom: 16px;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--text-secondary, #475569);
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    padding: 8px 0;
    transition: color 0.2s;
}

.back-link:active {
    color: var(--primary-600, #1e40af);
}

/* Hero Card */
.documents-hero-card {
    background: var(--bg-elevated, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-left-width: 4px;
    border-radius: 20px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.documents-hero-header {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 16px;
}

.status-icon-large {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.documents-hero-info {
    flex: 1;
}

.request-number-badge {
    display: inline-block;
    font-family: ui-monospace, monospace;
    font-size: 0.75rem;
    font-weight: 600;
    color: #3b82f6;
    background: #eff6ff;
    padding: 4px 10px;
    border-radius: 20px;
    margin-bottom: 6px;
}

.documents-title-mobile {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary, #0f172a);
    margin: 0 0 10px 0;
    line-height: 1.3;
}

.documents-meta-row {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.status-badge-hero {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    padding: 6px 12px;
    border-radius: 20px;
}

.progress-badge {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 12px;
    background: #fef3c7;
    color: #92400e;
}

.progress-badge.complete {
    background: #dcfce7;
    color: #166534;
}

/* Progress Section */
.progress-section-mobile {
    padding-top: 16px;
    border-top: 1px solid var(--border-light, #f1f5f9);
}

.progress-bar-bg {
    height: 6px;
    background: #e2e8f0;
    border-radius: 3px;
    overflow: hidden;
    margin-bottom: 8px;
}

.progress-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #2563eb, #3b82f6);
    border-radius: 3px;
    transition: width 0.6s ease;
}

.progress-text {
    font-size: 0.8125rem;
    color: var(--text-muted, #94a3b8);
    text-align: center;
}

/* Status Locked */
.status-locked {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    background: #fee2e2;
    border: 1px solid #fecaca;
    border-radius: 12px;
    color: #991b1b;
    margin-bottom: 20px;
}

.status-locked svg {
    flex-shrink: 0;
}

.status-locked p {
    margin: 0;
    font-size: 0.875rem;
    line-height: 1.5;
}

/* Info Sections (cohérent avec show.blade.php) */
.info-section {
    margin-bottom: 24px;
}

.section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}

.section-icon {
    width: 40px;
    height: 40px;
    background: #eff6ff;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3b82f6;
    flex-shrink: 0;
}

.section-icon-warning {
    background: #fef3c7;
    color: #f59e0b;
}

.section-icon-success {
    background: #dcfce7;
    color: #22c55e;
}

.section-title {
    font-size: 1.0625rem;
    font-weight: 700;
    color: var(--text-primary, #0f172a);
    margin: 0;
    flex: 1;
}

.section-badge {
    font-size: 0.6875rem;
    font-weight: 700;
    padding: 6px 12px;
    border-radius: 20px;
}

.badge-warning {
    background: #fef3c7;
    color: #92400e;
}

.badge-success {
    background: #dcfce7;
    color: #166534;
}

/* Details Card */
.details-card {
    background: var(--bg-elevated, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 16px;
    padding: 20px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 0;
    border-bottom: 1px solid var(--border-light, #f1f5f9);
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    font-size: 0.875rem;
    color: var(--text-muted, #94a3b8);
}

.detail-value {
    font-size: 0.9375rem;
    font-weight: 600;
    color: var(--text-primary, #0f172a);
}

.detail-value.amount {
    color: #1e40af;
}

.detail-value.fee {
    color: #16a34a;
}

/* Document Upload Cards */
.documents-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.document-upload-card {
    background: var(--bg-elevated, #ffffff);
    border: 2px dashed #e2e8f0;
    border-radius: 16px;
    padding: 20px;
    transition: all 0.2s;
}

.document-upload-card:hover {
    border-color: #bfdbfe;
}

.document-upload-header {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 16px;
}

.document-icon-upload {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3b82f6;
    flex-shrink: 0;
}

.document-upload-info {
    flex: 1;
    min-width: 0;
}

.document-name-row {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 4px;
}

.document-name {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-primary, #0f172a);
}

.badge-required {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    padding: 4px 8px;
    background: #fee2e2;
    color: #991b1b;
    border-radius: 4px;
}

.document-hint {
    font-size: 0.8125rem;
    color: var(--text-muted, #94a3b8);
    margin: 0;
}

/* Upload Zone */
.upload-zone {
    position: relative;
    background: #f8fafc;
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
    padding: 24px;
    text-align: center;
    transition: all 0.2s;
    cursor: pointer;
}

.upload-zone:hover {
    border-color: #3b82f6;
    background: #eff6ff;
}

.file-input-hidden {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
}

.upload-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    pointer-events: none;
}

.upload-icon {
    color: #94a3b8;
}

.upload-text {
    font-size: 0.9375rem;
    font-weight: 600;
    color: var(--text-primary, #0f172a);
}

.upload-subtext {
    font-size: 0.75rem;
    color: var(--text-muted, #94a3b8);
}

.file-preview-mobile {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.file-info {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #166534;
}

.file-name {
    font-size: 0.875rem;
    font-weight: 600;
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.btn-remove-file {
    width: 32px;
    height: 32px;
    border: none;
    background: #fee2e2;
    color: #dc2626;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.btn-remove-file:hover {
    background: #fecaca;
}

/* Submit Button */
.btn-submit-upload {
    width: 100%;
    margin-top: 12px;
    padding: 14px;
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 0.9375rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s;
    position: relative;
    overflow: hidden;
}

.btn-submit-upload:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
}

.btn-submit-upload:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.btn-text, .btn-loader {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: opacity 0.2s;
}

.btn-loader {
    position: absolute;
    inset: 0;
    opacity: 0;
}

.btn-submit-upload.loading .btn-text {
    opacity: 0;
}

.btn-submit-upload.loading .btn-loader {
    opacity: 1;
}

.spinner-dual {
    width: 20px;
    height: 20px;
    position: relative;
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
    border-top-color: white;
    border-right-color: white;
    animation: spin 1s linear infinite;
}

.spinner-dual::after {
    border-bottom-color: rgba(255,255,255,0.3);
    border-left-color: rgba(255,255,255,0.3);
    animation: spin 1.5s linear infinite reverse;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.upload-locked {
    text-align: center;
    padding: 16px;
    color: var(--text-muted, #94a3b8);
    font-style: italic;
}

/* Completed Documents */
.documents-list-completed {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.document-completed-card {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 14px;
    padding: 16px;
}

.document-completed-header {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 12px;
}

.document-status-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.document-status-icon.verified {
    background: #dcfce7;
    color: #16a34a;
}

.document-status-icon.pending {
    background: #fef3c7;
    color: #d97706;
}

.document-completed-info {
    flex: 1;
    min-width: 0;
}

.document-name-completed {
    display: block;
    font-size: 0.9375rem;
    font-weight: 600;
    color: var(--text-primary, #0f172a);
    margin-bottom: 4px;
}

.document-meta-completed {
    font-size: 0.75rem;
    color: var(--text-muted, #94a3b8);
}

.status-mini-badge {
    font-size: 0.625rem;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
    white-space: nowrap;
}

.status-mini-badge.verified {
    background: #dcfce7;
    color: #166534;
}

.status-mini-badge.pending {
    background: #fef3c7;
    color: #92400e;
}

.document-completed-actions {
    display: flex;
    gap: 8px;
    padding-top: 12px;
    border-top: 1px solid #bbf7d0;
}

.btn-view-doc, .btn-replace-doc, .btn-delete-doc {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 600;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-view-doc {
    background: white;
    color: #374151;
    border: 1px solid #e2e8f0;
}

.btn-view-doc:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}

.btn-replace-doc {
    background: #eff6ff;
    color: #2563eb;
    cursor: pointer;
}

.btn-replace-doc:hover {
    background: #dbeafe;
}

.btn-delete-doc {
    background: #fee2e2;
    color: #dc2626;
    padding: 8px;
}

.btn-delete-doc:hover {
    background: #fecaca;
}

.form-replace, .form-delete {
    display: inline;
}

/* Footer */
.documents-footer-mobile {
    margin-top: 32px;
}

.completion-card {
    text-align: center;
    padding: 32px 24px;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    border: 2px solid #86efac;
    border-radius: 20px;
}

.completion-icon {
    width: 80px;
    height: 80px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #22c55e;
    margin: 0 auto 16px;
    box-shadow: 0 8px 24px rgba(34, 197, 94, 0.2);
}

.completion-card h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: #166534;
    margin: 0 0 8px 0;
}

.completion-card p {
    font-size: 0.9375rem;
    color: #15803d;
    margin: 0 0 20px 0;
}

.info-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 16px;
    color: #1e40af;
}

.info-card p {
    margin: 0;
    font-size: 0.9375rem;
    line-height: 1.5;
}

.btn-full {
    width: 100%;
    justify-content: center;
}

/* Buttons génériques */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 20px;
    border-radius: 12px;
    font-size: 0.9375rem;
    font-weight: 600;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary {
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    color: white;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(37, 99, 235, 0.45);
}

.btn-lg {
    padding: 16px 24px;
    font-size: 1rem;
}

.btn-outline {
    background: none;
    border: 1px solid var(--border-color, #e2e8f0);
    color: var(--text-secondary, #475569);
    padding: 10px 20px;
    border-radius: 10px;
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
}

.btn-outline:hover {
    background: var(--surface, #ffffff);
    color: var(--text, #0f172a);
    border-color: var(--text-muted, #64748b);
}

.btn-sm {
    padding: 8px 16px;
    font-size: 0.875rem;
}

/* Contact Section (cohérent avec show.blade.php) */
.contact-section {
    display: flex;
    align-items: center;
    gap: 16px;
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border: 1px dashed var(--border-color, #e2e8f0);
    border-radius: 20px;
    padding: 20px;
    margin-top: 32px;
}

.contact-icon {
    width: 48px;
    height: 48px;
    background: white;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3b82f6;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}

.contact-content {
    flex: 1;
}

.contact-content h3 {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-primary, #0f172a);
    margin: 0 0 4px 0;
}

.contact-content p {
    font-size: 0.875rem;
    color: var(--text-muted, #94a3b8);
    margin: 0;
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
    :root {
        --bg-primary: #0f172a;
        --bg-secondary: #1e293b;
        --bg-elevated: #1e293b;
        --text-primary: #f8fafc;
        --text-secondary: #e2e8f0;
        --text-muted: #64748b;
        --border-color: #334155;
        --border-light: #1e293b;
    }

    .status-icon-large { color: inherit; }
    .document-icon-upload { background: #1e293b; }
    .document-status-icon { background: #0f172a; }
    .document-status-icon.verified { background: #14532d; }
    .document-status-icon.pending { background: #451a03; }
    .upload-zone { background: #0f172a; border-color: #334155; }
    .upload-zone:hover { background: #1e293b; }
    .document-completed-card { background: #14532d; border-color: #166534; }
    .document-completed-actions { border-color: #166534; }
    .completion-card { background: linear-gradient(135deg, #14532d, #166534); }
    .completion-icon { background: #1e293b; }
    .info-card { background: #1e3a8a; border-color: #3b82f6; }
    .contact-icon { background: #1e293b; }
    .btn-view-doc { background: #1e293b; border-color: #334155; color: #f8fafc; }
    .btn-replace-doc { background: #1e3a8a; color: #60a5fa; }
    .btn-delete-doc { background: #450a0a; color: #f87171; }
    .file-preview-mobile .file-info { color: #4ade80; }
    .btn-remove-file { background: #450a0a; color: #f87171; }
}

/* Responsive */
@media (min-width: 640px) {
    .documents-mobile {
        padding: 24px;
        max-width: 600px;
        margin: 0 auto;
    }

    .document-completed-actions {
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

    const maxSize = 10 * 1024 * 1024; // 10MB
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
        alert('Format non supporté. Utilisez PDF, JPG, PNG, DOC ou DOCX.');
        input.value = '';
        return;
    }

    // Afficher le preview
    document.getElementById('file-name-' + docId).textContent = file.name;
    document.getElementById('preview-' + docId).style.display = 'flex';
    document.getElementById('label-' + docId).style.display = 'none';
    document.getElementById('submit-' + docId).style.display = 'flex';
}

function clearFile(docId) {
    const form = document.querySelector('#doc-' + docId + ' .upload-form-mobile');
    const fileInput = form.querySelector('input[type="file"]');
    fileInput.value = '';

    document.getElementById('preview-' + docId).style.display = 'none';
    document.getElementById('label-' + docId).style.display = 'flex';
    document.getElementById('submit-' + docId).style.display = 'none';
}

// Soumission AJAX avec feedback visuel
document.querySelectorAll('.upload-form-mobile').forEach(form => {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const submitBtn = this.querySelector('.btn-submit-upload');
        const docId = this.dataset.docId;
        
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
                // Animation de succès avant reload
                const card = document.getElementById('doc-' + docId);
                card.style.transform = 'scale(0.95)';
                card.style.opacity = '0.5';
                
                setTimeout(() => {
                    window.location.reload();
                }, 300);
            } else {
                throw new Error(data.message || 'Erreur lors de l\'envoi');
            }
        } catch (error) {
            alert(error.message || 'Erreur lors de l\'envoi du document');
            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
        }
    });
});

// Support du drag & drop
document.querySelectorAll('.upload-zone').forEach(zone => {
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        zone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        zone.addEventListener(eventName, () => {
            zone.style.borderColor = '#3b82f6';
            zone.style.background = '#eff6ff';
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        zone.addEventListener(eventName, () => {
            zone.style.borderColor = '';
            zone.style.background = '';
        });
    });

    zone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        const fileInput = zone.querySelector('input[type="file"]');
        
        if (files.length > 0) {
            fileInput.files = files;
            // Déclencher l'événement change manuellement
            fileInput.dispatchEvent(new Event('change'));
        }
    });
});
</script>
@endsection