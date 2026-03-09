@extends('layouts.app')

@section('title', $typeFinancement->name)
@section('header-title', 'Détails de l\'offre')

@section('content')

<div class="financing-show-mobile">

    {{-- Header avec retour --}}
    <div class="show-header-nav">
        <a href="{{ route('client.financements.index') }}" class="back-link" data-transition="slide-right">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <span>Retour aux offres</span>
        </a>
    </div>

    {{-- Hero Card --}}
    <div class="show-hero-card">
        <div class="show-hero-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="32" height="32">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="show-hero-content">
            <span class="show-eyebrow">Offre de financement</span>
            <h1 class="show-title">{{ $typeFinancement->name }}</h1>
            @if($typeFinancement->duration_months)
                <span class="show-duration-badge">{{ $typeFinancement->duration_months }} mois</span>
            @endif
        </div>
    </div>

    {{-- Alerte demande existante --}}
    @if($existingRequest)
    <div class="alert-card alert-warning">
        <div class="alert-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="22" height="22">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div class="alert-content">
            <h4 class="alert-title">Demande en cours</h4>
            <p class="alert-text">Vous avez déjà une demande active pour ce financement.</p>
            <a href="{{ route('client.requests.show', $existingRequest) }}" class="alert-action">
                Voir ma demande
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>
    @endif

    {{-- Description --}}
    @if($typeFinancement->description)
    <div class="info-card">
        <div class="card-header">
            <div class="header-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="header-title">Description</h2>
        </div>
        <p class="card-text">{{ $typeFinancement->description }}</p>
    </div>
    @endif

    {{-- Caractéristiques principales --}}
    <div class="info-card">
        <div class="card-header">
            <div class="header-icon header-icon-success">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <h2 class="header-title">Caractéristiques</h2>
        </div>

        <div class="features-grid">
            {{-- SR-Standard : Montant variable --}}
            @if($typeFinancement->is_variable_amount && $typeFinancement->max_daily_amount)
            <div class="feature-item highlight">
                <span class="feature-label">Montant quotidien max</span>
                <span class="feature-value feature-primary">
                    {{ number_format($typeFinancement->max_daily_amount, 0, ',', ' ') }} FCFA
                </span>
                <span class="feature-suffix">par jour</span>
            </div>
            <div class="feature-item">
                <span class="feature-label">Type</span>
                <span class="feature-badge badge-primary">Montant libre</span>
            </div>

            {{-- SF1, SF2, SF3 : Gains fixes --}}
            @elseif($typeFinancement->daily_gain && $typeFinancement->amount)
            <div class="feature-item highlight">
                <span class="feature-label">Gain journalier</span>
                <span class="feature-value feature-success">
                    {{ number_format($typeFinancement->daily_gain, 0, ',', ' ') }} FCFA
                </span>
                <span class="feature-suffix">par jour</span>
            </div>
            <div class="feature-item">
                <span class="feature-label">Gain total</span>
                <span class="feature-value">
                    {{ number_format($typeFinancement->amount, 0, ',', ' ') }} FCFA
                </span>
            </div>
            @endif

            @if($typeFinancement->duration_months)
            <div class="feature-item">
                <span class="feature-label">Durée du contrat</span>
                <span class="feature-value">{{ $typeFinancement->duration_months }} mois</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Frais --}}
    <div class="info-card">
        <div class="card-header">
            <div class="header-icon header-icon-warning">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <h2 class="header-title">Frais de dossier</h2>
        </div>

        <div class="fees-list">
            <div class="fee-row">
                <span class="fee-label">Frais d'inscription</span>
                <span class="fee-value">{{ number_format($typeFinancement->registration_fee, 0, ',', ' ') }} FCFA</span>
            </div>
            <div class="fee-row">
                <span class="fee-label">Frais finaux</span>
                <span class="fee-value">{{ number_format($typeFinancement->registration_final_fee, 0, ',', ' ') }} FCFA</span>
            </div>
            <div class="fee-row fee-total">
                <span class="fee-label">Total des frais</span>
                <span class="fee-value fee-total-value">
                    {{ number_format($typeFinancement->registration_fee + $typeFinancement->registration_final_fee, 0, ',', ' ') }} FCFA
                </span>
            </div>
        </div>
    </div>

    {{-- Documents requis --}}
    @if($requiredDocs && count($requiredDocs) > 0)
    <div class="info-card">
        <div class="card-header">
            <div class="header-icon header-icon-info">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h2 class="header-title">Documents requis</h2>
            <span class="doc-count">{{ count($requiredDocs) }}</span>
        </div>

        <ul class="docs-list">
            @foreach($requiredDocs as $doc)
            <li class="doc-item">
                <div class="doc-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="doc-name">{{ $doc->name ?? $doc }}</span>
                <span class="doc-badge">Requis</span>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- CTA Principal --}}
    <div class="cta-section">
        @if($existingRequest)
            <a href="{{ route('client.requests.show', $existingRequest) }}" class="btn btn-secondary btn-block btn-lg">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Voir ma demande en cours
            </a>
        @else
            <a href="{{ route('client.requests.create', ['typefinancement_id' => $typeFinancement->id]) }}" 
               class="btn btn-primary btn-block btn-lg"
               data-transition="slide-up">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                @if($typeFinancement->is_variable_amount)
                    Choisir mon montant
                @else
                    Postuler à cette offre
                @endif
            </a>
            <p class="cta-note">
                @if($typeFinancement->is_variable_amount)
                    Définissez votre montant (max {{ number_format($typeFinancement->max_daily_amount, 0, ',', ' ') }} FCFA/jour)
                @else
                    Processus 100% en ligne · Réponse sous 48h
                @endif
            </p>
        @endif
    </div>

    {{-- Suggestions --}}
    @if($suggestions->isNotEmpty())
    <div class="suggestions-section">
        <div class="suggestions-header">
            <h2 class="suggestions-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
                Autres offres
            </h2>
        </div>

        <div class="suggestions-list">
            @foreach($suggestions as $suggestion)
            <a href="{{ route('client.financements.show', $suggestion) }}" 
               class="suggestion-card"
               data-transition="slide-left">
                <div class="suggestion-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="suggestion-content">
                    <h3 class="suggestion-name">{{ $suggestion->name }}</h3>
                    <div class="suggestion-tags">
                        @if($suggestion->is_variable_amount && $suggestion->max_daily_amount)
                            <span class="tag tag-primary">Jusqu'à {{ number_format($suggestion->max_daily_amount, 0, ',', ' ') }} F/jour</span>
                        @elseif($suggestion->daily_gain)
                            <span class="tag tag-success">{{ number_format($suggestion->daily_gain, 0, ',', ' ') }} F/jour</span>
                        @endif
                        @if($suggestion->duration_months)
                            <span class="tag">{{ $suggestion->duration_months }} mois</span>
                        @endif
                    </div>
                </div>
                <div class="suggestion-arrow">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

