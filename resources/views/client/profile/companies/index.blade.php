@extends('layouts.app')

@section('title', 'Mes Entreprises - BHDM')
@section('header-title', 'Mes Entreprises')

@section('content')

<div class="companies-mobile">

    {{-- Header Navigation --}}
    <div class="companies-header-nav">
        <a href="{{ route('client.profile') }}" class="back-link" data-transition="slide-right">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Retour au profil</span>
        </a>
    </div>

    {{-- Hero Section --}}
    <div class="companies-hero">
        <div class="hero-icon-bg">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="28" height="28">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
        <div class="hero-content">
            <h1 class="hero-title">Mes entreprises</h1>
            <p class="hero-subtitle">Gérez vos structures professionnelles</p>
        </div>
    </div>

    {{-- CTA Principal --}}
    <div class="companies-cta">
        <a href="{{ route('client.profile.companies.create') }}" class="btn btn-primary btn-block btn-lg" data-transition="slide-up">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Ajouter une entreprise
        </a>
    </div>

    {{-- Stats rapides --}}
    @if($companies->count() > 0)
    <div class="stats-row">
        <div class="stat-pill">
            <span class="stat-number">{{ $companies->count() }}</span>
            <span class="stat-label">Total</span>
        </div>
        <div class="stat-pill stat-pill-primary">
            <span class="stat-number">{{ $companies->where('is_primary', true)->count() }}</span>
            <span class="stat-label">Principale</span>
        </div>
        <div class="stat-pill stat-pill-success">
            <span class="stat-number">{{ $companies->where('is_active', true)->count() }}</span>
            <span class="stat-label">Actives</span>
        </div>
    </div>
    @endif

    {{-- Liste des entreprises --}}
    @if($companies->count() > 0)
        <div class="companies-list">
            @foreach($companies as $company)
            <div class="company-card-mobile {{ $company->is_primary ? 'is-primary' : '' }} {{ !$company->is_active ? 'is-inactive' : '' }}">
                
                {{-- Badges --}}
                @if($company->is_primary)
                    <div class="company-badge badge-primary">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="12" height="12">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Principale
                    </div>
                @endif

                @if(!$company->is_active)
                    <div class="company-badge badge-inactive">Inactive</div>
                @endif

                {{-- Card Header --}}
                <div class="company-card-header">
                    <div class="company-avatar" style="background: {{ $company->color }}">
                        {{ $company->initials }}
                    </div>
                    <div class="company-info">
                        <h3 class="company-name">{{ $company->company_name }}</h3>
                        <div class="company-meta">
                            <span class="meta-type">{{ $company->company_type_label }}</span>
                            <span class="meta-dot">•</span>
                            <span class="meta-sector">{{ $company->sector_label }}</span>
                        </div>
                    </div>
                </div>

                {{-- Card Body --}}
                <div class="company-card-body">
                    <div class="details-grid">
                        @if($company->city)
                            <div class="detail-cell">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>
                                <span>{{ $company->city }}</span>
                            </div>
                        @endif
                        
                        @if($company->employees_count)
                            <div class="detail-cell">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span>{{ $company->employees_count }} employés</span>
                            </div>
                        @endif

                        @if($company->annual_turnover)
                            <div class="detail-cell detail-highlight">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>{{ number_format($company->annual_turnover, 0, ',', ' ') }} FCFA</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Card Actions --}}
                <div class="company-card-actions">
                    <a href="{{ route('client.profile.companies.show', $company) }}" class="action-btn action-view" data-transition="slide-left">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <span>Voir</span>
                    </a>

                    <a href="{{ route('client.profile.companies.edit', $company) }}" class="action-btn action-edit" data-transition="slide-left">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span>Modifier</span>
                    </a>

                    @if(!$company->is_primary)
                        <form action="{{ route('client.profile.companies.primary', $company) }}" method="POST" class="inline-form">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="action-btn action-primary">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                </svg>
                                <span>Principale</span>
                            </button>
                        </form>
                    @endif

                    @if($companies->count() > 1)
                        <form action="{{ route('client.profile.companies.destroy', $company) }}" method="POST" class="inline-form" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette entreprise ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-btn action-delete">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <span>Supprimer</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    @else
        {{-- État vide --}}
        <div class="empty-state-card">
            <div class="empty-icon-wrapper">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="56" height="56">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <h3 class="empty-title">Aucune entreprise</h3>
            <p class="empty-description">Créez votre première structure professionnelle pour compléter votre profil.</p>
            <a href="{{ route('client.profile.companies.create') }}" class="btn btn-primary btn-lg" data-transition="slide-up">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Ajouter une entreprise
            </a>
        </div>
    @endif

</div>

@endsection

@section('styles')
<style>
/* ============================================
   COMPANIES INDEX - Mobile First
   ============================================ */

