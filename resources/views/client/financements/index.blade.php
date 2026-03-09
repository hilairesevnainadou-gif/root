@extends('layouts.app')

@section('title', 'Nos Offres de Financement')
@section('header-title', 'Offres')

@section('content')

<div class="financing-mobile">

    {{-- Header avec solde et accès rapide --}}
    <div class="financing-header-section">
        <div class="financing-header-content">
            <div class="financing-icon-bg">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="28" height="28">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="financing-header-text">
                <h1 class="financing-title">Nos offres</h1>
                <p class="financing-subtitle">{{ $financements->count() }} solution{{ $financements->count() > 1 ? 's' : '' }} disponible{{ $financements->count() > 1 ? 's' : '' }}</p>
            </div>
        </div>
    </div>

    {{-- Barre de recherche --}}
    @if($financements->count() > 3)
    <div class="search-container">
        <div class="search-wrapper">
            <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" id="financing-search" class="search-input" placeholder="Rechercher une offre...">
            <button type="button" id="search-clear" class="search-clear" style="display:none;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
    @endif

    {{-- Liste des offres --}}
    @if($financements->isEmpty())
        <div class="empty-state-card">
            <div class="empty-icon-wrapper">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="48" height="48">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="empty-title">Aucune offre disponible</h3>
            <p class="empty-description">De nouvelles solutions de financement arrivent très bientôt.</p>
        </div>
    @else
        <div class="offers-list" id="offers-list">
            @foreach($financements as $financement)
            <a href="{{ route('client.financements.show', $financement) }}"
               class="offer-card"
               data-name="{{ strtolower($financement->name) }}"
               data-transition="slide-left">

                <div class="offer-card-header">
                    <div class="offer-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="22" height="22">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="offer-meta">
                        <span class="offer-badge">
                            @if($financement->duration_months)
                                {{ $financement->duration_months }} mois
                            @else
                                Flexible
                            @endif
                        </span>
                    </div>
                </div>

                <div class="offer-card-body">
                    <h3 class="offer-name">{{ $financement->name }}</h3>
                    
                    @if($financement->description)
                        <p class="offer-description">{{ Str::limit($financement->description, 70) }}</p>
                    @endif

                    <div class="offer-details">
                        {{-- SR-Standard : Montant variable --}}
                        @if($financement->is_variable_amount && $financement->max_daily_amount)
                            <div class="offer-detail-item highlight">
                                <span class="detail-label">Jusqu'à</span>
                                <span class="detail-value">{{ number_format($financement->max_daily_amount, 0, ',', ' ') }} FCFA/jour</span>
                            </div>
                            <div class="offer-detail-item">
                                <span class="detail-label">Type</span>
                                <span class="detail-value">Montant libre</span>
                            </div>

                        {{-- SF1, SF2, SF3 : Gains fixes --}}
                        @elseif($financement->daily_gain && $financement->amount)
                            <div class="offer-detail-item highlight">
                                <span class="detail-label">Gain journalier</span>
                                <span class="detail-value">{{ number_format($financement->daily_gain, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="offer-detail-item">
                                <span class="detail-label">Investissement</span>
                                <span class="detail-value">{{ number_format($financement->amount, 0, ',', ' ') }} FCFA</span>
                            </div>
                        @endif

                        @if($financement->registration_fee)
                            <div class="offer-detail-item fee">
                                <span class="detail-label">Frais d'adhésion</span>
                                <span class="detail-value">{{ number_format($financement->registration_fee, 0, ',', ' ') }} FCFA</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="offer-card-footer">
                    <span class="offer-action">
                        Voir les détails
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                </div>
            </a>
            @endforeach
        </div>

        {{-- État vide recherche --}}
        <div id="no-search-results" class="empty-state-card" style="display:none;">
            <div class="empty-icon-wrapper">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="48" height="48">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <h3 class="empty-title">Aucun résultat</h3>
            <p class="empty-description">Essayez avec un autre terme de recherche.</p>
        </div>
    @endif

</div>

@endsection

@section('styles')
<style>
/* ============================================
   FINANCING/OFFERS PAGE - Mobile First
   ============================================ */

.financing-mobile {
    padding: 16px;
    padding-bottom: 100px;
    max-width: 100%;
    overflow-x: hidden;
    background-color: var(--bg-primary, #ffffff);
    min-height: 100vh;
}

/* Header Section */
.financing-header-section {
    margin-bottom: 20px;
}

.financing-header-content {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 4px;
}

.financing-icon-bg {
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

.financing-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary, #0f172a);
    margin: 0 0 4px 0;
    line-height: 1.2;
}

.financing-subtitle {
    font-size: 0.875rem;
    color: var(--text-tertiary, #64748b);
    margin: 0;
}

/* Search */
.search-container {
    margin-bottom: 20px;
}

.search-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.search-icon {
    position: absolute;
    left: 14px;
    color: var(--text-muted, #94a3b8);
    pointer-events: none;
    z-index: 2;
}

.search-input {
    width: 100%;
    padding: 14px 42px 14px 42px;
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 12px;
    background: var(--bg-elevated, #ffffff);
    color: var(--text-primary, #0f172a);
    font-size: 0.9375rem;
    outline: none;
    transition: all 0.2s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.search-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.search-clear {
    position: absolute;
    right: 12px;
    background: var(--bg-tertiary, #f1f5f9);
    border: none;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted, #94a3b8);
    cursor: pointer;
    transition: all 0.2s;
    z-index: 2;
}

.search-clear:hover {
    background: var(--border-color, #e2e8f0);
    color: var(--text-secondary, #475569);
}

/* Offers List */
.offers-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

/* Offer Card */
.offer-card {
    display: flex;
    flex-direction: column;
    background: var(--bg-elevated, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 16px;
    padding: 16px;
    text-decoration: none;
    color: inherit;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.offer-card:active {
    transform: scale(0.98);
    box-shadow: 0 1px 4px rgba(0,0,0,0.02);
}

.offer-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}

.offer-icon {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #dbeafe, #eff6ff);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #1e40af;
    flex-shrink: 0;
}

.offer-badge {
    font-size: 0.75rem;
    font-weight: 600;
    color: #059669;
    background: #d1fae5;
    padding: 4px 10px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.offer-card-body {
    flex: 1;
    margin-bottom: 14px;
}

.offer-name {
    font-size: 1.0625rem;
    font-weight: 700;
    color: var(--text-primary, #0f172a);
    margin: 0 0 6px 0;
    line-height: 1.3;
}

.offer-description {
    font-size: 0.8125rem;
    color: var(--text-secondary, #475569);
    margin: 0 0 12px 0;
    line-height: 1.5;
}

/* Offer Details Grid */
.offer-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.offer-detail-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
    padding: 10px 12px;
    background: var(--bg-secondary, #f8fafc);
    border-radius: 10px;
    border: 1px solid var(--border-light, #f1f5f9);
}

.offer-detail-item.highlight {
    background: linear-gradient(135deg, #dbeafe, #eff6ff);
    border-color: #bfdbfe;
}

.offer-detail-item.fee {
    grid-column: 1 / -1;
    background: #fef3c7;
    border-color: #fde68a;
}

.detail-label {
    font-size: 0.6875rem;
    color: var(--text-muted, #94a3b8);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 500;
}

.detail-value {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--text-primary, #0f172a);
}

.offer-detail-item.highlight .detail-value {
    color: #1e40af;
}

.offer-detail-item.fee .detail-value {
    color: #b45309;
}

/* Card Footer */
.offer-card-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-top: 12px;
    border-top: 1px solid var(--border-light, #f1f5f9);
}

.offer-action {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.875rem;
    font-weight: 600;
    color: #3b82f6;
    transition: all 0.2s;
}

.offer-card:hover .offer-action {
    color: #1e40af;
    gap: 10px;
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
    border-radius: 20px;
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
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--text-primary, #0f172a);
    margin: 0 0 8px 0;
}

.empty-description {
    font-size: 0.875rem;
    color: var(--text-secondary, #475569);
    margin: 0;
    max-width: 280px;
    line-height: 1.5;
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
    :root {
        --bg-primary: #0f172a;
        --bg-secondary: #1e293b;
        --bg-elevated: #1e293b;
        --bg-tertiary: #334155;
        --text-primary: #f8fafc;
        --text-secondary: #e2e8f0;
        --text-muted: #64748b;
        --border-color: #334155;
        --border-light: #1e293b;
    }

    .offer-icon {
        background: linear-gradient(135deg, #1e3a8a, #172554);
        color: #60a5fa;
    }

    .offer-detail-item.highlight {
        background: linear-gradient(135deg, #1e3a8a, #172554);
        border-color: #3b82f6;
    }

    .offer-detail-item.highlight .detail-value {
        color: #60a5fa;
    }

    .empty-state-card {
        background: #1e293b;
        border-color: #334155;
    }

    .empty-icon-wrapper {
        background: #0f172a;
        color: #64748b;
    }
}

/* Responsive */
@media (min-width: 640px) {
    .financing-mobile {
        padding: 24px;
        max-width: 600px;
        margin: 0 auto;
    }

    .offers-list {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }

    .offer-card {
        height: 100%;
    }
}
</style>
@endsection

@section('scripts')
<script>
(function () {
    const input    = document.getElementById('financing-search');
    const clear    = document.getElementById('search-clear');
    const list     = document.getElementById('offers-list');
    const noResult = document.getElementById('no-search-results');
    
    if (!input || !list) return;

    function filter(query) {
        const q = query.trim().toLowerCase();
        let visible = 0;
        
        list.querySelectorAll('.offer-card').forEach(item => {
            const match = item.dataset.name.includes(q);
            item.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        
        if (noResult) noResult.style.display = visible === 0 && q.length > 0 ? 'block' : 'none';
        if (clear) clear.style.display = q.length > 0 ? 'flex' : 'none';
    }

    input.addEventListener('input', () => filter(input.value));
    
    if (clear) {
        clear.addEventListener('click', () => { 
            input.value = ''; 
            filter(''); 
            input.focus(); 
        });
    }
})();
</script>
@endsection