@extends('layouts.app')

@section('title', $company->company_name . ' - BHDM')
@section('header-title', 'Détail Entreprise')

@section('content')
<div class="company-show-container">
    <!-- Carte principale -->
    <div class="company-hero">
        <div class="hero-main">
            <div class="hero-avatar" style="background: {{ $company->color }}">
                {{ $company->initials }}
            </div>
            <div class="hero-info">
                <h2>{{ $company->company_name }}</h2>
                <div class="hero-meta">
                    <span class="badge badge-type">{{ $company->company_type_label }}</span>
                    <span class="separator">•</span>
                    <span>{{ $company->sector_label }}</span>
                </div>
            </div>
        </div>
        <div class="hero-actions">
            <a href="{{ route('client.profile.companies.edit', $company) }}" class="btn btn-primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Modifier
            </a>
        </div>
    </div>

    <!-- Informations détaillées -->
    <div class="details-grid">
        <!-- Colonne gauche -->
        <div class="details-column">
            <!-- Identité -->
            <div class="detail-card">
                <h3 class="card-title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3 3 0 01-3-3V6"/>
                    </svg>
                    Identité légale
                </h3>
                <div class="detail-list">
                    <div class="detail-item">
                        <span class="detail-label">Type de structure</span>
                        <span class="detail-value">{{ $company->company_type_label }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Secteur d'activité</span>
                        <span class="detail-value">{{ $company->sector_label }}</span>
                    </div>
                    @if($company->registration_number)
                    <div class="detail-item">
                        <span class="detail-label">Numéro RCCM</span>
                        <span class="detail-value">{{ $company->registration_number }}</span>
                    </div>
                    @endif
                    @if($company->tax_id)
                    <div class="detail-item">
                        <span class="detail-label">Numéro IFU</span>
                        <span class="detail-value">{{ $company->tax_id }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Taille -->
            <div class="detail-card">
                <h3 class="card-title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Structure et effectif
                </h3>
                <div class="detail-list">
                    @if($company->job_title)
                    <div class="detail-item">
                        <span class="detail-label">Votre fonction</span>
                        <span class="detail-value">{{ $company->job_title }}</span>
                    </div>
                    @endif
                    <div class="detail-item">
                        <span class="detail-label">Nombre d'employés</span>
                        <span class="detail-value">{{ $company->employees_count ?? 0 }}</span>
                    </div>
                    @if($company->annual_turnover)
                    <div class="detail-item">
                        <span class="detail-label">Chiffre d'affaires</span>
                        <span class="detail-value highlight">{{ number_format($company->annual_turnover, 0, ',', ' ') }} FCFA</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Colonne droite -->
        <div class="details-column">
            <!-- Coordonnées -->
            <div class="detail-card">
                <h3 class="card-title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Coordonnées
                </h3>
                <div class="detail-list">
                    @if($company->address)
                    <div class="detail-item block">
                        <span class="detail-label">Adresse</span>
                        <span class="detail-value">{{ $company->address }}</span>
                    </div>
                    @endif
                    @if($company->city)
                    <div class="detail-item">
                        <span class="detail-label">Ville</span>
                        <span class="detail-value">{{ $company->city }}</span>
                    </div>
                    @endif
                    @if($company->company_phone)
                    <div class="detail-item">
                        <span class="detail-label">Téléphone</span>
                        <a href="tel:{{ $company->company_phone }}" class="detail-value link">{{ $company->company_phone }}</a>
                    </div>
                    @endif
                    @if($company->company_email)
                    <div class="detail-item">
                        <span class="detail-label">Email</span>
                        <a href="mailto:{{ $company->company_email }}" class="detail-value link">{{ $company->company_email }}</a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Description -->
            @if($company->description)
            <div class="detail-card">
                <h3 class="card-title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                    </svg>
                    Description de l'activité
                </h3>
                <p class="description-text">{{ $company->description }}</p>
            </div>
            @endif

            <!-- Statut -->
            <div class="detail-card status-card">
                <h3 class="card-title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Statut
                </h3>
                <div class="status-list">
                    <div class="status-item {{ $company->is_active ? 'active' : 'inactive' }}">
                        <span class="status-dot"></span>
                        <span>{{ $company->is_active ? 'Entreprise active' : 'Entreprise inactive' }}</span>
                    </div>
                    @if($company->is_primary)
                    <div class="status-item primary">
                        <span class="status-dot"></span>
                        <span>Entreprise principale du compte</span>
                    </div>
                    @endif
                    <div class="status-item">
                        <span class="status-date">Créée le {{ $company->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation retour -->
    <div class="back-navigation">
        <a href="{{ route('client.profile.companies.index') }}" class="btn-back">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour à la liste des entreprises
        </a>
    </div>
</div>
@endsection

@section('styles')
<style>
/* Container principal - s'adapte au main-content de app.blade.php */
.company-show-container {
    padding: 1rem;
    max-width: 900px;
    margin: 0 auto;
}

/* Hero Card - carte principale entreprise */
.company-hero {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.hero-main {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.hero-avatar {
    width: 72px;
    height: 72px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.75rem;
    color: white;
    flex-shrink: 0;
    text-transform: uppercase;
}

.hero-info h2 {
    margin: 0 0 0.5rem 0;
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
}

.hero-meta {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.875rem;
    color: #64748b;
    flex-wrap: wrap;
}

.badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-weight: 600;
}

.badge-type {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    color: #1d4ed8;
}

.separator {
    color: #d1d5db;
}

.hero-actions {
    display: flex;
    gap: 0.75rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    border-radius: 0.5rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    text-decoration: none;
    font-size: 0.875rem;
}

.btn-primary {
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
    color: white;
    box-shadow: 0 2px 4px rgba(30, 64, 175, 0.2);
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(30, 64, 175, 0.3);
}

/* Grille détails */
.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.details-column {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* Cartes détails */
.detail-card {
    background: white;
    border-radius: 1rem;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: all 0.2s;
}

.detail-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.card-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0 0 1rem 0;
    font-size: 1rem;
    font-weight: 600;
    color: #374151;
}

.card-title svg {
    color: #3b82f6;
}

.detail-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f3f4f6;
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-item.block {
    flex-direction: column;
    gap: 0.25rem;
}

.detail-label {
    font-size: 0.875rem;
    color: #6b7280;
    flex-shrink: 0;
}

.detail-value {
    font-size: 0.9375rem;
    font-weight: 500;
    color: #1e293b;
    text-align: right;
}

.detail-value.highlight {
    color: #059669;
    font-weight: 600;
}

.detail-value.link {
    color: #3b82f6;
    text-decoration: none;
}

.detail-value.link:hover {
    text-decoration: underline;
}

.description-text {
    margin: 0;
    font-size: 0.9375rem;
    line-height: 1.6;
    color: #4b5563;
}

/* Status Card */
.status-card {
    background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
    border: 1px solid #f3f4f6;
}

.status-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.status-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #4b5563;
}

.status-item.active {
    color: #059669;
}

.status-item.inactive {
    color: #dc2626;
}

.status-item.primary {
    color: #7c3aed;
    font-weight: 600;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}

.status-item.active .status-dot {
    background: #22c55e;
}

.status-item.inactive .status-dot {
    background: #ef4444;
}

.status-item.primary .status-dot {
    background: #8b5cf6;
}

.status-date {
    font-size: 0.8125rem;
    color: #9ca3af;
    font-style: italic;
    margin-left: 1rem;
}

/* Navigation retour */
.back-navigation {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #64748b;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    transition: color 0.2s;
    padding: 0.5rem;
}

.btn-back:hover {
    color: #1e293b;
}

/* Responsive - Mobile */
@media (max-width: 768px) {
    .company-show-container {
        padding: 0.75rem;
    }
    
    .company-hero {
        flex-direction: column;
        align-items: flex-start;
        padding: 1rem;
    }
    
    .hero-avatar {
        width: 56px;
        height: 56px;
        font-size: 1.25rem;
    }
    
    .hero-info h2 {
        font-size: 1.25rem;
    }
    
    .hero-actions {
        width: 100%;
    }
    
    .hero-actions .btn {
        flex: 1;
        justify-content: center;
    }
    
    .details-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .detail-item {
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .detail-value {
        text-align: left;
    }
    
    .btn-back {
        width: 100%;
        justify-content: center;
    }
}

/* Animation d'entrée */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.company-hero,
.detail-card {
    animation: fadeInUp 0.5s ease forwards;
}

.detail-card:nth-child(2) {
    animation-delay: 0.1s;
}
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation au scroll pour les cartes
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    document.querySelectorAll('.detail-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'all 0.5s ease';
        observer.observe(card);
    });
});
</script>
@endsection