@extends('layouts.app')

@section('title', 'Détails de la demande - ' . $fundingRequest->request_number)
@section('header-title', 'Détails de la demande')

@section('header-action')
<a href="{{ route('client.requests.index') }}" class="btn btn-secondary">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Retour à la liste
</a>
@endsection

@section('content')

<div class="request-show">

    {{-- En-tête avec statut --}}
    <div class="request-header-card">
        <div class="request-header-main">
            <div class="request-identity">
                <span class="request-number">{{ $fundingRequest->request_number }}</span>
                <h1 class="request-title">{{ $fundingRequest->title }}</h1>
                <p class="request-meta">
                    Créée le {{ $fundingRequest->created_at->format('d/m/Y à H:i') }}
                </p>
            </div>

            <div class="request-status-badges">
                {{-- Badge statut paiement --}}
                @if($fundingRequest->payment_status === 'pending')
                    <span class="badge badge-warning">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Paiement en attente
                    </span>
                @elseif($fundingRequest->payment_status === 'paid')
                    <span class="badge badge-success">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Payé
                    </span>
                @elseif($fundingRequest->payment_status === 'failed')
                    <span class="badge badge-danger">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Paiement échoué
                    </span>
                @endif

                {{-- Badge statut demande --}}
                <span class="badge badge-{{ $fundingRequest->status }}">
                    {{ $fundingRequest->getStatusLabel() }}
                </span>
            </div>
        </div>

        {{-- Actions selon l'étape --}}
        <div class="request-actions-bar">

            {{-- ÉTAPE 1: DRAFT + PAIEMENT EN ATTENTE --}}
            @if($fundingRequest->status === 'draft' && $fundingRequest->payment_status === 'pending')
                <a href="{{ route('client.requests.payment', $fundingRequest) }}" class="btn btn-primary btn-lg">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a1 1 0 11-2 0 1 1 0 012 0z"/>
                    </svg>
                    Payer les frais d'inscription ({{ number_format($fees['registration'], 0, ',', ' ') }} FCFA)
                </a>

                <form action="{{ route('client.requests.destroy', $fundingRequest) }}" method="POST" class="inline-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette demande ?')">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Annuler la demande
                    </button>
                </form>

            {{-- ÉTAPE 2: PAYÉ + DOCUMENTS MANQUANTS --}}
            @elseif($fundingRequest->payment_status === 'paid' && $missingDocs->count() > 0)
                <a href="{{ route('client.documents.required', $fundingRequest) }}" class="btn btn-warning btn-lg">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Compléter les documents ({{ $missingDocs->count() }} manquant{{ $missingDocs->count() > 1 ? 's' : '' }})
                </a>

            {{-- ÉTAPE 3: SOUMISE - En attente de traitement --}}
            @elseif($fundingRequest->status === 'submitted')
                <div class="status-message info">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Votre demande est en attente d'examen par notre équipe.</span>
                </div>

            {{-- ÉTAPE 4: EN EXAMEN --}}
            @elseif(in_array($fundingRequest->status, ['under_review', 'pending_committee']))
                <div class="status-message warning">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span>Votre demande est en cours d'analyse.</span>
                </div>

            {{-- ÉTAPE 5: APPROUVÉE - Attente financement --}}
            @elseif($fundingRequest->status === 'approved')
                <div class="status-message success">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Félicitations ! Votre demande a été approuvée. Le financement est en cours de préparation.</span>
                </div>

            {{-- ÉTAPE 6: FINANCÉE --}}
            @elseif($fundingRequest->status === 'funded')
                <div class="status-message success">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Votre demande a été financée ! Les fonds ont été versés.</span>
                </div>

            {{-- ÉTAPE 7: REJETÉE --}}
            @elseif($fundingRequest->status === 'rejected')
                <div class="status-message danger">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Votre demande n'a pas été retenue. {{ $fundingRequest->rejection_reason ? 'Motif : ' . $fundingRequest->rejection_reason : '' }}</span>
                </div>
            @endif

        </div>
    </div>

    <div class="request-content-grid">

        {{-- Colonne gauche: Informations --}}
        <div class="request-info-column">

            {{-- Détails du financement --}}
            <div class="info-card">
                <h3 class="card-title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Détails du financement
                </h3>

                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Type de financement</span>
                        <span class="info-value">{{ $fundingRequest->typeFinancement->name }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Montant demandé</span>
                        <span class="info-value highlight">{{ number_format($fundingRequest->amount_requested, 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Durée</span>
                        <span class="info-value">{{ $fundingRequest->duration }} mois</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Description</span>
                        <span class="info-value">{{ $fundingRequest->description ?? 'Aucune description' }}</span>
                    </div>
                </div>
            </div>

            {{-- Frais et montants --}}
            <div class="info-card">
                <h3 class="card-title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    Récapitulatif des frais
                </h3>

                <div class="fees-breakdown">
                    <div class="fee-row">
                        <span>Frais d'inscription</span>
                        <span>{{ number_format($fees['registration'], 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div class="fee-row">
                        <span>Frais finaux (à la signature)</span>
                        <span>{{ number_format($fees['final'], 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div class="fee-row total">
                        <span>Total des frais</span>
                        <span>{{ number_format($fees['total_fees'], 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div class="fee-row net">
                        <span>Montant net à recevoir</span>
                        <span class="highlight">{{ number_format($fees['net_amount'], 0, ',', ' ') }} FCFA</span>
                    </div>
                </div>

                @if($fundingRequest->registration_fee_paid > 0)
                    <div class="payment-status-box success">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <div>
                            <strong>Frais d'inscription payés</strong>
                            <span>{{ number_format($fundingRequest->registration_fee_paid, 0, ',', ' ') }} FCFA le {{ $fundingRequest->paid_at?->format('d/m/Y') }}</span>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Documents --}}
            <div class="info-card">
                <h3 class="card-title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Documents
                    @if($missingDocs->count() > 0)
                        <span class="badge badge-warning">{{ $missingDocs->count() }} manquant{{ $missingDocs->count() > 1 ? 's' : '' }}</span>
                    @else
                        <span class="badge badge-success">Complets</span>
                    @endif
                </h3>

                @if($providedDocs->count() > 0)
                    <div class="documents-list">
                        <h4>Documents fournis</h4>
                        @foreach($providedDocs as $doc)
                            <div class="document-item {{ $doc->status }}">
                                <div class="doc-info">
                                    <span class="doc-name">{{ $doc->typeDoc->name }}</span>
                                    <span class="doc-status">{{ $doc->getStatusLabel() }}</span>
                                </div>
                                @if($doc->status === 'verified')
                                    <svg class="doc-icon success" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @elseif($doc->status === 'pending')
                                    <svg class="doc-icon warning" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($missingDocs->count() > 0)
                    <div class="documents-missing">
                        <h4>Documents requis manquants</h4>
                        <ul>
                            @foreach($missingDocs as $doc)
                                <li>{{ $doc->name }}</li>
                            @endforeach
                        </ul>

                        @if($fundingRequest->payment_status === 'paid')
                            <a href="{{ route('client.documents.required', $fundingRequest) }}" class="btn btn-warning btn-sm">
                                Compléter maintenant
                            </a>
                        @endif
                    </div>
                @endif
            </div>

        </div>

        {{-- Colonne droite: Timeline --}}
        <div class="request-timeline-column">
            <div class="timeline-card">
                <h3 class="card-title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Suivi de la demande
                </h3>

                <div class="timeline">
                    @foreach($timeline as $step)
                        <div class="timeline-item {{ $step['completed'] ? 'completed' : '' }} {{ $step['active'] ? 'active' : '' }}">
                            <div class="timeline-marker">
                                @if($step['completed'])
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @else
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                        @if($step['icon'] === 'plus')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        @elseif($step['icon'] === 'credit-card')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        @elseif($step['icon'] === 'send')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                        @elseif($step['icon'] === 'search')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        @elseif($step['icon'] === 'users')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                        @elseif($step['icon'] === 'gavel')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                        @elseif($step['icon'] === 'money-bill')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a1 1 0 11-2 0 1 1 0 012 0z"/>
                                        @endif
                                    </svg>
                                @endif
                            </div>
                            <div class="timeline-content">
                                <span class="timeline-label">{{ $step['label'] }}</span>
                                @if($step['date'])
                                    <span class="timeline-date">{{ $step['date']->format('d/m/Y') }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Informations de contact --}}
            <div class="info-card contact-card">
                <h3 class="card-title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Besoin d'aide ?
                </h3>
                <p>Contactez notre équipe pour toute question concernant votre demande.</p>
                <a href="mailto:support@bhdm.com" class="btn btn-outline-primary btn-sm">
                    Contacter le support
                </a>
            </div>

        </div>

    </div>

</div>

@endsection

@section('styles')
<style>
    .request-show {
        max-width: 1200px;
        margin: 0 auto;
    }

    /* Header Card */
    .request-header-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .request-header-main {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .request-identity {
        flex: 1;
    }

    .request-number {
        display: inline-block;
        font-family: monospace;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--primary);
        background: #eff6ff;
        padding: 0.35rem 0.75rem;
        border-radius: var(--radius);
        margin-bottom: 0.5rem;
    }

    .request-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text);
        margin: 0 0 0.5rem;
    }

    .request-meta {
        color: var(--text-muted);
        font-size: 0.9rem;
        margin: 0;
    }

    .request-status-badges {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    /* Actions Bar */
    .request-actions-bar {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
        padding-top: 1rem;
        border-top: 1px solid var(--border);
    }

    .status-message {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem 1.25rem;
        border-radius: var(--radius);
        font-weight: 500;
    }

    .status-message.info {
        background: #dbeafe;
        color: #1e40af;
    }

    .status-message.warning {
        background: #fef3c7;
        color: #92400e;
    }

    .status-message.success {
        background: #dcfce7;
        color: #166534;
    }

    .status-message.danger {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Content Grid */
    .request-content-grid {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 1.5rem;
    }

    @media (max-width: 1024px) {
        .request-content-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Info Cards */
    .info-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .card-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text);
        margin: 0 0 1.25rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--border);
    }

    .card-title .badge {
        margin-left: auto;
        font-size: 0.75rem;
    }

    /* Info Grid */
    .info-grid {
        display: grid;
        gap: 1rem;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .info-label {
        font-size: 0.8rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }

    .info-value {
        font-size: 1rem;
        color: var(--text);
        font-weight: 500;
    }

    .info-value.highlight {
        color: var(--primary);
        font-size: 1.25rem;
        font-weight: 700;
    }

    /* Fees Breakdown */
    .fees-breakdown {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .fee-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        color: var(--text);
    }

    .fee-row.total {
        border-top: 1px dashed var(--border);
        padding-top: 0.75rem;
        font-weight: 600;
    }

    .fee-row.net {
        background: #f0fdf4;
        margin: 0 -1.5rem;
        padding: 1rem 1.5rem;
        border-top: 1px solid #bbf7d0;
        border-bottom: 1px solid #bbf7d0;
    }

    .fee-row.net .highlight {
        color: #15803d;
        font-size: 1.1rem;
        font-weight: 700;
    }

    .payment-status-box {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-top: 1rem;
        padding: 1rem;
        border-radius: var(--radius);
    }

    .payment-status-box.success {
        background: #dcfce7;
        color: #166534;
    }

    .payment-status-box div {
        display: flex;
        flex-direction: column;
    }

    .payment-status-box strong {
        font-weight: 600;
    }

    .payment-status-box span {
        font-size: 0.85rem;
        opacity: 0.8;
    }

    /* Documents */
    .documents-list, .documents-missing {
        margin-bottom: 1rem;
    }

    .documents-list h4, .documents-missing h4 {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-muted);
        margin: 0 0 0.75rem;
        text-transform: uppercase;
    }

    .document-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem;
        background: #f8fafc;
        border-radius: var(--radius);
        margin-bottom: 0.5rem;
    }

    .document-item.verified {
        background: #dcfce7;
    }

    .document-item.pending {
        background: #fef3c7;
    }

    .doc-info {
        display: flex;
        flex-direction: column;
    }

    .doc-name {
        font-weight: 500;
        color: var(--text);
    }

    .doc-status {
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    .doc-icon.success {
        color: #22c55e;
    }

    .doc-icon.warning {
        color: #f59e0b;
    }

    .documents-missing ul {
        list-style: none;
        padding: 0;
        margin: 0 0 1rem;
    }

    .documents-missing li {
        padding: 0.5rem 0;
        color: #92400e;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .documents-missing li::before {
        content: '•';
        color: #f59e0b;
        font-weight: bold;
    }

    /* Timeline */
    .timeline-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        position: sticky;
        top: 1rem;
    }

    .timeline {
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    .timeline-item {
        display: flex;
        gap: 1rem;
        padding: 1rem 0;
        position: relative;
    }

    .timeline-item:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 20px;
        top: 48px;
        bottom: -16px;
        width: 2px;
        background: var(--border);
    }

    .timeline-item.completed::after {
        background: #22c55e;
    }

    .timeline-marker {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #f1f5f9;
        border: 2px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
        flex-shrink: 0;
        z-index: 1;
    }

    .timeline-item.completed .timeline-marker {
        background: #22c55e;
        border-color: #22c55e;
        color: white;
    }

    .timeline-item.active .timeline-marker {
        background: #3b82f6;
        border-color: #3b82f6;
        color: white;
        animation: pulse 2s infinite;
    }

    .timeline-content {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .timeline-label {
        font-weight: 600;
        color: var(--text);
    }

    .timeline-item.completed .timeline-label {
        color: #166534;
    }

    .timeline-item.active .timeline-label {
        color: #1d4ed8;
    }

    .timeline-date {
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
        50% { box-shadow: 0 0 0 8px rgba(59, 130, 246, 0); }
    }

    /* Contact Card */
    .contact-card {
        margin-top: 1.5rem;
    }

    .contact-card p {
        color: var(--text-muted);
        margin-bottom: 1rem;
    }

    /* Badges */
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
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

    .badge-draft {
        background: #f3f4f6;
        color: #6b7280;
    }

    .badge-submitted {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-under_review, .badge-pending_committee {
        background: #ede9fe;
        color: #5b21b6;
    }

    .badge-approved {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-funded {
        background: #a7f3d0;
        color: #064e3b;
    }

    .badge-rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Buttons */
    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
    }

    .inline-form {
        display: inline;
    }

    /* Responsive */
    @media (max-width: 640px) {
        .request-header-main {
            flex-direction: column;
        }

        .request-actions-bar {
            flex-direction: column;
            align-items: stretch;
        }

        .request-actions-bar .btn {
            justify-content: center;
        }
    }
</style>
@endsection
