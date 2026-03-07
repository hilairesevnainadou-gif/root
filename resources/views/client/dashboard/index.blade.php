@extends('layouts.app')

@section('title', 'Tableau de bord - BHDM')

@section('header-title', 'Mon Tableau de bord')

@section('content')
@php
    $hour = now()->hour;
    if ($hour >= 5 && $hour < 12) {
        $greeting = 'Bonjour';
    } elseif ($hour >= 12 && $hour < 14) {
        $greeting = 'Bon après-midi';
    } elseif ($hour >= 14 && $hour < 18) {
        $greeting = 'Bonne après-midi';
    } else {
        $greeting = 'Bonsoir';
    }
@endphp

<div class="dashboard-mobile">
    
    {{-- En-tête avec salutation --}}
    <div class="dashboard-header">
        <div class="user-greeting">
            <h1>{{ $greeting }}, <span class="user-name">{{ $user->first_name }}</span></h1>
            <p class="user-status">
                @if($stats['active_requests'] > 0)
                    <span class="status-badge pulse">{{ $stats['active_requests'] }}</span> 
                    demande{{ $stats['active_requests'] > 1 ? 's' : '' }} active{{ $stats['active_requests'] > 1 ? 's' : '' }}
                @else
                    Prêt à démarrer ?
                @endif
            </p>
        </div>
        <a href="{{ route('client.requests.create') }}" class="btn-quick-action" title="Nouvelle demande">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
        </a>
    </div>

    {{-- Carte Portefeuille Compacte (visible si pas dans le layout) --}}
    @if(!isset($financialSummary))
        <div class="wallet-mini">
            <div class="wallet-mini-content">
                <span class="wallet-mini-label">Solde disponible</span>
                <span class="wallet-mini-value">--</span>
            </div>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </div>
    @endif

    {{-- Actions Rapides --}}
    <div class="quick-actions">
        <a href="{{ route('client.wallet.show') }}" class="action-chip">
            <div class="action-icon action-blue">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <span>Portefeuille</span>
        </a>
        
        <a href="{{ route('client.requests.index') }}" class="action-chip">
            <div class="action-icon action-green">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <span>Mes demandes</span>
        </a>

        <a href="{{ route('client.profile') }}" class="action-chip">
            <div class="action-icon action-purple">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <span>Mon profil</span>
        </a>
    </div>

    {{-- Alertes Prioritaires --}}
    @if(!empty($alerts))
        <div class="section-priority">
            <h2 class="section-title">À traiter en priorité</h2>
            <div class="alerts-swipe">
                @foreach($alerts as $alert)
                    <div class="alert-card-mobile alert-{{ $alert['type'] }}">
                        <div class="alert-mobile-icon">
                            @switch($alert['icon'])
                                @case('document')
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    @break
                                @case('draft')
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    @break
                                @case('notification')
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    @break
                                @default
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @endswitch
                        </div>
                        <div class="alert-mobile-content">
                            <h4>{{ $alert['title'] }}</h4>
                            <p>{{ $alert['message'] }}</p>
                        </div>
                        <a href="{{ $alert['action_url'] }}" class="alert-mobile-action">
                            {{ $alert['action_text'] }}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Statistiques en grille --}}
    <div class="stats-section">
        <h2 class="section-title">Vue d'ensemble</h2>
        <div class="stats-grid-mobile">
            <div class="stat-tile">
                <div class="stat-tile-icon stat-blue">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div class="stat-tile-info">
                    <span class="stat-tile-value">{{ $stats['total_requests']['value'] }}</span>
                    <span class="stat-tile-label">Total demandes</span>
                </div>
                @if($stats['total_requests']['trend'] != 0)
                    <span class="stat-trend-mini {{ $stats['total_requests']['trend'] > 0 ? 'up' : 'down' }}">
                        {{ $stats['total_requests']['trend'] > 0 ? '+' : '' }}{{ $stats['total_requests']['trend'] }}%
                    </span>
                @endif
            </div>

            <div class="stat-tile">
                <div class="stat-tile-icon stat-green">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="stat-tile-info">
                    <span class="stat-tile-value">{{ $stats['total_funded']['formatted'] }}</span>
                    <span class="stat-tile-label">Financé</span>
                </div>
            </div>

            <div class="stat-tile">
                <div class="stat-tile-icon stat-purple">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="stat-tile-info">
                    <span class="stat-tile-value">{{ $stats['success_rate']['value'] }}%</span>
                    <span class="stat-tile-label">Taux réussite</span>
                </div>
                <span class="stat-badge {{ $stats['success_rate']['value'] >= 70 ? 'excellent' : ($stats['success_rate']['value'] >= 40 ? 'good' : 'low') }}">
                    {{ $stats['success_rate']['label'] }}
                </span>
            </div>

            <div class="stat-tile">
                <div class="stat-tile-icon stat-orange">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="stat-tile-info">
                    <span class="stat-tile-value">{{ $stats['active_requests'] }}</span>
                    <span class="stat-tile-label">En cours</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Demandes Actives --}}
    @if($activeRequests->count() > 0)
        <div class="requests-section">
            <div class="section-header-row">
                <h2 class="section-title">Demandes en cours</h2>
                <a href="{{ route('client.requests.index') }}" class="link-see-all">Voir tout</a>
            </div>
            
            <div class="requests-list-mobile">
                @foreach($activeRequests->take(3) as $request)
                    <div class="request-item-mobile">
                        <div class="request-item-header">
                            <div class="request-meta">
                                <span class="request-number">{{ $request->request_number }}</span>
                                <span class="badge-status badge-{{ $request->status }}">{{ $request->progress_label }}</span>
                            </div>
                            <h4 class="request-title-mobile">{{ Str::limit($request->title, 35) }}</h4>
                            <span class="request-type-tag">{{ $request->typeFinancement->name ?? 'Non défini' }}</span>
                        </div>
                        
                        <div class="request-progress-compact">
                            <div class="progress-bar-mini">
                                <div class="progress-fill-mini" style="width: {{ $request->progress }}%"></div>
                            </div>
                            <span class="progress-text-mini">{{ $request->progress }}%</span>
                        </div>

                        @if($request->next_action)
                            <a href="{{ $request->next_action['url'] }}" class="request-action-btn">
                                {{ $request->next_action['text'] }}
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Activités Récentes --}}
    @if($recentActivities->count() > 0)
        <div class="activities-section">
            <h2 class="section-title">Activités récentes</h2>
            <div class="activities-timeline">
                @foreach($recentActivities->take(5) as $activity)
                    <div class="activity-row">
                        <div class="activity-dot activity-{{ $activity['type'] }}"></div>
                        <div class="activity-content-mini">
                            <div class="activity-top">
                                <span class="activity-title-mini">{{ $activity['title'] }}</span>
                                <span class="activity-time">{{ $activity['date']->diffForHumans(short: true) }}</span>
                            </div>
                            <p class="activity-desc-mini">{{ $activity['description'] }}</p>
                        </div>
                        <a href="{{ $activity['url'] }}" class="activity-arrow">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- CTA Final --}}
    <div class="dashboard-footer-cta">
        <p>Besoin d'un nouveau financement ?</p>
        <a href="{{ route('client.requests.create') }}" class="btn btn-primary btn-large">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Créer une demande
        </a>
    </div>