</div>

@endsection

@section('styles')
<style>
/* ============================================
   FINANCING SHOW PAGE - Mobile First
   ============================================ */

.financing-show-mobile {
    padding: 16px;
    padding-bottom: 100px;
    max-width: 100%;
    overflow-x: hidden;
    background-color: var(--bg-primary, #ffffff);
    min-height: 100vh;
}

/* Header Navigation */
.show-header-nav {
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
.show-hero-card {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
    border-radius: 20px;
    padding: 24px;
    margin-bottom: 20px;
    color: white;
    position: relative;
    overflow: hidden;
    box-shadow: 0 20px 40px -10px rgba(30, 64, 175, 0.3);
}

.show-hero-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -30%;
    width: 250px;
    height: 250px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
    border-radius: 50%;
}

.show-hero-icon {
    width: 56px;
    height: 56px;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border: 1px solid rgba(255, 255, 255, 0.3);
    position: relative;
    z-index: 1;
}

.show-hero-content {
    flex: 1;
    position: relative;
    z-index: 1;
}

.show-eyebrow {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    opacity: 0.85;
    margin-bottom: 6px;
}

.show-title {
    font-size: 1.375rem;
    font-weight: 700;
    margin: 0 0 10px 0;
    line-height: 1.2;
}

.show-duration-badge {
    display: inline-block;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8125rem;
    font-weight: 600;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

/* Alert Card */
.alert-card {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    background: #fef3c7;
    border: 1px solid #fde68a;
    border-radius: 16px;
    padding: 18px;
    margin-bottom: 20px;
}

.alert-icon {
    width: 44px;
    height: 44px;
    background: #f59e0b;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
}

.alert-content {
    flex: 1;
}

.alert-title {
    font-size: 1rem;
    font-weight: 700;
    color: #92400e;
    margin: 0 0 4px 0;
}

.alert-text {
    font-size: 0.875rem;
    color: #b45309;
    margin: 0 0 12px 0;
    line-height: 1.5;
}

.alert-action {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #b45309;
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
    transition: gap 0.2s;
}

.alert-action:active {
    gap: 10px;
}

/* Info Cards */
.info-card {
    background: var(--bg-elevated, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 18px;
}

.header-icon {
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

.header-icon-success { background: #dcfce7; color: #16a34a; }
.header-icon-warning { background: #fef3c7; color: #f59e0b; }
.header-icon-info { background: #dbeafe; color: #2563eb; }

.header-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-primary, #0f172a);
    margin: 0;
    flex: 1;
}

.doc-count {
    background: #dbeafe;
    color: #1e40af;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
}

.card-text {
    font-size: 0.9375rem;
    color: var(--text-secondary, #475569);
    line-height: 1.7;
    margin: 0;
}

/* Features Grid */
.features-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.feature-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 16px;
    background: var(--bg-secondary, #f8fafc);
    border-radius: 12px;
    border: 1px solid var(--border-light, #f1f5f9);
}

.feature-item.highlight {
    background: linear-gradient(135deg, #dbeafe, #eff6ff);
    border-color: #bfdbfe;
    grid-column: 1 / -1;
}

.feature-label {
    font-size: 0.75rem;
    color: var(--text-muted, #94a3b8);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 600;
}

.feature-value {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--text-primary, #0f172a);
}

.feature-primary { color: #1e40af; }
.feature-success { color: #16a34a; }

.feature-suffix {
    font-size: 0.8125rem;
    color: var(--text-muted, #94a3b8);
}

.feature-badge {
    align-self: flex-start;
}

/* Fees List */
.fees-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.fee-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 0;
    border-bottom: 1px solid var(--border-light, #f1f5f9);
}

.fee-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.fee-label {
    font-size: 0.9375rem;
    color: var(--text-secondary, #475569);
}

.fee-value {
    font-size: 0.9375rem;
    font-weight: 600;
    color: var(--text-primary, #0f172a);
}

.fee-total {
    background: #eff6ff;
    margin: 8px -20px -20px;
    padding: 18px 20px;
    border-radius: 0 0 16px 16px;
    border-top: 2px solid #bfdbfe;
}

.fee-total-value {
    color: #1e40af;
    font-size: 1.125rem;
}

/* Documents List */
.docs-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.doc-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    background: var(--bg-secondary, #f8fafc);
    border-radius: 12px;
    border: 1px solid var(--border-light, #f1f5f9);
}

.doc-icon {
    width: 36px;
    height: 36px;
    background: #dbeafe;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #2563eb;
    flex-shrink: 0;
}

.doc-name {
    flex: 1;
    font-size: 0.9375rem;
    font-weight: 500;
    color: var(--text-primary, #0f172a);
}

.doc-badge {
    font-size: 0.6875rem;
    font-weight: 600;
    color: #dc2626;
    background: #fee2e2;
    padding: 4px 10px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

/* CTA Section */
.cta-section {
    margin: 24px 0;
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

.btn-secondary {
    background: var(--bg-secondary, #f8fafc);
    color: var(--text-primary, #0f172a);
    border: 2px solid var(--border-color, #e2e8f0);
}

.btn-secondary:active {
    background: var(--border-light, #f1f5f9);
}

.cta-note {
    font-size: 0.8125rem;
    color: var(--text-muted, #94a3b8);
    text-align: center;
    margin-top: 14px;
    line-height: 1.5;
}

/* Badges */
.badge-primary {
    background: #dbeafe;
    color: #1e40af;
    border: 1px solid #3b82f6;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

/* Suggestions */
.suggestions-section {
    margin-top: 32px;
    padding-top: 24px;
    border-top: 2px dashed var(--border-color, #e2e8f0);
}

.suggestions-header {
    margin-bottom: 16px;
}

.suggestions-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.0625rem;
    font-weight: 700;
    color: var(--text-primary, #0f172a);
    margin: 0;
}

.suggestions-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.suggestion-card {
    display: flex;
    align-items: center;
    gap: 14px;
    background: var(--bg-elevated, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 14px;
    padding: 16px;
    text-decoration: none;
    color: inherit;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.suggestion-card:active {
    transform: scale(0.98);
    border-color: #3b82f6;
}

.suggestion-icon {
    width: 44px;
    height: 44px;
    background: #eff6ff;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3b82f6;
    flex-shrink: 0;
}

.suggestion-content {
    flex: 1;
    min-width: 0;
}

.suggestion-name {
    font-size: 0.9375rem;
    font-weight: 600;
    color: var(--text-primary, #0f172a);
    margin: 0 0 6px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.suggestion-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.tag {
    font-size: 0.6875rem;
    font-weight: 500;
    background: var(--bg-secondary, #f8fafc);
    color: var(--text-muted, #64748b);
    border: 1px solid var(--border-color, #e2e8f0);
    padding: 4px 10px;
    border-radius: 20px;
}

.tag-primary {
    background: #dbeafe;
    color: #1e40af;
    border-color: #3b82f6;
}

.tag-success {
    background: #dcfce7;
    color: #166534;
    border-color: #22c55e;
}

.suggestion-arrow {
    color: var(--text-muted, #94a3b8);
    flex-shrink: 0;
    transition: all 0.2s;
}

.suggestion-card:active .suggestion-arrow {
    color: #3b82f6;
    transform: translateX(4px);
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

    .show-hero-card {
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
    }

    .alert-card {
        background: #451a03;
        border-color: #78350f;
    }

    .alert-icon { background: #b45309; }
    .alert-title { color: #fbbf24; }
    .alert-text { color: #fde68a; }
    .alert-action { color: #fbbf24; }

    .header-icon { background: #1e3a8a; color: #60a5fa; }
    .header-icon-success { background: #14532d; color: #4ade80; }
    .header-icon-warning { background: #451a03; color: #fbbf24; }
    .header-icon-info { background: #1e3a8a; color: #60a5fa; }

    .feature-item { background: #0f172a; border-color: #334155; }
    .feature-item.highlight { background: linear-gradient(135deg, #1e3a8a, #172554); border-color: #3b82f6; }

    .fee-total { background: #1e3a8a; border-top-color: #3b82f6; }
    .fee-total-value { color: #60a5fa; }

    .doc-item { background: #0f172a; }
    .doc-icon { background: #1e3a8a; color: #60a5fa; }

    .suggestion-icon { background: #1e3a8a; color: #60a5fa; }
    .tag-primary { background: #1e3a8a; color: #60a5fa; border-color: #3b82f6; }
    .tag-success { background: #14532d; color: #4ade80; border-color: #16a34a; }
}

/* Responsive */
@media (min-width: 640px) {
    .financing-show-mobile {
        padding: 24px;
        max-width: 600px;
        margin: 0 auto;
    }

    .features-grid {
        grid-template-columns: repeat(3, 1fr);
    }

    .feature-item.highlight {
        grid-column: span 2;
    }
}
</style>
@endsection