@extends('layouts.app')

@section('title', 'Mes demandes de financement')
@section('header-title', 'Mes demandes')

@section('header-action')
<a href="{{ route('client.dashboard') }}" class="btn-back" data-transition="slide-right">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
    </svg>
</a>
@endsection

@section('content')

@php
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
        'rejected' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'border' => '#ef4444'],
        'funded' => ['bg' => '#a7f3d0', 'text' => '#064e3b', 'border' => '#059669'],
        'completed' => ['bg' => '#f3f4f6', 'text' => '#374151', 'border' => '#9ca3af'],
        'cancelled' => ['bg' => '#f3f4f6', 'text' => '#6b7280', 'border' => '#d1d5db'],
    ];
@endphp

<div class="requests-mobile">

    {{-- Hero Section --}}
    <div class="requests-hero">
        <div class="hero-icon-bg">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="28" height="28">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <div class="hero-content">
            <h1 class="hero-title">Mes demandes</h1>
            <p class="hero-subtitle">Suivez vos demandes de financement</p>
        </div>
    </div>

    {{-- CTA Principal --}}
    <div class="requests-cta">
        <a href="{{ route('client.financements.index') }}" class="btn btn-primary btn-block btn-lg" data-transition="slide-up">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nouvelle demande
        </a>
    </div>

    {{-- Filtres scrollables --}}
    <div class="filters-scroll">
        <a href="{{ route('client.requests.index') }}" class="filter-pill {{ !request('status') && !request('payment_status') ? 'active' : '' }}">
            <span class="filter-count">{{ $stats['all'] ?? 0 }}</span>
            <span class="filter-label">Toutes</span>
        </a>

        <a href="{{ route('client.requests.index', ['payment_status' => 'pending', 'status' => 'draft']) }}" class="filter-pill filter-pill-warning {{ request('payment_status') === 'pending' ? 'active' : '' }}">
            <span class="filter-count">{{ $stats['pending_payment'] ?? 0 }}</span>
            <span class="filter-label">À payer</span>
        </a>

        <a href="{{ route('client.requests.index', ['status' => 'submitted']) }}" class="filter-pill filter-pill-info {{ request('status') === 'submitted' ? 'active' : '' }}">
            <span class="filter-count">{{ $stats['submitted'] ?? 0 }}</span>
            <span class="filter-label">Soumises</span>
        </a>

        <a href="{{ route('client.requests.index', ['status' => 'under_review']) }}" class="filter-pill filter-pill-purple {{ request('status') === 'under_review' ? 'active' : '' }}">
            <span class="filter-count">{{ $stats['under_review'] ?? 0 }}</span>
            <span class="filter-label">En examen</span>
        </a>

        <a href="{{ route('client.requests.index', ['status' => 'approved']) }}" class="filter-pill filter-pill-success {{ request('status') === 'approved' ? 'active' : '' }}">
            <span class="filter-count">{{ $stats['approved'] ?? 0 }}</span>
            <span class="filter-label">Approuvées</span>
        </a>

        <a href="{{ route('client.requests.index', ['status' => 'funded']) }}" class="filter-pill filter-pill-funded {{ request('status') === 'funded' ? 'active' : '' }}">
            <span class="filter-count">{{ $stats['funded'] ?? 0 }}</span>
            <span class="filter-label">Financées</span>
        </a>
    </div>

    {{-- Réinitialiser filtres --}}
    @if(request('status') || request('payment_status'))
    <div class="filter-reset">
        <a href="{{ route('client.requests.index') }}" class="reset-link">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Réinitialiser les filtres
        </a>
    </div>
    @endif

    {{-- Liste des demandes --}}
    @if($requests->count() > 0)
        <div class="requests-list-mobile">
            @foreach($requests as $request)
                @php
                    $colors = $statusColors[$request->status] ?? $statusColors['draft'];
                    $statusLabel = $statusLabels[$request->status] ?? $request->status;
                @endphp

                <div class="request-card-mobile" style="border-left-color: {{ $colors['border'] }}">
                    
                    {{-- Card Header --}}
                    <div class="request-card-header">
                        <div class="request-identity">
                            <div class="status-icon" style="background: {{ $colors['bg'] }}; color: {{ $colors['text'] }}">
                                @if($request->status === 'draft')
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                @elseif($request->status === 'submitted')
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @elseif($request->status === 'under_review' || $request->status === 'pending_committee')
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                @elseif($request->status === 'approved')
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @elseif($request->status === 'funded')
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @elseif($request->status === 'rejected')
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @else
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                @endif
                            </div>
                            <div class="request-info">
                                <span class="request-number">{{ $request->request_number }}</span>
                                <span class="request-date">{{ $request->created_at->format('d/m/Y') }}</span>
                            </div>
                        </div>
                        
                        <span class="status-badge" style="background: {{ $colors['bg'] }}; color: {{ $colors['text'] }}; border: 1px solid {{ $colors['border'] }}">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    {{-- Card Body --}}
                    <div class="request-card-body">
                        <h3 class="request-title">{{ $request->title }}</h3>
                        <p class="request-type">{{ $request->typeFinancement->name ?? 'Non spécifié' }}</p>
                        
                        <div class="request-amount-row">
                            <div class="amount-main">
                                <span class="amount-value">{{ number_format($request->amount_requested, 0, ',', ' ') }} FCFA</span>
                                <span class="amount-duration">{{ $request->duration }} mois</span>
                            </div>
                            
                            @if($request->payment_status === 'pending' && $request->status === 'draft')
                                <span class="payment-badge badge-warning">Paiement requis</span>
                            @elseif($request->payment_status === 'paid')
                                <span class="payment-badge badge-success">Payée</span>
                            @endif
                        </div>
                    </div>

                    {{-- Card Actions --}}
                    <div class="request-card-actions">
                        
                        {{-- Étape 1: Paiement en attente --}}
                        @if($request->payment_status === 'pending' && $request->status === 'draft')
                            <a href="{{ route('client.requests.payment', $request) }}" class="action-btn action-urgent">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Payer maintenant
                            </a>
                            <form action="{{ route('client.requests.destroy', $request) }}" method="POST" class="inline-form" onsubmit="return confirm('Annuler cette demande ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn action-cancel">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Annuler
                                </button>
                            </form>

                        {{-- Étape 2: Documents manquants --}}
                        @elseif($request->isPaid() && $request->pendingDocumentsCount() > 0)
                            <a href="{{ route('client.documents.required', $request) }}" class="action-btn action-warning">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Documents ({{ $request->pendingDocumentsCount() }})
                            </a>

                        {{-- Étape 3: Voir détails --}}
                        @else
                            <a href="{{ route('client.requests.show', $request) }}" class="action-btn action-view" data-transition="slide-left">
                                <span>Voir les détails</span>
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        @endif

                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($requests->hasPages())
        <div class="pagination-mobile">
            {{ $requests->links() }}
        </div>
        @endif

    @else
        {{-- État vide --}}
        <div class="empty-state-card">
            <div class="empty-icon-wrapper">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="56" height="56">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <h3 class="empty-title">Aucune demande</h3>
            <p class="empty-description">
                {{ request('status') || request('payment_status') ? 'Aucune demande ne correspond à ce filtre.' : 'Vous n\'avez pas encore de demande de financement.' }}
            </p>
            <a href="{{ route('client.financements.index') }}" class="btn btn-primary btn-lg" data-transition="slide-up">
                Découvrir les financements
            </a>
        </div>
    @endif

