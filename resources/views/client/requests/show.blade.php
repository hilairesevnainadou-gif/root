@extends('layouts.app')

@section('title', 'Détails de la demande - ' . $fundingRequest->request_number)
@section('header-title', 'Demande #' . $fundingRequest->request_number)

@section('content')

@php
    // Définition des variables si non passées par le contrôleur
    if (!isset($documents)) {
        $documents = $fundingRequest->documentUsers ?? collect();
    }
    
    if (!isset($providedDocs)) {
        $providedDocs = $documents->filter(function ($doc) {
            return !empty($doc->file_path);
        });
    }
    
    if (!isset($missingDocs)) {
        $missingDocs = $documents->filter(function ($doc) {
            return empty($doc->file_path);
        });
    }
    
    // Si toujours vide, vérifier les documents requis
    if ($missingDocs->isEmpty() && isset($fundingRequest->typeFinancement->requiredTypeDocs)) {
        $existingIds = $documents->pluck('typedoc_id')->toArray();
        $missingDocs = $fundingRequest->typeFinancement->requiredTypeDocs->whereNotIn('id', $existingIds);
    }

    // Status labels et couleurs
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

    $statusColors = [
        'draft' => ['bg' => '#f3f4f6', 'text' => '#374151', 'border' => '#d1d5db'],
        'submitted' => ['bg' => '#dbeafe', 'text' => '#1e40af', 'border' => '#3b82f6'],
        'under_review' => ['bg' => '#ede9fe', 'text' => '#5b21b6', 'border' => '#8b5cf6'],
        'pending_committee' => ['bg' => '#ede9fe', 'text' => '#5b21b6', 'border' => '#8b5cf6'],
        'approved' => ['bg' => '#d1fae5', 'text' => '#065f46', 'border' => '#10b981'],
        'funded' => ['bg' => '#a7f3d0', 'text' => '#064e3b', 'border' => '#059669'],
        'rejected' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'border' => '#ef4444'],
        'completed' => ['bg' => '#f3f4f6', 'text' => '#374151', 'border' => '#9ca3af'],
        'cancelled' => ['bg' => '#f3f4f6', 'text' => '#6b7280', 'border' => '#d1d5db'],
    ];

    $colors = $statusColors[$fundingRequest->status] ?? $statusColors['draft'];
    $statusLabel = $statusLabels[$fundingRequest->status] ?? $fundingRequest->status;
@endphp