.companies-mobile {
    padding: 16px;
    padding-bottom: 100px;
    max-width: 100%;
    overflow-x: hidden;
    background-color: var(--bg-primary, #ffffff);
    min-height: 100vh;
}

/* Header Navigation */
.companies-header-nav {
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

/* Hero Section */
.companies-hero {
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

.hero-content {
    flex: 1;
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

/* CTA Section */
.companies-cta {
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

/* Stats Row */
.stats-row {
    display: flex;
    gap: 10px;
    margin-bottom: 24px;
    overflow-x: auto;
    padding-bottom: 4px;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}

.stats-row::-webkit-scrollbar {
    display: none;
}

.stat-pill {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding: 14px 20px;
    background: var(--bg-elevated, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 16px;
    min-width: 80px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.stat-pill-primary {
    background: linear-gradient(135deg, #f0fdf4, #ffffff);
    border-color: #bbf7d0;
}

.stat-pill-success {
    background: linear-gradient(135deg, #eff6ff, #ffffff);
    border-color: #bfdbfe;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary, #0f172a);
    line-height: 1;
}

.stat-pill-primary .stat-number {
    color: #16a34a;
}

.stat-pill-success .stat-number {
    color: #2563eb;
}

.stat-label {
    font-size: 0.6875rem;
    color: var(--text-muted, #94a3b8);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 600;
}

/* Companies List */
.companies-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

/* Company Card */
.company-card-mobile {
    background: var(--bg-elevated, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 20px;
    padding: 20px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    transition: all 0.2s;
}

.company-card-mobile.is-primary {
    border: 2px solid #22c55e;
    background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
}

.company-card-mobile.is-inactive {
    opacity: 0.7;
}

/* Badges */
.company-badge {
    position: absolute;
    top: 0;
    right: 0;
    padding: 6px 12px;
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    display: flex;
    align-items: center;
    gap: 4px;
    border-radius: 0 0 0 12px;
}

.badge-primary {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: white;
}

.badge-inactive {
    background: #fee2e2;
    color: #dc2626;
}

/* Card Header */
.company-card-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 16px;
}

.company-avatar {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.125rem;
    color: white;
    flex-shrink: 0;
    text-transform: uppercase;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.company-info {
    flex: 1;
    min-width: 0;
}

.company-name {
    font-size: 1.0625rem;
    font-weight: 700;
    color: var(--text-primary, #0f172a);
    margin: 0 0 6px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.company-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.8125rem;
    color: var(--text-tertiary, #64748b);
    flex-wrap: wrap;
}

.meta-type {
    background: #dbeafe;
    color: #1e40af;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.6875rem;
    font-weight: 600;
}

.meta-dot {
    color: var(--text-muted, #94a3b8);
}

.meta-sector {
    color: #7c3aed;
    font-weight: 500;
}

/* Card Body */
.company-card-body {
    padding: 16px 0;
    border-top: 1px solid var(--border-light, #f1f5f9);
    border-bottom: 1px solid var(--border-light, #f1f5f9);
    margin-bottom: 16px;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.detail-cell {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.875rem;
    color: var(--text-secondary, #475569);
}

.detail-cell svg {
    color: var(--text-muted, #94a3b8);
    flex-shrink: 0;
}

.detail-highlight {
    color: #059669;
    font-weight: 600;
}

.detail-highlight svg {
    color: #22c55e;
}

/* Card Actions */
.company-card-actions {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}

.action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    background: var(--bg-secondary, #f8fafc);
    color: var(--text-secondary, #475569);
}

.action-btn span {
    display: none;
}

@media (min-width: 380px) {
    .action-btn span {
        display: inline;
    }
}

.action-view:hover {
    background: #dbeafe;
    color: #1e40af;
}

.action-edit:hover {
    background: #fef3c7;
    color: #d97706;
}

.action-primary {
    background: #f0fdf4;
    color: #16a34a;
}

.action-primary:hover {
    background: #dcfce7;
}

.action-delete {
    background: #fef2f2;
    color: #dc2626;
}

.action-delete:hover {
    background: #fee2e2;
}

.inline-form {
    display: contents;
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

    .stat-pill-primary {
        background: linear-gradient(135deg, #14532d, #166534);
        border-color: #22c55e;
    }

    .stat-pill-success {
        background: linear-gradient(135deg, #1e3a8a, #1e40af);
        border-color: #3b82f6;
    }

    .company-card-mobile.is-primary {
        background: linear-gradient(135deg, #14532d 0%, #166534 100%);
        border-color: #22c55e;
    }

    .meta-type {
        background: #1e3a8a;
        color: #60a5fa;
    }

    .action-view:hover { background: #1e3a8a; color: #60a5fa; }
    .action-edit:hover { background: #451a03; color: #fbbf24; }
    .action-primary { background: #14532d; color: #4ade80; }
    .action-delete { background: #450a0a; color: #f87171; }
}

/* Responsive */
@media (min-width: 640px) {
    .companies-mobile {
        padding: 24px;
        max-width: 600px;
        margin: 0 auto;
    }

    .company-card-actions {
        grid-template-columns: repeat(4, 1fr);
    }

    .details-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}
</style>
@endsection