</div>

@endsection

@section('styles')
<style>
/* ============================================
   REQUESTS INDEX - Mobile First
   ============================================ */

.requests-mobile {
    padding: 16px;
    padding-bottom: 100px;
    max-width: 100%;
    overflow-x: hidden;
    background-color: var(--bg-primary, #ffffff);
    min-height: 100vh;
}

/* Hero Section */
.requests-hero {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 20px;
}

.hero-icon-bg {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #1e40af, #3b82f6);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
    box-shadow: 0 8px 20px rgba(30, 64, 175, 0.25);
}

.hero-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary, #0f172a);
    margin: 0 0 4px 0;
    line-height: 1.2;
}

.hero-subtitle {
    font-size: 0.875rem;
    color: var(--text-tertiary, #64748b);
    margin: 0;
}

/* CTA */
.requests-cta {
    margin-bottom: 20px;
}

.btn-block {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    padding: 16px 24px;
    border-radius: 14px;
    font-size: 1rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}

.btn-lg {
    min-height: 56px;
}

.btn-primary {
    background: linear-gradient(135deg, #1e40af, #3b82f6);
    color: white;
    box-shadow: 0 8px 20px rgba(30, 64, 175, 0.25);
}

.btn-primary:active {
    transform: translateY(1px);
    box-shadow: 0 4px 12px rgba(30, 64, 175, 0.2);
}

/* Filtres scrollables */
.filters-scroll {
    display: flex;
    gap: 10px;
    margin-bottom: 16px;
    overflow-x: auto;
    padding-bottom: 4px;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}

.filters-scroll::-webkit-scrollbar {
    display: none;
}

.filter-pill {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding: 12px 18px;
    background: var(--bg-elevated, #ffffff);
    border: 2px solid var(--border-color, #e2e8f0);
    border-radius: 16px;
    min-width: 80px;
    text-decoration: none;
    color: inherit;
    transition: all 0.2s;
    flex-shrink: 0;
}

.filter-pill.active {
    border-color: #3b82f6;
    background: #eff6ff;
}

.filter-count {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary, #0f172a);
    line-height: 1;
}

.filter-pill-warning .filter-count { color: #f59e0b; }
.filter-pill-warning.active { border-color: #f59e0b; background: #fef3c7; }

.filter-pill-info .filter-count { color: #3b82f6; }
.filter-pill-info.active { border-color: #3b82f6; background: #dbeafe; }

.filter-pill-purple .filter-count { color: #8b5cf6; }
.filter-pill-purple.active { border-color: #8b5cf6; background: #ede9fe; }

.filter-pill-success .filter-count { color: #10b981; }
.filter-pill-success.active { border-color: #10b981; background: #d1fae5; }

.filter-pill-funded .filter-count { color: #059669; }
.filter-pill-funded.active { border-color: #059669; background: #a7f3d0; }

.filter-label {
    font-size: 0.6875rem;
    color: var(--text-muted, #94a3b8);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 600;
    white-space: nowrap;
}

/* Reset link */
.filter-reset {
    margin-bottom: 16px;
    text-align: center;
}

.reset-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.875rem;
    color: #3b82f6;
    font-weight: 500;
    text-decoration: none;
    padding: 8px 16px;
    background: #eff6ff;
    border-radius: 20px;
    transition: all 0.2s;
}

.reset-link:active {
    background: #dbeafe;
}

/* Requests List */
.requests-list-mobile {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

/* Request Card */
.request-card-mobile {
    background: var(--bg-elevated, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-left-width: 4px;
    border-radius: 20px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    transition: all 0.2s;
}

.request-card-mobile:active {
    transform: scale(0.98);
}

/* Card Header */
.request-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
}

.request-identity {
    display: flex;
    align-items: center;
    gap: 12px;
}

.status-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.request-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.request-number {
    font-family: ui-monospace, monospace;
    font-size: 0.8125rem;
    font-weight: 600;
    color: #3b82f6;
}

.request-date {
    font-size: 0.75rem;
    color: var(--text-muted, #94a3b8);
}

.status-badge {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    padding: 6px 12px;
    border-radius: 20px;
    white-space: nowrap;
}

/* Card Body */
.request-card-body {
    padding: 16px 0;
    border-top: 1px solid var(--border-light, #f1f5f9);
    border-bottom: 1px solid var(--border-light, #f1f5f9);
    margin-bottom: 16px;
}

.request-title {
    font-size: 1.0625rem;
    font-weight: 700;
    color: var(--text-primary, #0f172a);
    margin: 0 0 6px 0;
    line-height: 1.3;
}

.request-type {
    font-size: 0.875rem;
    color: var(--text-secondary, #475569);
    margin: 0 0 14px 0;
}

.request-amount-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

.amount-main {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.amount-value {
    font-size: 1.125rem;
    font-weight: 700;
    color: #1e40af;
}

.amount-duration {
    font-size: 0.75rem;
    color: var(--text-muted, #94a3b8);
}

.payment-badge {
    font-size: 0.6875rem;
    font-weight: 600;
    padding: 6px 12px;
    border-radius: 20px;
    white-space: nowrap;
}

.badge-warning {
    background: #fef3c7;
    color: #92400e;
}

.badge-success {
    background: #dcfce7;
    color: #166534;
}

/* Card Actions */
.request-card-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 16px;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.action-urgent {
    grid-column: 1 / -1;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.25);
}

.action-urgent:active {
    transform: translateY(1px);
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.2);
}

.action-warning {
    grid-column: 1 / -1;
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
}

.action-view {
    grid-column: 1 / -1;
    background: var(--bg-secondary, #f8fafc);
    color: var(--text-primary, #0f172a);
    border: 1px solid var(--border-color, #e2e8f0);
    justify-content: space-between;
}

.action-view:active {
    background: var(--border-light, #f1f5f9);
}

.action-cancel {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.inline-form {
    display: contents;
}

/* Pagination */
.pagination-mobile {
    margin-top: 24px;
    display: flex;
    justify-content: center;
}

.pagination-mobile nav {
    display: flex;
    gap: 8px;
}

.pagination-mobile a,
.pagination-mobile span {
    padding: 10px 16px;
    border-radius: 10px;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    background: var(--bg-elevated, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    color: var(--text-secondary, #475569);
}

.pagination-mobile .active {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

/* Empty State */
.empty-state-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 48px 24px;
    background: var(--bg-secondary, #f8fafc);
    border: 2px dashed var(--border-color, #e2e8f0);
    border-radius: 24px;
    margin-top: 20px;
}

.empty-icon-wrapper {
    width: 80px;
    height: 80px;
    background: var(--bg-elevated, #ffffff);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted, #94a3b8);
    margin-bottom: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.empty-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary, #0f172a);
    margin: 0 0 8px 0;
}

.empty-description {
    font-size: 0.9375rem;
    color: var(--text-secondary, #475569);
    margin: 0 0 24px 0;
    max-width: 280px;
    line-height: 1.6;
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
    :root {
        --bg-primary: #0f172a;
        --bg-secondary: #1e293b;
        --bg-elevated: #1e293b;
        --text-primary: #f8fafc;
        --text-secondary: #e2e8f0;
        --text-tertiary: #cbd5e1;
        --text-muted: #64748b;
        --border-color: #334155;
        --border-light: #1e293b;
    }

    .hero-icon-bg {
        background: linear-gradient(135deg, #1e3a8a, #2563eb);
    }

    .amount-value { color: #60a5fa; }

    .filter-pill-warning.active { background: #451a03; border-color: #f59e0b; }
    .filter-pill-info.active { background: #1e3a8a; border-color: #3b82f6; }
    .filter-pill-purple.active { background: #4c1d95; border-color: #8b5cf6; }
    .filter-pill-success.active { background: #14532d; border-color: #10b981; }
    .filter-pill-funded.active { background: #064e3b; border-color: #059669; }

    .action-urgent { background: linear-gradient(135deg, #b45309, #d97706); }
    .action-warning { background: #451a03; color: #fbbf24; border-color: #78350f; }
    .action-view { background: #1e293b; border-color: #334155; color: #f8fafc; }
    .action-cancel { background: #450a0a; border-color: #7f1d1d; color: #f87171; }

    .badge-warning { background: #451a03; color: #fbbf24; }
    .badge-success { background: #14532d; color: #4ade80; }
}

/* Responsive */
@media (min-width: 640px) {
    .requests-mobile {
        padding: 24px;
        max-width: 600px;
        margin: 0 auto;
    }

    .filters-scroll {
        flex-wrap: wrap;
        justify-content: center;
    }

    .request-card-actions {
        grid-template-columns: repeat(2, 1fr);
    }

    .action-urgent,
    .action-warning,
    .action-view {
        grid-column: span 2;
    }
}
</style>
@endsection