<div class="request-show-mobile">

    {{-- Header Navigation --}}
    <div class="request-header-nav">
        <a href="{{ route('client.requests.index') }}" class="back-link" data-transition="slide-right">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Mes demandes</span>
        </a>
    </div>

    {{-- Hero Card avec statut --}}
    <div class="request-hero-card" style="border-left-color: {{ $colors['border'] }}">
        <div class="request-hero-header">
            <div class="status-icon-large" style="background: {{ $colors['bg'] }}; color: {{ $colors['text'] }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                    @if($fundingRequest->status === 'draft')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    @elseif($fundingRequest->status === 'submitted')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    @elseif($fundingRequest->status === 'under_review' || $fundingRequest->status === 'pending_committee')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    @elseif($fundingRequest->status === 'approved')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    @elseif($fundingRequest->status === 'funded')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    @elseif($fundingRequest->status === 'rejected')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    @endif
                </svg>
            </div>
            <div class="request-hero-info">
                <span class="request-number-badge">{{ $fundingRequest->request_number }}</span>
                <h1 class="request-title-mobile">{{ $fundingRequest->title }}</h1>
                <div class="request-meta-row">
                    <span class="status-badge-hero" style="background: {{ $colors['bg'] }}; color: {{ $colors['text'] }}; border: 1px solid {{ $colors['border'] }}">
                        {{ $statusLabel }}
                    </span>
                    <span class="request-date">{{ $fundingRequest->created_at->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>

        {{-- Badge paiement --}}
        <div class="payment-status-row">
            @if($fundingRequest->payment_status === 'pending')
                <span class="payment-badge badge-warning">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Paiement en attente
                </span>
            @elseif($fundingRequest->payment_status === 'paid')
                <span class="payment-badge badge-success">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Payée
                </span>
            @elseif($fundingRequest->payment_status === 'failed')
                <span class="payment-badge badge-danger">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Paiement échoué
                </span>
            @endif
        </div>
    </div>

    {{-- Actions principales --}}
    <div class="request-actions-mobile">
        
        {{-- ÉTAPE 1: DRAFT + PAIEMENT EN ATTENTE --}}
        @if($fundingRequest->status === 'draft' && $fundingRequest->payment_status === 'pending')
            <div class="action-card action-urgent">
                <div class="action-icon-bg">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a1 1 0 11-2 0 1 1 0 012 0z"/>
                    </svg>
                </div>
                <div class="action-content">
                    <h3>Frais d'inscription à payer</h3>
                    <p class="action-amount">{{ number_format($fees['registration'], 0, ',', ' ') }} FCFA</p>
                </div>
                <a href="{{ route('client.requests.payment', $fundingRequest) }}" class="btn btn-primary btn-full">
                    Payer maintenant
                </a>
            </div>

            <form action="{{ route('client.requests.destroy', $fundingRequest) }}" method="POST" class="cancel-form" onsubmit="return confirm('Annuler cette demande ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-text-danger">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Annuler la demande
                </button>
            </form>

        {{-- ÉTAPE 2: PAYÉ + DOCUMENTS MANQUANTS --}}
        @elseif($fundingRequest->payment_status === 'paid' && $missingDocs->count() > 0)
            <div class="action-card action-warning">
                <div class="action-icon-bg">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="action-content">
                    <h3>Documents manquants</h3>
                    <p>{{ $missingDocs->count() }} document{{ $missingDocs->count() > 1 ? 's' : '' }} à fournir</p>
                </div>
                <a href="{{ route('client.documents.required', $fundingRequest) }}" class="btn btn-warning btn-full">
                    Compléter
                </a>
            </div>

        {{-- ÉTAPES SUIVANTES: Messages de statut --}}
        @elseif($fundingRequest->status === 'submitted')
            <div class="status-card status-info">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p>Votre demande est en attente d'examen par notre équipe.</p>
            </div>

        @elseif(in_array($fundingRequest->status, ['under_review', 'pending_committee']))
            <div class="status-card status-warning">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <p>Votre demande est en cours d'analyse.</p>
            </div>

        @elseif($fundingRequest->status === 'approved')
            <div class="status-card status-success">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p>Demande approuvée ! Le financement est en préparation.</p>
            </div>

        @elseif($fundingRequest->status === 'funded')
            <div class="status-card status-success">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p>Financement effectué ! Les fonds ont été versés.</p>
            </div>

        @elseif($fundingRequest->status === 'rejected')
            <div class="status-card status-danger">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p>Demande non retenue. {{ $fundingRequest->rejection_reason ? 'Motif : ' . $fundingRequest->rejection_reason : '' }}</p>
            </div>
        @endif

    </div>

    {{-- Détails du financement --}}
    <div class="info-section">
        <div class="section-header">
            <div class="section-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="section-title">Détails du financement</h2>
        </div>

        <div class="details-card">
            <div class="detail-row">
                <span class="detail-label">Type</span>
                <span class="detail-value">{{ $fundingRequest->typeFinancement->name ?? 'Non spécifié' }}</span>
            </div>
            <div class="detail-row highlight">
                <span class="detail-label">Montant demandé</span>
                <span class="detail-value amount">{{ number_format($fundingRequest->amount_requested, 0, ',', ' ') }} FCFA</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Durée</span>
                <span class="detail-value">{{ $fundingRequest->duration }} mois</span>
            </div>
            @if($fundingRequest->description)
            <div class="detail-row description">
                <span class="detail-label">Description</span>
                <span class="detail-value">{{ $fundingRequest->description }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Frais à payer (CORRECTION: l'utilisateur paie, ne reçoit pas) --}}
    <div class="info-section">
        <div class="section-header">
            <div class="section-icon section-icon-fees">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <h2 class="section-title">Frais à payer</h2>
        </div>

        <div class="fees-card">
            <div class="fee-item">
                <div class="fee-info">
                    <span class="fee-name">Frais d'inscription</span>
                    <span class="fee-desc">À payer maintenant pour valider</span>
                </div>
                <span class="fee-amount">{{ number_format($fees['registration'], 0, ',', ' ') }} FCFA</span>
            </div>
            
            <div class="fee-item">
                <div class="fee-info">
                    <span class="fee-name">Frais de dossier final</span>
                    <span class="fee-desc">À payer lors de la signature</span>
                </div>
                <span class="fee-amount">{{ number_format($fees['final'], 0, ',', ' ') }} FCFA</span>
            </div>

            <div class="fee-divider"></div>

            <div class="fee-item fee-total">
                <div class="fee-info">
                    <span class="fee-name">Total des frais</span>
                </div>
                <span class="fee-amount total">{{ number_format($fees['total_fees'], 0, ',', ' ') }} FCFA</span>
            </div>

            @if($fundingRequest->registration_fee_paid > 0)
            <div class="fee-paid-badge">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ number_format($fundingRequest->registration_fee_paid, 0, ',', ' ') }} FCFA payés le {{ $fundingRequest->paid_at?->format('d/m/Y') }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Documents --}}
    <div class="info-section">
        <div class="section-header">
            <div class="section-icon section-icon-docs">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h2 class="section-title">Documents</h2>
            @if($missingDocs->count() > 0)
                <span class="section-badge badge-warning">{{ $missingDocs->count() }} manquant{{ $missingDocs->count() > 1 ? 's' : '' }}</span>
            @else
                <span class="section-badge badge-success">Complets</span>
            @endif
        </div>

        <div class="documents-card">
            @if($providedDocs->count() > 0)
                <div class="docs-section">
                    <h4>Fournis ({{ $providedDocs->count() }})</h4>
                    @foreach($providedDocs as $doc)
                        <div class="doc-row {{ $doc->status }}">
                            <div class="doc-status-icon">
                                @if($doc->status === 'verified')
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @else
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @endif
                            </div>
                            <span class="doc-name">{{ $doc->typeDoc->name ?? 'Document' }}</span>
                            <span class="doc-status">{{ $doc->getStatusLabel() ?? 'En attente' }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($missingDocs->count() > 0)
                <div class="docs-section missing">
                    <h4>Manquants ({{ $missingDocs->count() }})</h4>
                    @foreach($missingDocs as $doc)
                        <div class="doc-row missing">
                            <div class="doc-status-icon missing">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <span class="doc-name">{{ $doc->typeDoc->name ?? $doc->name ?? 'Document requis' }}</span>
                        </div>
                    @endforeach
                </div>

                @if($fundingRequest->payment_status === 'paid')
                    <a href="{{ route('client.documents.required', $fundingRequest) }}" class="btn btn-warning btn-sm btn-full">
                        Ajouter les documents manquants
                    </a>
                @endif
            @endif
        </div>
    </div>

    {{-- Timeline --}}
    <div class="info-section">
        <div class="section-header">
            <div class="section-icon section-icon-timeline">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="section-title">Suivi</h2>
        </div>

        <div class="timeline-mobile">
            @foreach($timeline as $step)
                <div class="timeline-step {{ $step['completed'] ? 'completed' : '' }} {{ $step['active'] ? 'active' : '' }}">
                    <div class="step-marker">
                        @if($step['completed'])
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            <span class="step-number">{{ $loop->iteration }}</span>
                        @endif
                    </div>
                    <div class="step-content">
                        <span class="step-label">{{ $step['label'] }}</span>
                        @if($step['date'])
                            <span class="step-date">{{ $step['date']->format('d/m/Y') }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
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
            <p>Contactez-nous pour toute question</p>
        </div>
        <a href="mailto:support@bhdm.com" class="btn btn-outline btn-sm">Contacter</a>
    </div>

</div>

@endsection

@section('styles')
<style>
/* ============================================
   REQUEST SHOW - Mobile First
   ============================================ */

.request-show-mobile {
    padding: 16px;
    padding-bottom: 100px;
    max-width: 100%;
    overflow-x: hidden;
    background-color: var(--bg-primary, #ffffff);
    min-height: 100vh;
}

/* Header Nav */
.request-header-nav {
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
.request-hero-card {
    background: var(--bg-elevated, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-left-width: 4px;
    border-radius: 20px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.request-hero-header {
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

.request-hero-info {
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

.request-title-mobile {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary, #0f172a);
    margin: 0 0 10px 0;
    line-height: 1.3;
}

.request-meta-row {
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

.request-date {
    font-size: 0.8125rem;
    color: var(--text-muted, #94a3b8);
}

.payment-status-row {
    display: flex;
    gap: 8px;
    padding-top: 16px;
    border-top: 1px solid var(--border-light, #f1f5f9);
}

.payment-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8125rem;
    font-weight: 600;
    padding: 8px 14px;
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

.badge-danger {
    background: #fee2e2;
    color: #991b1b;
}

/* Actions */
.request-actions-mobile {
    margin-bottom: 24px;
}

.action-card {
    display: flex;
    align-items: center;
    gap: 16px;
    background: var(--bg-elevated, #ffffff);
    border: 2px solid var(--border-color, #e2e8f0);
    border-radius: 20px;
    padding: 20px;
    margin-bottom: 12px;
}

.action-urgent {
    background: linear-gradient(135deg, #fef3c7, #fffbeb);
    border-color: #f59e0b;
}

.action-warning {
    background: linear-gradient(135deg, #fef3c7, #fffbeb);
    border-color: #fbbf24;
}

.action-icon-bg {
    width: 56px;
    height: 56px;
    background: white;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #f59e0b;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.action-content {
    flex: 1;
}

.action-content h3 {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-primary, #0f172a);
    margin: 0 0 4px 0;
}

.action-amount {
    font-size: 1.25rem;
    font-weight: 800;
    color: #f59e0b;
    margin: 0;
}

.btn-full {
    width: 100%;
    justify-content: center;
    margin-top: 12px;
}

.btn-text-danger {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 12px;
    background: none;
    border: none;
    color: #dc2626;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
}

.cancel-form {
    margin-top: 12px;
}

/* Status Cards */
.status-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
    border-radius: 16px;
    margin-bottom: 16px;
}

.status-card svg {
    flex-shrink: 0;
}

.status-card p {
    margin: 0;
    font-size: 0.9375rem;
    font-weight: 500;
    line-height: 1.5;
}

.status-info {
    background: #dbeafe;
    color: #1e40af;
}

.status-warning {
    background: #fef3c7;
    color: #92400e;
}

.status-success {
    background: #dcfce7;
    color: #166534;
}

.status-danger {
    background: #fee2e2;
    color: #991b1b;
}

/* Info Sections */
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

.section-icon-fees {
    background: #fef3c7;
    color: #f59e0b;
}

.section-icon-docs {
    background: #ede9fe;
    color: #8b5cf6;
}

.section-icon-timeline {
    background: #f0fdf4;
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

.detail-row.description {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
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
    font-size: 1.125rem;
    color: #1e40af;
}

/* Fees Card */
.fees-card {
    background: var(--bg-elevated, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 16px;
    padding: 20px;
}

.fee-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 0;
}

.fee-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.fee-name {
    font-size: 0.9375rem;
    font-weight: 600;
    color: var(--text-primary, #0f172a);
}

.fee-desc {
    font-size: 0.75rem;
    color: var(--text-muted, #94a3b8);
}

.fee-amount {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-primary, #0f172a);
}

.fee-amount.total {
    font-size: 1.125rem;
    color: #1e40af;
}

.fee-divider {
    height: 1px;
    background: var(--border-color, #e2e8f0);
    margin: 8px 0;
}

.fee-paid-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 16px;
    padding: 12px 16px;
    background: #dcfce7;
    color: #166534;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 500;
}

/* Documents Card */
.documents-card {
    background: var(--bg-elevated, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 16px;
    padding: 20px;
}

.docs-section {
    margin-bottom: 20px;
}

.docs-section:last-child {
    margin-bottom: 0;
}

.docs-section h4 {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-muted, #94a3b8);
    margin: 0 0 12px 0;
}

.doc-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f8fafc;
    border-radius: 12px;
    margin-bottom: 8px;
}

.doc-row.verified {
    background: #dcfce7;
}

.doc-row.pending {
    background: #fef3c7;
}

.doc-row.missing {
    background: #fee2e2;
}

.doc-status-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    color: #22c55e;
    flex-shrink: 0;
}

.doc-status-icon.missing {
    color: #dc2626;
}

.doc-name {
    flex: 1;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-primary, #0f172a);
}

.doc-status {
    font-size: 0.75rem;
    color: var(--text-muted, #94a3b8);
    font-weight: 500;
}

/* Timeline */
.timeline-mobile {
    background: var(--bg-elevated, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 16px;
    padding: 20px;
}

.timeline-step {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 16px 0;
    position: relative;
}

.timeline-step:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 20px;
    top: 48px;
    bottom: -16px;
    width: 2px;
    background: var(--border-color, #e2e8f0);
}

.timeline-step.completed::after {
    background: #22c55e;
}

.step-marker {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f1f5f9;
    border: 2px solid var(--border-color, #e2e8f0);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted, #94a3b8);
    flex-shrink: 0;
    z-index: 1;
}

.timeline-step.completed .step-marker {
    background: #22c55e;
    border-color: #22c55e;
    color: white;
}

.timeline-step.active .step-marker {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
    animation: pulse 2s infinite;
}

.step-number {
    font-size: 0.875rem;
    font-weight: 700;
}

.step-content {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding-top: 8px;
}

.step-label {
    font-weight: 600;
    color: var(--text-primary, #0f172a);
}

.timeline-step.completed .step-label {
    color: #166534;
}

.timeline-step.active .step-label {
    color: #1d4ed8;
}

.step-date {
    font-size: 0.75rem;
    color: var(--text-muted, #94a3b8);
}

@keyframes pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
    50% { box-shadow: 0 0 0 8px rgba(59, 130, 246, 0); }
}

/* Contact Section */
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

.btn-sm {
    padding: 8px 16px;
    font-size: 0.875rem;
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
    .action-icon-bg { background: #1e293b; }
    .doc-status-icon { background: #0f172a; }
    .contact-icon { background: #1e293b; }
    .step-marker { background: #0f172a; }
}

/* Responsive */
@media (min-width: 640px) {
    .request-show-mobile {
        padding: 24px;
        max-width: 600px;
        margin: 0 auto;
    }
}
</style>
@endsection