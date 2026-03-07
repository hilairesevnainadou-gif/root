@extends('layouts.app')

@section('title', 'Mes demandes de financement')
@section('header-title', 'Mes demandes')

@section('header-action')
    <a href="{{ route('client.financements.index') }}" class="btn btn-primary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nouvelle demande
    </a>
@endsection

@section('content')

<div class="requests-index">

    {{-- Cartes statistiques --}}
    <div class="stats-grid">
        <div class="stat-card {{ request('status') ? '' : 'active' }}">
            <a href="{{ route('client.requests.index') }}" class="stat-link">
                <span class="stat-value">{{ $stats['all'] }}</span>
                <span class="stat-label">Total</span>
            </a>
        </div>
        
        <div class="stat-card {{ request('payment_status') === 'pending' ? 'active' : '' }}">
            <a href="{{ route('client.requests.index', ['payment_status' => 'pending']) }}" class="stat-link">
                <span class="stat-value">{{ $stats['pending_payment'] }}</span>
                <span class="stat-label">Paiement en attente</span>
            </a>
        </div>
        
        <div class="stat-card {{ request('status') === 'submitted' ? 'active' : '' }}">
            <a href="{{ route('client.requests.index', ['status' => 'submitted']) }}" class="stat-link">
                <span class="stat-value">{{ $stats['submitted'] }}</span>
                <span class="stat-label">Soumises</span>
            </a>
        </div>
        
        <div class="stat-card {{ request('status') === 'under_review' ? 'active' : '' }}">
            <a href="{{ route('client.requests.index', ['status' => 'under_review']) }}" class="stat-link">
                <span class="stat-value">{{ $stats['under_review'] }}</span>
                <span class="stat-label">En examen</span>
            </a>
        </div>
        
        <div class="stat-card {{ request('status') === 'approved' ? 'active' : '' }}">
            <a href="{{ route('client.requests.index', ['status' => 'approved']) }}" class="stat-link">
                <span class="stat-value">{{ $stats['approved'] }}</span>
                <span class="stat-label">Approuvées</span>
            </a>
        </div>
        
        <div class="stat-card {{ request('status') === 'funded' ? 'active' : '' }}">
            <a href="{{ route('client.requests.index', ['status' => 'funded']) }}" class="stat-link">
                <span class="stat-value">{{ $stats['funded'] }}</span>
                <span class="stat-label">Financées</span>
            </a>
        </div>
    </div>

    {{-- Liste des demandes --}}
    <div class="card">
        <div class="card-header">
            <h2 class="section-title">Liste des demandes</h2>
            
            @if(request('status') || request('payment_status'))
                <a href="{{ route('client.requests.index') }}" class="btn btn-sm btn-secondary">
                    Réinitialiser les filtres
                </a>
            @endif
        </div>

        @if($requests->count() > 0)
            <div class="requests-list">
                @foreach($requests as $request)
                <div class="request-item {{ $request->status }}">
                    
                    {{-- En-tête avec numéro et date --}}
                    <div class="request-header">
                        <div class="request-id">
                            <span class="request-number">{{ $request->request_number }}</span>
                            <span class="request-date">{{ $request->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        
                        <div class="request-badges">
                            {{-- Badge statut paiement --}}
                            @if($request->payment_status === 'pending' && $request->status === 'draft')
                                <span class="badge badge-warning">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Paiement requis
                                </span>
                            @elseif($request->payment_status === 'paid')
                                <span class="badge badge-success">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Payé
                                </span>
                            @elseif($request->payment_status === 'failed')
                                <span class="badge badge-danger">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Paiement échoué
                                </span>
                            @endif

                            {{-- Badge statut demande --}}
                            <span class="badge badge-{{ $request->status }}">
                                {{ $request->getStatusLabel() }}
                            </span>
                        </div>
                    </div>

                    {{-- Corps avec infos financement --}}
                    <div class="request-body">
                        <div class="request-info">
                            <h3 class="request-title">{{ $request->title }}</h3>
                            <p class="request-type">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $request->typeFinancement->name ?? 'N/A' }}
                            </p>
                        </div>

                        <div class="request-amounts">
                            <div class="amount-item">
                                <span class="amount-label">Montant demandé</span>
                                <span class="amount-value">{{ number_format($request->amount_requested, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="amount-item">
                                <span class="amount-label">Durée</span>
                                <span class="amount-value">{{ $request->duration }} mois</span>
                            </div>
                            @if($request->registration_fee_paid > 0)
                                <div class="amount-item">
                                    <span class="amount-label">Frais payés</span>
                                    <span class="amount-value text-success">{{ number_format($request->registration_fee_paid, 0, ',', ' ') }} FCFA</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="request-actions">
                        
                        {{-- Si paiement en attente --}}
                        @if($request->payment_status === 'pending' && $request->status === 'draft')
                            <a href="{{ route('client.requests.create', ['typefinancement_id' => $request->typefinancement_id]) }}" 
                               class="btn btn-primary btn-sm">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a1 1 0 11-2 0 1 1 0 012 0z"/>
                                </svg>
                                Compléter le paiement
                            </a>
                            
                            <form action="{{ route('client.requests.destroy', $request) }}" method="POST" class="inline-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" 
                                        onclick="return confirm('Annuler cette demande ?')">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Annuler
                                </button>
                            </form>

                        {{-- Si payé mais documents manquants --}}
                        @elseif($request->isPaid() && $request->pendingDocumentsCount() > 0)
                            <a href="{{ route('client.documents.required', $request) }}" class="btn btn-warning btn-sm">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                {{ $request->pendingDocumentsCount() }} document(s) manquant(s)
                            </a>

                        {{-- Voir détails --}}
                        @else
                            <a href="{{ route('client.requests.show', $request) }}" class="btn btn-secondary btn-sm">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Voir détails
                            </a>
                        @endif

                        {{-- Suivi public --}}
                        <a href="{{ route('funding.track', $request->request_number) }}" 
                           class="btn btn-link btn-sm" target="_blank">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            Lien de suivi
                        </a>
                    </div>

                    {{-- Barre de progression pour les étapes --}}
                    @if($request->status !== 'draft')
                        <div class="request-progress">
                            <div class="progress-steps">
                                @php
                                    $steps = [
                                        ['key' => 'submitted', 'label' => 'Soumise', 'icon' => 'send'],
                                        ['key' => 'under_review', 'label' => 'Examen', 'icon' => 'search'],
                                        ['key' => 'approved', 'label' => 'Approuvée', 'icon' => 'check'],
                                        ['key' => 'funded', 'label' => 'Financée', 'icon' => 'money'],
                                    ];
                                    $currentStepIndex = collect($steps)->search(fn($s) => $request->status === $s['key'] || 
                                        ($s['key'] === 'under_review' && in_array($request->status, ['under_review', 'pending_committee'])));
                                    if ($currentStepIndex === false) $currentStepIndex = -1;
                                @endphp

                                @foreach($steps as $index => $step)
                                    <div class="step {{ $index <= $currentStepIndex ? 'completed' : '' }} {{ $index === $currentStepIndex ? 'current' : '' }}">
                                        <div class="step-icon">
                                            @if($index < $currentStepIndex)
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            @else
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                                    @if($step['icon'] === 'send')
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                                    @elseif($step['icon'] === 'search')
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                                    @elseif($step['icon'] === 'check')
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    @elseif($step['icon'] === 'money')
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    @endif
                                                </svg>
                                            @endif
                                        </div>
                                        <span class="step-label">{{ $step['label'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="pagination-wrapper">
                {{ $requests->links() }}
            </div>

        @else
            {{-- État vide --}}
            <div class="empty-state">
                <div class="empty-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="64" height="64">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3>Aucune demande trouvée</h3>
                <p>Vous n'avez pas encore de demande de financement{{ request('status') ? ' avec ce statut' : '' }}.</p>
                <a href="{{ route('client.financements.index') }}" class="btn btn-primary">
                    Découvrir les financements
                </a>
            </div>
        @endif
    </div>

</div>

@endsection

@section('styles')
<style>
    .requests-index {
        max-width: 1200px;
        margin: 0 auto;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: var(--surface);
        border: 2px solid var(--border);
        border-radius: var(--radius);
        padding: 1.25rem;
        text-align: center;
        transition: all 0.2s;
    }

    .stat-card:hover, .stat-card.active {
        border-color: var(--primary);
        background: #eff6ff;
    }

    .stat-link {
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .stat-value {
        display: block;
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--primary);
        line-height: 1;
    }

    .stat-label {
        display: block;
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-top: 0.5rem;
    }

    /* Request Items */
    .requests-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .request-item {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 1.5rem;
        transition: all 0.2s;
    }

    .request-item:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .request-item.draft {
        border-left: 4px solid #f59e0b;
    }

    .request-item.submitted {
        border-left: 4px solid #3b82f6;
    }

    .request-item.under_review, .request-item.pending_committee {
        border-left: 4px solid #8b5cf6;
    }

    .request-item.approved {
        border-left: 4px solid #10b981;
    }

    .request-item.funded {
        border-left: 4px solid #059669;
    }

    .request-item.rejected {
        border-left: 4px solid #ef4444;
    }

    /* Header */
    .request-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .request-id {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .request-number {
        font-family: monospace;
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--primary);
        background: #eff6ff;
        padding: 0.25rem 0.5rem;
        border-radius: var(--radius-sm);
    }

    .request-date {
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    .request-badges {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

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

    /* Body */
    .request-body {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 1.5rem;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border);
    }

    @media (max-width: 640px) {
        .request-body {
            grid-template-columns: 1fr;
        }
    }

    .request-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text);
        margin: 0 0 0.5rem;
    }

    .request-type {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        color: var(--text-muted);
        margin: 0;
    }

    .request-amounts {
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .amount-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .amount-label {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .amount-value {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text);
    }

    .amount-value.text-success {
        color: #059669;
    }

    /* Actions */
    .request-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .inline-form {
        display: inline;
    }

    /* Progress */
    .request-progress {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px dashed var(--border);
    }

    .progress-steps {
        display: flex;
        justify-content: space-between;
        position: relative;
    }

    .progress-steps::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 10%;
        right: 10%;
        height: 2px;
        background: var(--border);
        z-index: 0;
    }

    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        z-index: 1;
        flex: 1;
    }

    .step-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--bg);
        border: 2px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
    }

    .step.completed .step-icon {
        background: #dcfce7;
        border-color: #22c55e;
        color: #166534;
    }

    .step.current .step-icon {
        background: #3b82f6;
        border-color: #3b82f6;
        color: white;
        animation: pulse 2s infinite;
    }

    .step-label {
        font-size: 0.75rem;
        color: var(--text-muted);
        text-align: center;
    }

    .step.completed .step-label,
    .step.current .step-label {
        color: var(--text);
        font-weight: 500;
    }

    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
        50% { box-shadow: 0 0 0 8px rgba(59, 130, 246, 0); }
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
    }

    .empty-icon {
        color: var(--text-muted);
        margin-bottom: 1rem;
    }

    .empty-state h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text);
        margin: 0 0 0.5rem;
    }

    .empty-state p {
        color: var(--text-muted);
        margin: 0 0 1.5rem;
    }

    /* Pagination */
    .pagination-wrapper {
        margin-top: 1.5rem;
        display: flex;
        justify-content: center;
    }
</style>
@endsection