</div>
@endsection

@section('styles')
<style>
    /* ============================================
       DESIGN SYSTEM - SUPPORT CLAIR & SOMBRE
       ============================================ */
    
    /* Variables par défaut (Mode Clair) */
    :root {
        color-scheme: light dark;
        
        /* Couleurs primaires */
        --primary-600: #1e40af;
        --primary-500: #3b82f6;
        --primary-100: #dbeafe;
        --primary-50: #eff6ff;
        
        /* Couleurs de succès */
        --success-600: #059669;
        --success-500: #10b981;
        --success-100: #d1fae5;
        
        /* Couleurs d'avertissement */
        --warning-600: #d97706;
        --warning-500: #f59e0b;
        --warning-100: #fef3c7;
        
        /* Couleurs de danger */
        --danger-600: #dc2626;
        --danger-500: #ef4444;
        --danger-100: #fee2e2;
        
        /* Couleurs violet/orange */
        --purple-500: #8b5cf6;
        --purple-100: #ede9fe;
        --orange-500: #f97316;
        --orange-100: #ffedd5;
        
        /* Échelle de gris - Mode Clair */
        --gray-900: #0f172a;
        --gray-800: #1e293b;
        --gray-700: #334155;
        --gray-600: #475569;
        --gray-500: #64748b;
        --gray-400: #94a3b8;
        --gray-300: #cbd5e1;
        --gray-200: #e2e8f0;
        --gray-100: #f1f5f9;
        --gray-50: #f8fafc;
        
        /* Couleurs de surface et texte */
        --bg-primary: #ffffff;
        --bg-secondary: #f8fafc;
        --bg-tertiary: #f1f5f9;
        --bg-elevated: #ffffff;
        --text-primary: #0f172a;
        --text-secondary: #475569;
        --text-tertiary: #64748b;
        --text-muted: #94a3b8;
        --border-color: #e2e8f0;
        --border-light: #f1f5f9;
        
        /* Ombres */
        --shadow-sm: 0 1px 2px 0 rgba(15, 23, 42, 0.05);
        --shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.08);
        --shadow-lg: 0 10px 15px -3px rgba(15, 23, 42, 0.1);
        
        /* Rayons */
        --radius: 16px;
        --radius-sm: 12px;
    }

    /* ============================================
       MODE SOMBRE - Détection automatique
       ============================================ */
    @media (prefers-color-scheme: dark) {
        :root {
            /* Échelle de gris inversée pour le dark mode */
            --gray-900: #f8fafc;
            --gray-800: #f1f5f9;
            --gray-700: #e2e8f0;
            --gray-600: #cbd5e1;
            --gray-500: #94a3b8;
            --gray-400: #64748b;
            --gray-300: #475569;
            --gray-200: #334155;
            --gray-100: #1e293b;
            --gray-50: #0f172a;
            
            /* Couleurs de surface et texte - Dark */
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --bg-tertiary: #334155;
            --bg-elevated: #1e293b;
            --text-primary: #f8fafc;
            --text-secondary: #e2e8f0;
            --text-tertiary: #cbd5e1;
            --text-muted: #64748b;
            --border-color: #334155;
            --border-light: #1e293b;
            
            /* Ombres adaptées */
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.3);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.4);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
        }
    }

    /* ============================================
       CLASSE MANUELLE (pour toggle JS)
       ============================================ */
    [data-theme="dark"] {
        --gray-900: #f8fafc;
        --gray-800: #f1f5f9;
        --gray-700: #e2e8f0;
        --gray-600: #cbd5e1;
        --gray-500: #94a3b8;
        --gray-400: #64748b;
        --gray-300: #475569;
        --gray-200: #334155;
        --gray-100: #1e293b;
        --gray-50: #0f172a;
        
        --bg-primary: #0f172a;
        --bg-secondary: #1e293b;
        --bg-tertiary: #334155;
        --bg-elevated: #1e293b;
        --text-primary: #f8fafc;
        --text-secondary: #e2e8f0;
        --text-tertiary: #cbd5e1;
        --text-muted: #64748b;
        --border-color: #334155;
        --border-light: #1e293b;
        
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.3);
        --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.4);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
    }

    /* ============================================
       STYLES DE BASE
       ============================================ */
    .dashboard-mobile {
        padding: 16px;
        padding-bottom: 100px;
        max-width: 100%;
        overflow-x: hidden;
        background-color: var(--bg-primary);
        min-height: 100vh;
    }

    /* Header Dashboard */
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding: 4px;
    }

    .user-greeting h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0 0 6px 0;
        line-height: 1.2;
    }

    .user-name {
        color: var(--primary-500);
    }

    .user-status {
        font-size: 0.875rem;
        color: var(--text-tertiary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .status-badge {
        background: var(--primary-500);
        color: white;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .status-badge.pulse {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.8; }
    }

    .btn-quick-action {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, var(--primary-600), var(--primary-500));
        color: white;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        transition: transform 0.2s;
    }

    .btn-quick-action:active {
        transform: scale(0.95);
    }

    /* Wallet Mini (fallback) */
    .wallet-mini {
        background: linear-gradient(135deg, #1e293b, #334155);
        color: white;
        padding: 16px 20px;
        border-radius: var(--radius-sm);
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        border: 1px solid var(--border-color);
    }

    @media (prefers-color-scheme: dark) {
        .wallet-mini {
            background: linear-gradient(135deg, #334155, #475569);
        }
    }

    [data-theme="dark"] .wallet-mini {
        background: linear-gradient(135deg, #334155, #475569);
    }

    .wallet-mini-label {
        font-size: 0.75rem;
        opacity: 0.8;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #cbd5e1;
    }

    .wallet-mini-value {
        font-size: 1.5rem;
        font-weight: 700;
        display: block;
        color: white;
    }

    /* Quick Actions */
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 24px;
    }

    .action-chip {
        background: var(--bg-elevated);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-sm);
        padding: 16px 8px;
        text-align: center;
        text-decoration: none;
        color: var(--text-secondary);
        font-size: 0.8125rem;
        font-weight: 500;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        box-shadow: var(--shadow-sm);
    }

    .action-chip:active {
        transform: scale(0.98);
        background: var(--bg-secondary);
    }

    .action-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }

    .action-blue { background: linear-gradient(135deg, #3b82f6, var(--primary-500)); }
    .action-green { background: linear-gradient(135deg, #10b981, var(--success-500)); }
    .action-purple { background: linear-gradient(135deg, #8b5cf6, var(--purple-500)); }

    /* Sections */
    .section-priority {
        margin-bottom: 24px;
    }

    .section-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0 0 16px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Alerts Mobile */
    .alerts-swipe {
        display: flex;
        gap: 12px;
        overflow-x: auto;
        padding-bottom: 8px;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }

    .alerts-swipe::-webkit-scrollbar {
        display: none;
    }

    .alert-card-mobile {
        flex: 0 0 85%;
        background: var(--bg-elevated);
        border-radius: var(--radius-sm);
        padding: 16px;
        border-left: 4px solid;
        box-shadow: var(--shadow);
        display: flex;
        flex-direction: column;
        gap: 12px;
        border: 1px solid var(--border-color);
        border-left-width: 4px;
    }

    .alert-card-mobile.alert-error { 
        border-left-color: var(--danger-500); 
        background: var(--bg-elevated);
    }
    .alert-card-mobile.alert-warning { 
        border-left-color: var(--warning-500); 
        background: var(--bg-elevated);
    }
    .alert-card-mobile.alert-info { 
        border-left-color: var(--primary-500); 
        background: var(--bg-elevated);
    }

    .alert-mobile-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .alert-error .alert-mobile-icon { background: rgba(239, 68, 68, 0.15); color: var(--danger-500); }
    .alert-warning .alert-mobile-icon { background: rgba(245, 158, 11, 0.15); color: var(--warning-500); }
    .alert-info .alert-mobile-icon { background: rgba(59, 130, 246, 0.15); color: var(--primary-500); }

    @media (prefers-color-scheme: dark) {
        .alert-error .alert-mobile-icon { background: rgba(239, 68, 68, 0.25); }
        .alert-warning .alert-mobile-icon { background: rgba(245, 158, 11, 0.25); }
        .alert-info .alert-mobile-icon { background: rgba(59, 130, 246, 0.25); }
    }

    [data-theme="dark"] .alert-error .alert-mobile-icon { background: rgba(239, 68, 68, 0.25); }
    [data-theme="dark"] .alert-warning .alert-mobile-icon { background: rgba(245, 158, 11, 0.25); }
    [data-theme="dark"] .alert-info .alert-mobile-icon { background: rgba(59, 130, 246, 0.25); }

    .alert-mobile-content h4 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 4px 0;
    }

    .alert-mobile-content p {
        font-size: 0.875rem;
        color: var(--text-secondary);
        margin: 0;
        line-height: 1.4;
    }

    .alert-mobile-action {
        align-self: flex-start;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        color: white;
    }

    .alert-error .alert-mobile-action { background: var(--danger-500); }
    .alert-warning .alert-mobile-action { background: var(--warning-500); }
    .alert-info .alert-mobile-action { background: var(--primary-500); }

    /* Stats Grid */
    .stats-section {
        margin-bottom: 24px;
    }

    .stats-grid-mobile {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }

    .stat-tile {
        background: var(--bg-elevated);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-sm);
        padding: 16px;
        position: relative;
        box-shadow: var(--shadow-sm);
    }

    .stat-tile-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-bottom: 12px;
    }

    .stat-blue { background: linear-gradient(135deg, #3b82f6, var(--primary-500)); }
    .stat-green { background: linear-gradient(135deg, #10b981, var(--success-500)); }
    .stat-purple { background: linear-gradient(135deg, #8b5cf6, var(--purple-500)); }
    .stat-orange { background: linear-gradient(135deg, #f97316, var(--orange-500)); }

    .stat-tile-info {
        display: flex;
        flex-direction: column;
    }

    .stat-tile-value {
        font-size: 1.375rem;
        font-weight: 700;
        color: var(--text-primary);
        line-height: 1.2;
    }

    .stat-tile-label {
        font-size: 0.75rem;
        color: var(--text-tertiary);
        margin-top: 4px;
    }

    .stat-trend-mini {
        position: absolute;
        top: 16px;
        right: 16px;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 20px;
    }

    .stat-trend-mini.up {
        background: var(--success-100);
        color: var(--success-600);
    }

    .stat-trend-mini.down {
        background: var(--danger-100);
        color: var(--danger-600);
    }

    @media (prefers-color-scheme: dark) {
        .stat-trend-mini.up {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
        }
        .stat-trend-mini.down {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
        }
    }

    [data-theme="dark"] .stat-trend-mini.up {
        background: rgba(16, 185, 129, 0.2);
        color: #34d399;
    }
    [data-theme="dark"] .stat-trend-mini.down {
        background: rgba(239, 68, 68, 0.2);
        color: #f87171;
    }

    .stat-badge {
        position: absolute;
        top: 16px;
        right: 16px;
        font-size: 0.625rem;
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 20px;
        text-transform: uppercase;
    }

    .stat-badge.excellent { background: var(--success-100); color: var(--success-600); }
    .stat-badge.good { background: var(--warning-100); color: var(--warning-600); }
    .stat-badge.low { background: var(--danger-100); color: var(--danger-600); }

    @media (prefers-color-scheme: dark) {
        .stat-badge.excellent { background: rgba(16, 185, 129, 0.2); color: #34d399; }
        .stat-badge.good { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }
        .stat-badge.low { background: rgba(239, 68, 68, 0.2); color: #f87171; }
    }

    [data-theme="dark"] .stat-badge.excellent { background: rgba(16, 185, 129, 0.2); color: #34d399; }
    [data-theme="dark"] .stat-badge.good { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }
    [data-theme="dark"] .stat-badge.low { background: rgba(239, 68, 68, 0.2); color: #f87171; }

    /* Requests Section */
    .requests-section {
        margin-bottom: 24px;
    }

    .section-header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .link-see-all {
        font-size: 0.875rem;
        color: var(--primary-500);
        font-weight: 500;
        text-decoration: none;
    }

    .requests-list-mobile {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .request-item-mobile {
        background: var(--bg-elevated);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-sm);
        padding: 16px;
        box-shadow: var(--shadow-sm);
    }

    .request-item-header {
        margin-bottom: 12px;
    }

    .request-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .request-number {
        font-size: 0.75rem;
        color: var(--text-muted);
        font-family: ui-monospace, monospace;
        font-weight: 500;
    }

    .badge-status {
        font-size: 0.6875rem;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }

    .badge-draft { background: var(--bg-tertiary); color: var(--text-secondary); }
    .badge-submitted { background: var(--primary-100); color: var(--primary-600); }
    .badge-under_review { background: var(--warning-100); color: var(--warning-600); }
    .badge-pending_committee { background: var(--purple-100); color: #6d28d9; }
    .badge-approved { background: var(--success-100); color: var(--success-600); }

    @media (prefers-color-scheme: dark) {
        .badge-draft { background: rgba(51, 65, 85, 0.5); color: var(--text-secondary); }
        .badge-submitted { background: rgba(30, 64, 175, 0.3); color: #60a5fa; }
        .badge-under_review { background: rgba(217, 119, 6, 0.3); color: #fbbf24; }
        .badge-pending_committee { background: rgba(139, 92, 246, 0.3); color: #a78bfa; }
        .badge-approved { background: rgba(5, 150, 105, 0.3); color: #34d399; }
    }

    [data-theme="dark"] .badge-draft { background: rgba(51, 65, 85, 0.5); color: var(--text-secondary); }
    [data-theme="dark"] .badge-submitted { background: rgba(30, 64, 175, 0.3); color: #60a5fa; }
    [data-theme="dark"] .badge-under_review { background: rgba(217, 119, 6, 0.3); color: #fbbf24; }
    [data-theme="dark"] .badge-pending_committee { background: rgba(139, 92, 246, 0.3); color: #a78bfa; }
    [data-theme="dark"] .badge-approved { background: rgba(5, 150, 105, 0.3); color: #34d399; }

    .request-title-mobile {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 6px 0;
    }

    .request-type-tag {
        font-size: 0.75rem;
        color: var(--text-tertiary);
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .request-progress-compact {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
    }

    .progress-bar-mini {
        flex: 1;
        height: 6px;
        background: var(--bg-tertiary);
        border-radius: 3px;
        overflow: hidden;
    }

    .progress-fill-mini {
        height: 100%;
        background: linear-gradient(90deg, var(--primary-500), #60a5fa);
        border-radius: 3px;
        transition: width 0.5s ease;
    }

    .progress-text-mini {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--primary-500);
        min-width: 32px;
        text-align: right;
    }

    .request-action-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 100%;
        padding: 10px;
        background: var(--primary-50);
        color: var(--primary-600);
        border-radius: 8px;
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 600;
        transition: all 0.2s;
        border: 1px solid var(--primary-100);
    }

    .request-action-btn:active {
        background: var(--primary-100);
    }

    @media (prefers-color-scheme: dark) {
        .request-action-btn {
            background: rgba(30, 64, 175, 0.2);
            color: #60a5fa;
            border-color: rgba(30, 64, 175, 0.3);
        }
        .request-action-btn:active {
            background: rgba(30, 64, 175, 0.3);
        }
    }

    [data-theme="dark"] .request-action-btn {
        background: rgba(30, 64, 175, 0.2);
        color: #60a5fa;
        border-color: rgba(30, 64, 175, 0.3);
    }
    [data-theme="dark"] .request-action-btn:active {
        background: rgba(30, 64, 175, 0.3);
    }

    /* Activities */
    .activities-section {
        margin-bottom: 24px;
    }

    .activities-timeline {
        background: var(--bg-elevated);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-sm);
        padding: 8px 16px;
        box-shadow: var(--shadow-sm);
    }

    .activity-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 0;
        border-bottom: 1px solid var(--border-light);
    }

    .activity-row:last-child {
        border-bottom: none;
    }

    .activity-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .activity-dot.activity-request { background: var(--primary-500); }
    .activity-dot.activity-notification { background: var(--warning-500); }
    .activity-dot.activity-transaction { background: var(--success-500); }

    .activity-content-mini {
        flex: 1;
        min-width: 0;
    }

    .activity-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 4px;
    }

    .activity-title-mini {
        font-size: 0.9375rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .activity-time {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .activity-desc-mini {
        font-size: 0.8125rem;
        color: var(--text-secondary);
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .activity-arrow {
        color: var(--text-muted);
        flex-shrink: 0;
    }

    /* Footer CTA */
    .dashboard-footer-cta {
        background: linear-gradient(135deg, var(--bg-secondary), var(--bg-primary));
        border: 2px dashed var(--border-color);
        border-radius: var(--radius);
        padding: 24px;
        text-align: center;
        margin-top: 8px;
    }

    .dashboard-footer-cta p {
        font-size: 0.9375rem;
        color: var(--text-secondary);
        margin: 0 0 16px 0;
    }

    .btn-large {
        width: 100%;
        padding: 14px 24px;
        font-size: 1rem;
        justify-content: center;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-600), var(--primary-500));
        color: white;
        border: none;
        border-radius: var(--radius-sm);
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
    }

    .btn-primary:active {
        transform: translateY(1px);
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.2);
    }

    /* Responsive adjustments */
    @media (min-width: 640px) {
        .dashboard-mobile {
            padding: 24px;
            max-width: 600px;
            margin: 0 auto;
        }

        .quick-actions {
            grid-template-columns: repeat(3, 1fr);
        }

        .stats-grid-mobile {
            grid-template-columns: repeat(4, 1fr);
        }

        .alert-card-mobile {
            flex: 0 0 45%;
        }
    }

    @media (min-width: 1024px) {
        .dashboard-mobile {
            max-width: 900px;
        }

        .two-columns-desktop {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 24px;
        }
    }
</style>
@endsection