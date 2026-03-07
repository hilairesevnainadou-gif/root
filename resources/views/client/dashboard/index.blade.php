@extends('layouts.app')

@section('title', 'Tableau de bord - BHDM')

@section('header-title', 'Tableau de bord')

@section('content')
<div class="dashboard-container">

    {{-- Section Bienvenue --}}
    <div class="welcome-section">
        <div class="welcome-content">
            <h1 class="welcome-title">Bonjour, {{ $user->first_name }} !</h1>
            <p class="welcome-subtitle">
                @if($stats['active_requests'] > 0)
                    Vous avez {{ $stats['active_requests'] }} demande{{ $stats['active_requests'] > 1 ? 's' : '' }} en cours
                @else
                    Gérez vos demandes de financement en toute simplicité
                @endif
            </p>
        </div>
        <a href="{{ route('client.requests.create') }}" class="btn btn-primary btn-create">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouvelle demande
        </a>
    </div>

    {{-- Alertes --}}
    @if(!empty($alerts))
        <div class="alerts-section">
            @foreach($alerts as $alert)
                <div class="alert-card alert-{{ $alert['type'] }}">
                    <div class="alert-icon">
                        @switch($alert['icon'])
                            @case('document')
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                @break
                            @case('draft')
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                @break
                            @case('notification')
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                @break
                            @case('profile')
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                @break
                            @default
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @endswitch
                    </div>
                    <div class="alert-content">
                        <h4 class="alert-title">{{ $alert['title'] }}</h4>
                        <p class="alert-message">{{ $alert['message'] }}</p>
                    </div>
                    <a href="{{ $alert['action_url'] }}" class="btn btn-sm btn-alert">{{ $alert['action_text'] }}</a>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Statistiques Principales --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon stat-icon-blue">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                @if($stats['total_requests']['trend'] != 0)
                    <span class="stat-trend {{ $stats['total_requests']['trend'] > 0 ? 'trend-up' : 'trend-down' }}">
                        {{ $stats['total_requests']['trend'] > 0 ? '+' : '' }}{{ $stats['total_requests']['trend'] }}%
                    </span>
                @endif
            </div>
            <div class="stat-value">{{ $stats['total_requests']['value'] }}</div>
            <div class="stat-label">Demandes totales</div>
            <div class="stat-subtitle">{{ $stats['total_requests']['this_month'] }} ce mois</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon stat-icon-green">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="stat-value">{{ $stats['total_funded']['formatted'] }}</div>
            <div class="stat-label">Montant financé</div>
            <div class="stat-subtitle">Total approuvé</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon stat-icon-purple">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="stat-value">{{ $stats['success_rate']['value'] }}%</div>
            <div class="stat-label">Taux de succès</div>
            <div class="stat-subtitle {{ $stats['success_rate']['value'] >= 70 ? 'text-success' : ($stats['success_rate']['value'] >= 40 ? 'text-warning' : 'text-danger') }}">
                {{ $stats['success_rate']['label'] }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon stat-icon-orange">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="stat-value">{{ $stats['active_requests'] }}</div>
            <div class="stat-label">En cours</div>
            <div class="stat-subtitle">Demandes actives</div>
        </div>
    </div>

    {{-- Graphique d'Activité --}}
    <div class="chart-section">
        <div class="section-header">
            <h2 class="section-title">Activité des 12 derniers mois</h2>
        </div>
        <div class="chart-container">
            <canvas id="activityChart"></canvas>
        </div>
    </div>
    <div class="two-columns">
        {{-- Demandes Actives --}}
        <div class="column-main">
            <div class="section-header">
                <h2 class="section-title">Demandes en cours</h2>
                <a href="{{ route('client.requests.index') }}" class="link-view-all">Voir tout</a>
            </div>

            @if($activeRequests->count() > 0)
                <div class="requests-list">
                    @foreach($activeRequests as $request)
                        <div class="request-card">
                            <div class="request-header">
                                <div class="request-info">
                                    <h4 class="request-title">{{ $request->title }}</h4>
                                    <span class="request-number">{{ $request->request_number }}</span>
                                </div>
                                <span class="badge badge-{{ $request->status }}">
                                    {{ $request->progress_label }}
                                </span>
                            </div>

                            <div class="request-type">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                {{ $request->typeFinancement->name ?? 'Non défini' }}
                            </div>

                            <div class="progress-section">
                                <div class="progress-header">
                                    <span class="progress-label">Progression</span>
                                    <span class="progress-value">{{ $request->progress }}%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: {{ $request->progress }}%"></div>
                                </div>
                            </div>

                            @if($request->next_action)
                                <div class="request-footer">
                                    <a href="{{ $request->next_action['url'] }}" class="btn btn-sm btn-action">
                                        {{ $request->next_action['text'] }}
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <p class="empty-text">Aucune demande en cours</p>
                    <a href="{{ route('client.financements.index') }}" class="btn btn-primary">Découvrir les financements</a>
                </div>
            @endif
        </div>

        {{-- Colonne Latérale : Portefeuille + Actions --}}
        <div class="column-side">
            {{-- Portefeuille --}}
            <div class="wallet-card">
                <div class="wallet-header">
                    <h3 class="wallet-title">Portefeuille</h3>
                    @if($walletStats['has_wallet'])
                        <span class="wallet-status">Actif</span>
                    @endif
                </div>

                @if($walletStats['has_wallet'])
                    <div class="wallet-balance">
                        <span class="balance-amount">{{ $walletStats['formatted_balance'] }}</span>
                        <span class="balance-currency">{{ $walletStats['currency'] }}</span>
                    </div>

                    <div class="wallet-stats">
                        <div class="wallet-stat">
                            <span class="wallet-stat-label">Transactions</span>
                            <span class="wallet-stat-value">{{ $walletStats['transactions_count'] }}</span>
                        </div>
                        @if($walletStats['last_transaction'])
                            <div class="wallet-stat">
                                <span class="wallet-stat-label">Dernière</span>
                                <span class="wallet-stat-value">{{ $walletStats['last_transaction']['date'] }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="wallet-actions">
                        <a href="{{ route('client.wallet.show') }}" class="btn btn-outline btn-block">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Voir détails
                        </a>
                    </div>
                @else
                    <div class="wallet-empty">
                        <p>Aucun portefeuille actif</p>
                        <a href="{{ route('client.wallet.show') }}" class="btn btn-primary btn-block">Activer</a>
                    </div>
                @endif
            </div>

            {{-- Actions Prioritaires --}}
            @if(!empty($priorityActions))
                <div class="priority-section">
                    <h3 class="section-title-small">Actions prioritaires</h3>
                    <div class="priority-list">
                        @foreach($priorityActions as $action)
                            <div class="priority-item priority-{{ $action['priority'] }}">
                                <div class="priority-dot"></div>
                                <div class="priority-content">
                                    <h4 class="priority-title">{{ $action['title'] }}</h4>
                                    <p class="priority-desc">{{ $action['description'] }}</p>
                                    @if($action['deadline'])
                                        <span class="priority-deadline">⏰ {{ $action['deadline'] }}</span>
                                    @endif
                                </div>
                                <a href="{{ $action['url'] }}" class="priority-link">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Résumé Financier --}}
            <div class="financial-summary">
                <h3 class="section-title-small">Résumé financier</h3>
                <div class="financial-grid">
                    <div class="financial-item">
                        <span class="financial-label">Revenus (mois)</span>
                        <span class="financial-value text-success">+{{ number_format($financialSummary['monthly_income'], 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div class="financial-item">
                        <span class="financial-label">Dépenses (mois)</span>
                        <span class="financial-value text-danger">-{{ number_format($financialSummary['monthly_expenses'], 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div class="financial-item">
                        <span class="financial-label">En attente</span>
                        <span class="financial-value text-warning">{{ number_format($financialSummary['pending_amount'], 0, ',', ' ') }} FCFA</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Activités Récentes --}}
    <div class="activities-section">
        <div class="section-header">
            <h2 class="section-title">Activités récentes</h2>
        </div>

        @if($recentActivities->count() > 0)
            <div class="activities-list">
                @foreach($recentActivities as $activity)
                    <div class="activity-item">
                        <div class="activity-icon activity-{{ $activity['type'] }}">
                            @switch($activity['icon'])
                                @case('document')
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    @break
                                @case('bell')
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    @break
                                @case('arrow-down')
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                    @break
                                @case('arrow-up')
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                    @break
                                @default
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @endswitch
                        </div>
                        <div class="activity-content">
                            <div class="activity-header">
                                <h4 class="activity-title">{{ $activity['title'] }}</h4>
                                @if(isset($activity['amount_formatted']))
                                    <span class="activity-amount {{ $activity['type'] === 'credit' ? 'text-success' : 'text-danger' }}">
                                        {{ $activity['type'] === 'credit' ? '+' : '-' }}{{ $activity['amount_formatted'] }}
                                    </span>
                                @endif
                            </div>
                            <p class="activity-desc">{{ $activity['description'] }}</p>
                            <div class="activity-meta">
                                <span class="activity-status status-{{ $activity['status'] }}">{{ $activity['status'] }}</span>
                                <span class="activity-date">{{ $activity['date']->diffForHumans() }}</span>
                            </div>
                        </div>
                        <a href="{{ $activity['url'] }}" class="activity-link">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state-small">
                <p>Aucune activité récente</p>
            </div>
        @endif
    </div>

</div>
@endsection

@section('styles')
<style>
    /* Variables et Reset */
    :root {
        --primary: #2563eb;
        --primary-dark: #1d4ed8;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --purple: #8b5cf6;
        --orange: #f97316;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-400: #9ca3af;
        --gray-500: #6b7280;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --gray-900: #111827;
        --radius: 12px;
        --radius-sm: 8px;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    .dashboard-container {
        padding: 1rem;
        max-width: 1200px;
        margin: 0 auto;
        padding-bottom: 80px; /* Espace pour la nav mobile */
    }

    /* Section Bienvenue */
    .welcome-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .welcome-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--gray-900);
        margin: 0;
    }

    .welcome-subtitle {
        color: var(--gray-500);
        margin: 0.25rem 0 0 0;
        font-size: 0.875rem;
    }

    .btn-create {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        background: var(--primary);
        color: white;
        border-radius: var(--radius);
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s;
    }

    .btn-create:hover {
        background: var(--primary-dark);
        transform: translateY(-1px);
    }

    .btn-create svg {
        width: 20px;
        height: 20px;
    }

    /* Alertes */
    .alerts-section {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }

    .alert-card {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem;
        border-radius: var(--radius);
        border-left: 4px solid;
    }

    .alert-error {
        background: #fef2f2;
        border-color: var(--danger);
    }

    .alert-warning {
        background: #fffbeb;
        border-color: var(--warning);
    }

    .alert-info {
        background: #eff6ff;
        border-color: var(--primary);
    }

    .alert-icon {
        flex-shrink: 0;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
    }

    .alert-error .alert-icon { color: var(--danger); }
    .alert-warning .alert-icon { color: var(--warning); }
    .alert-info .alert-icon { color: var(--primary); }

    .alert-icon svg {
        width: 20px;
        height: 20px;
    }

    .alert-content {
        flex: 1;
    }

    .alert-title {
        font-size: 0.875rem;
        font-weight: 600;
        margin: 0 0 0.25rem 0;
        color: var(--gray-900);
    }

    .alert-message {
        font-size: 0.8125rem;
        color: var(--gray-600);
        margin: 0;
    }

    .btn-alert {
        padding: 0.5rem 1rem;
        border-radius: var(--radius-sm);
        font-size: 0.8125rem;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
    }

    .alert-error .btn-alert {
        background: var(--danger);
        color: white;
    }

    .alert-warning .btn-alert {
        background: var(--warning);
        color: white;
    }

    .alert-info .btn-alert {
        background: var(--primary);
        color: white;
    }

    /* Statistiques */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    @media (min-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    .stat-card {
        background: white;
        padding: 1.25rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--gray-200);
    }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-icon svg {
        width: 20px;
        height: 20px;
        color: white;
    }

    .stat-icon-blue { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .stat-icon-green { background: linear-gradient(135deg, #34d399, #10b981); }
    .stat-icon-purple { background: linear-gradient(135deg, #a78bfa, #8b5cf6); }
    .stat-icon-orange { background: linear-gradient(135deg, #fb923c, #f97316); }

    .stat-trend {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.5rem;
        border-radius: 9999px;
    }

    .trend-up {
        background: #d1fae5;
        color: #065f46;
    }

    .trend-down {
        background: #fee2e2;
        color: #991b1b;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--gray-900);
        line-height: 1;
    }

    .stat-label {
        font-size: 0.875rem;
        color: var(--gray-500);
        margin-top: 0.25rem;
    }

    .stat-subtitle {
        font-size: 0.75rem;
        color: var(--gray-400);
        margin-top: 0.25rem;
    }

    .text-success { color: var(--success); }
    .text-warning { color: var(--warning); }
    .text-danger { color: var(--danger); }

    /* Graphique */
    .chart-section {
        background: white;
        padding: 1.25rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--gray-200);
        margin-bottom: 1.5rem;
    }

    .chart-container {
        height: 250px;
        position: relative;
    }

    /* Layout Deux Colonnes */
    .two-columns {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    @media (min-width: 1024px) {
        .two-columns {
            grid-template-columns: 2fr 1fr;
        }
    }

    /* Section Headers */
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .section-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--gray-900);
        margin: 0;
    }

    .section-title-small {
        font-size: 0.9375rem;
        font-weight: 600;
        color: var(--gray-800);
        margin: 0 0 0.75rem 0;
    }

    .link-view-all {
        font-size: 0.875rem;
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
    }

    /* Demandes */
    .requests-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .request-card {
        background: white;
        padding: 1.25rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--gray-200);
        transition: all 0.2s;
    }

    .request-card:hover {
        box-shadow: var(--shadow);
        transform: translateY(-2px);
    }

    .request-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.75rem;
    }

    .request-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--gray-900);
        margin: 0;
    }

    .request-number {
        font-size: 0.75rem;
        color: var(--gray-400);
        font-family: monospace;
    }

    .badge {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        text-transform: uppercase;
    }

    .badge-draft { background: var(--gray-100); color: var(--gray-600); }
    .badge-submitted { background: #dbeafe; color: #1e40af; }
    .badge-under_review { background: #fef3c7; color: #92400e; }
    .badge-pending_committee { background: #e0e7ff; color: #3730a3; }
    .badge-approved { background: #d1fae5; color: #065f46; }
    .badge-funded { background: #d1fae5; color: #065f46; }

    .request-type {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: var(--gray-600);
        margin-bottom: 1rem;
    }

    .request-type svg {
        width: 16px;
        height: 16px;
    }

    .progress-section {
        margin-bottom: 1rem;
    }

    .progress-header {
        display: flex;
        justify-content: space-between;
        font-size: 0.8125rem;
        margin-bottom: 0.5rem;
    }

    .progress-label { color: var(--gray-500); }
    .progress-value { color: var(--gray-700); font-weight: 600; }

    .progress-bar {
        height: 6px;
        background: var(--gray-200);
        border-radius: 9999px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--primary), #60a5fa);
        border-radius: 9999px;
        transition: width 0.5s ease;
    }

    .request-footer {
        display: flex;
        justify-content: flex-end;
    }

    .btn-action {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.5rem 1rem;
        background: var(--primary);
        color: white;
        border-radius: var(--radius-sm);
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .btn-action svg {
        width: 16px;
        height: 16px;
    }

    /* Portefeuille */
    .wallet-card {
        background: linear-gradient(135deg, #1e3a8a, #3730a3);
        color: white;
        padding: 1.5rem;
        border-radius: var(--radius);
        margin-bottom: 1.5rem;
    }

    .wallet-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .wallet-title {
        font-size: 0.9375rem;
        font-weight: 600;
        margin: 0;
        opacity: 0.9;
    }

    .wallet-status {
        font-size: 0.75rem;
        background: rgba(255,255,255,0.2);
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
    }

    .wallet-balance {
        margin-bottom: 1.25rem;
    }

    .balance-amount {
        font-size: 2rem;
        font-weight: 700;
        display: block;
    }

    .balance-currency {
        font-size: 0.875rem;
        opacity: 0.8;
    }

    .wallet-stats {
        display: flex;
        gap: 1.5rem;
        margin-bottom: 1.25rem;
    }

    .wallet-stat {
        display: flex;
        flex-direction: column;
    }

    .wallet-stat-label {
        font-size: 0.75rem;
        opacity: 0.7;
    }

    .wallet-stat-value {
        font-size: 0.9375rem;
        font-weight: 600;
    }

    .btn-outline {
        background: transparent;
        border: 1px solid rgba(255,255,255,0.3);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-outline:hover {
        background: rgba(255,255,255,0.1);
    }

    .wallet-empty {
        text-align: center;
        padding: 1rem 0;
    }

    .wallet-empty p {
        opacity: 0.8;
        margin-bottom: 1rem;
    }

    /* Actions Prioritaires */
    .priority-section {
        background: white;
        padding: 1.25rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--gray-200);
        margin-bottom: 1.5rem;
    }

    .priority-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .priority-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.875rem;
        background: var(--gray-50);
        border-radius: var(--radius-sm);
        border-left: 3px solid;
    }

    .priority-high { border-color: var(--danger); }
    .priority-medium { border-color: var(--warning); }
    .priority-low { border-color: var(--primary); }

    .priority-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .priority-high .priority-dot { background: var(--danger); }
    .priority-medium .priority-dot { background: var(--warning); }
    .priority-low .priority-dot { background: var(--primary); }

    .priority-content {
        flex: 1;
    }

    .priority-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--gray-900);
        margin: 0 0 0.25rem 0;
    }

    .priority-desc {
        font-size: 0.8125rem;
        color: var(--gray-600);
        margin: 0 0 0.25rem 0;
        line-height: 1.4;
    }

    .priority-deadline {
        font-size: 0.75rem;
        color: var(--danger);
        font-weight: 500;
    }

    .priority-link {
        color: var(--gray-400);
        display: flex;
        align-items: center;
    }

    .priority-link svg {
        width: 20px;
        height: 20px;
    }

    /* Résumé Financier */
    .financial-summary {
        background: white;
        padding: 1.25rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--gray-200);
    }

    .financial-grid {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .financial-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--gray-100);
    }

    .financial-item:last-child {
        border-bottom: none;
    }

    .financial-label {
        font-size: 0.875rem;
        color: var(--gray-600);
    }

    .financial-value {
        font-size: 0.9375rem;
        font-weight: 600;
    }

    /* Activités */
    .activities-section {
        background: white;
        padding: 1.25rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--gray-200);
    }

    .activities-list {
        display: flex;
        flex-direction: column;
    }

    .activity-item {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        padding: 1rem 0;
        border-bottom: 1px solid var(--gray-100);
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .activity-icon svg {
        width: 20px;
        height: 20px;
        color: white;
    }

    .activity-request { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .activity-notification { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .activity-transaction { background: linear-gradient(135deg, #10b981, #059669); }

    .activity-content {
        flex: 1;
        min-width: 0;
    }

    .activity-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.25rem;
    }

    .activity-title {
        font-size: 0.9375rem;
        font-weight: 600;
        color: var(--gray-900);
        margin: 0;
    }

    .activity-amount {
        font-size: 0.875rem;
        font-weight: 600;
    }

    .activity-desc {
        font-size: 0.8125rem;
        color: var(--gray-600);
        margin: 0 0 0.25rem 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .activity-meta {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .activity-status {
        font-size: 0.75rem;
        padding: 0.125rem 0.5rem;
        border-radius: 9999px;
        text-transform: capitalize;
    }

    .status-draft { background: var(--gray-100); color: var(--gray-600); }
    .status-submitted { background: #dbeafe; color: #1e40af; }
    .status-read { background: var(--gray-100); color: var(--gray-500); }
    .status-unread { background: #dbeafe; color: #1e40af; }
    .status-completed { background: #d1fae5; color: #065f46; }
    .status-pending { background: #fef3c7; color: #92400e; }

    .activity-date {
        font-size: 0.75rem;
        color: var(--gray-400);
    }

    .activity-link {
        color: var(--gray-400);
        display: flex;
        align-items: center;
    }

    .activity-link svg {
        width: 20px;
        height: 20px;
    }

    /* Empty States */
    .empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
        background: var(--gray-50);
        border-radius: var(--radius);
        border: 2px dashed var(--gray-200);
    }

    .empty-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 1rem;
        color: var(--gray-300);
    }

    .empty-icon svg {
        width: 100%;
        height: 100%;
    }

    .empty-text {
        color: var(--gray-500);
        margin-bottom: 1.5rem;
    }

    .empty-state-small {
        text-align: center;
        padding: 2rem;
        color: var(--gray-400);
        font-size: 0.875rem;
    }

    /* Boutons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.625rem 1.25rem;
        border-radius: var(--radius-sm);
        font-weight: 500;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary {
        background: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background: var(--primary-dark);
    }

    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }

    .btn-block {
        width: 100%;
    }

    /* Responsive */
    @media (max-width: 640px) {
        .welcome-section {
            flex-direction: column;
            align-items: flex-start;
        }

        .welcome-title {
            font-size: 1.25rem;
        }

        .btn-create {
            width: 100%;
            justify-content: center;
        }

        .stat-value {
            font-size: 1.25rem;
        }

        .balance-amount {
            font-size: 1.5rem;
        }
    }
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Graphique d'activité
    const ctx = document.getElementById('activityChart').getContext('2d');
    const activityChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($activityChart['labels']),
            datasets: [
                {
                    label: 'Demandes créées',
                    data: @json($activityChart['requests']),
                    backgroundColor: 'rgba(37, 99, 235, 0.8)',
                    borderColor: 'rgba(37, 99, 235, 1)',
                    borderWidth: 0,
                    borderRadius: 4,
                },
                {
                    label: 'Montant financé (FCFA)',
                    data: @json($activityChart['funded']),
                    backgroundColor: 'rgba(16, 185, 129, 0.8)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 0,
                    borderRadius: 4,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(17, 24, 39, 0.9)',
                    padding: 12,
                    cornerRadius: 8,
                    titleFont: {
                        size: 13
                    },
                    bodyFont: {
                        size: 12
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        font: {
                            size: 11
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false,
                    },
                    ticks: {
                        callback: function(value) {
                            return value >= 1000000 ? (value/1000000).toFixed(1) + 'M' :
                                   value >= 1000 ? (value/1000).toFixed(0) + 'K' : value;
                        },
                        font: {
                            size: 11
                        }
                    }
                }
            }
        }
    });
</script>
@endsection
