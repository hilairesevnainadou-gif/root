<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#1e40af">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="BHDM">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Espace Client - BHDM')</title>

    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('icons/icon-72x72.png') }}">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pwa.css') }}">
    <link rel="stylesheet" href="{{ asset('css/transitions.css') }}">

    @yield('styles')

    <style>
        /* Design System Professionnel */
        :root {
            --primary-600: #1e40af;
            --primary-500: #3b82f6;
            --primary-50: #eff6ff;
            --success-500: #10b981;
            --warning-500: #f59e0b;
            --danger-500: #ef4444;
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
            --radius: 16px;
            --radius-sm: 12px;
            --shadow-sm: 0 1px 2px 0 rgba(15, 23, 42, 0.05);
            --shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.08);
            --shadow-lg: 0 10px 15px -3px rgba(15, 23, 42, 0.1);
        }

        /* Header Premium - Simplifié */
        .mobile-header {
            background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-500) 100%);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .mobile-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            height: 56px;
        }

        .brand-logo {
            height: 32px;
            width: auto;
            filter: brightness(0) invert(1);
            object-fit: contain;
        }

        .mobile-header-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-logo-fallback {
            display: none;
            width: 32px;
            height: 32px;
            background: white;
            border-radius: 8px;
            align-items: center;
            justify-content: center;
            color: var(--primary-600);
            font-weight: 800;
            font-size: 1.125rem;
        }

        .mobile-header-title {
            color: rgba(255, 255, 255, 0.95);
            font-weight: 600;
            font-size: 1.125rem;
            letter-spacing: -0.025em;
        }

        /* Bouton Déconnexion Unique - Style Premium */
        .header-actions {
            display: flex;
            align-items: center;
        }

        .btn-logout-header {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 14px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            backdrop-filter: blur(10px);
            transition: all 0.2s ease;
            cursor: pointer;
            -webkit-tap-highlight-color: transparent;
        }

        .btn-logout-header:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .btn-logout-header:active {
            background: rgba(255, 255, 255, 0.25);
            transform: scale(0.98);
        }

        .btn-logout-header svg {
            width: 18px;
            height: 18px;
            stroke-width: 2;
        }

        .logout-label {
            display: none;
        }

        @media (min-width: 380px) {
            .logout-label {
                display: inline;
            }
        }

        /* Carte Portefeuille Premium */
        .wallet-card {
            background: linear-gradient(145deg, #1e3a8a 0%, #3b82f6 50%, #60a5fa 100%);
            border-radius: 20px;
            padding: 24px;
            margin: 16px;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px -10px rgba(30, 64, 175, 0.4);
        }

        .wallet-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
            border-radius: 50%;
        }

        .wallet-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .wallet-label {
            font-size: 0.875rem;
            font-weight: 500;
            opacity: 0.85;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 8px;
        }

        .wallet-balance {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .wallet-currency {
            font-size: 1.125rem;
            font-weight: 600;
            opacity: 0.9;
            margin-left: 4px;
        }

        .wallet-icon {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 14px;
            padding: 10px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .wallet-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            position: relative;
            z-index: 1;
            padding-top: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .wallet-stat {
            text-align: left;
        }

        .wallet-stat-value {
            font-size: 1.125rem;
            font-weight: 700;
            display: block;
            margin-bottom: 4px;
        }

        .wallet-stat-label {
            font-size: 0.75rem;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Alertes Professionnelles */
        .alert-premium {
            border-radius: 12px;
            padding: 14px 16px;
            margin: 12px 16px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            border-left: 4px solid;
            animation: slideUp 0.4s ease-out;
        }

        .alert-premium.error {
            background: #fef2f2;
            border-color: var(--danger-500);
            color: #991b1b;
        }

        .alert-premium.warning {
            background: #fffbeb;
            border-color: var(--warning-500);
            color: #92400e;
        }

        .alert-premium.info {
            background: #eff6ff;
            border-color: var(--primary-500);
            color: #1e40af;
        }

        .alert-icon {
            flex-shrink: 0;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .alert-premium.error .alert-icon {
            background: #fee2e2;
            color: var(--danger-500);
        }

        .alert-premium.warning .alert-icon {
            background: #fef3c7;
            color: var(--warning-500);
        }

        .alert-premium.info .alert-icon {
            background: #dbeafe;
            color: var(--primary-500);
        }

        .alert-content h4 {
            font-weight: 600;
            font-size: 0.9375rem;
            margin-bottom: 4px;
        }

        .alert-content p {
            font-size: 0.875rem;
            opacity: 0.9;
            line-height: 1.4;
            margin: 0;
        }

        .alert-action {
            margin-top: 10px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-weight: 600;
            font-size: 0.875rem;
            text-decoration: none;
        }

        /* Navigation Optimisée */
        .mobile-nav {
            background: white;
            border-top: 1px solid var(--gray-200);
            padding: 8px 0 calc(8px + env(safe-area-inset-bottom));
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.05);
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 100;
            display: flex;
            justify-content: space-around;
            align-items: flex-end;
        }

        .mobile-nav-item {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            padding: 8px 4px;
            color: var(--gray-400);
            font-size: 0.6875rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            -webkit-tap-highlight-color: transparent;
        }

        .mobile-nav-item.active {
            color: var(--primary-600);
        }

        .mobile-nav-item svg {
            width: 24px;
            height: 24px;
            stroke-width: 2;
            transition: transform 0.2s;
        }

        .mobile-nav-item:active svg {
            transform: scale(0.9);
        }

        .mobile-nav-item.nav-item-primary {
            position: relative;
            top: -16px;
            color: var(--primary-600);
        }

        .nav-icon-bg {
            background: linear-gradient(135deg, var(--primary-600), var(--primary-500));
            border-radius: 50%;
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.35);
            color: white;
            margin-bottom: 4px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .mobile-nav-item.nav-item-primary:active .nav-icon-bg {
            transform: scale(0.95);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        /* Modal Premium */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background: white;
            border-radius: 24px;
            padding: 32px 24px;
            max-width: 340px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            position: relative;
            z-index: 1;
            animation: modalSlideUp 0.3s ease-out;
        }

        @keyframes modalSlideUp {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .modal-icon-danger {
            background: #fef2f2;
            color: var(--danger-500);
            width: 72px;
            height: 72px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 8px;
        }

        .modal-text {
            color: var(--gray-600);
            font-size: 0.9375rem;
            line-height: 1.5;
            margin-bottom: 24px;
        }

        .modal-actions {
            display: flex;
            gap: 12px;
        }

        .btn {
            flex: 1;
            padding: 14px 20px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.9375rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            -webkit-tap-highlight-color: transparent;
        }

        .btn-secondary {
            background: var(--gray-100);
            color: var(--gray-700);
        }

        .btn-secondary:active {
            background: var(--gray-200);
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc2626, var(--danger-500));
            color: white;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .btn-danger:active {
            transform: scale(0.98);
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2);
        }

        /* Offline Banner */
        .offline-banner {
            background: var(--gray-900);
            color: white;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.875rem;
            font-weight: 500;
            position: sticky;
            top: 0;
            z-index: 99;
        }

        .offline-banner.hidden {
            display: none;
        }

        /* Page Transition */
        .page-transition {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: white;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
        }

        .page-transition.active {
            opacity: 1;
            pointer-events: all;
        }

        .transition-spinner {
            width: 50px;
            height: 50px;
        }

        /* PWA Prompts */
        .pwa-prompt-ios {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            display: none;
            padding: 20px;
            animation: slideUp 0.4s ease-out;
        }

        @keyframes slideUp {
            from {
                transform: translateY(100%);
            }

            to {
                transform: translateY(0);
            }
        }

        .pwa-prompt-content-ios {
            background: white;
            border-radius: 20px;
            padding: 24px;
            max-width: 360px;
            margin: 0 auto;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            position: relative;
        }

        .pwa-prompt-close {
            position: absolute;
            top: 16px;
            right: 16px;
            background: none;
            border: none;
            font-size: 24px;
            color: var(--gray-400);
            cursor: pointer;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s;
        }

        .pwa-prompt-close:active {
            background: var(--gray-100);
        }

        /* Main Content */
        .main-content {
            min-height: calc(100vh - 56px);
            padding-bottom: 80px;
        }

        .page-content {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.4s ease;
        }

        .page-content.loaded {
            opacity: 1;
            transform: translateY(0);
        }

        /* Alertes système */
        .alert {
            margin: 16px;
            padding: 16px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.9375rem;
            animation: slideUp 0.4s ease-out;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-close {
            margin-left: auto;
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            opacity: 0.6;
            padding: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .alert-close:active {
            opacity: 1;
        }
    </style>
</head>

<body>
    <!-- Page Transition -->
    <div id="page-transition" class="page-transition">
        <div class="transition-spinner">
            <svg viewBox="0 0 50 50">
                <circle cx="25" cy="25" r="20" fill="none" stroke="#3b82f6" stroke-width="4" stroke-linecap="round">
                    <animate attributeName="stroke-dasharray" values="1,150;90,150;90,150" dur="1.5s"
                        repeatCount="indefinite" />
                    <animate attributeName="stroke-dashoffset" values="0;-35;-124" dur="1.5s"
                        repeatCount="indefinite" />
                </circle>
            </svg>
        </div>
    </div>

    <!-- Offline Banner -->
    <div id="offline-banner" class="offline-banner hidden">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414" />
        </svg>
        <span>Connexion interrompue - Mode hors ligne</span>
    </div>

    <!-- Header Professionnel - Simplifié -->
    <header class="mobile-header">
        <div class="mobile-header-content">
            <div class="mobile-header-brand">
                <img src="{{ asset('images/logo.png') }}" alt="BHDM" class="brand-logo"
                    onerror="this.style.display='none'; document.querySelector('.header-logo-fallback').style.display='flex'">
                <div class="header-logo-fallback">B</div>
                <span class="mobile-header-title">@yield('header-title', 'Mon Espace')</span>
            </div>

            <div class="header-actions">
                @auth
                <!-- Bouton Déconnexion Unique -->
                <button type="button" class="btn-logout-header" id="btn-logout-trigger" title="Se déconnecter">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span class="logout-label">Sortir</span>
                </button>
                @endauth
            </div>
        </div>
    </header>

    <!-- Contenu Principal -->
    <main class="main-content" id="main-content">
        <div class="page-content" id="page-content">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
                <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-error alert-dismissible">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span>{{ session('error') }}</span>
                <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
            </div>
            @endif

            <!-- Vue du Portefeuille - Intégration données réelles -->
            <!-- Vue du Portefeuille - Intégration données réelles -->
            @if(request()->routeIs('client.dashboard') && isset($financialSummary))
            <div class="wallet-card">
                <div class="wallet-header">
                    <div>
                        <div class="wallet-label">Solde disponible</div>
                        <div class="wallet-balance">
                            {{ $financialSummary['formatted_balance'] }}
                        </div>
                        @if(!$financialSummary['has_wallet'])
                        <div style="font-size: 0.75rem; opacity: 0.8; margin-top: 4px;">
                            Portefeuille non activé
                        </div>
                        @endif
                    </div>
                    <div class="wallet-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="28" height="28">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                </div>

                <div class="wallet-stats">
                    <div class="wallet-stat">
                        <span class="wallet-stat-value">{{ $stats['active_requests'] ?? 0 }}</span>
                        <span class="wallet-stat-label">Demandes actives</span>
                    </div>
                    <div class="wallet-stat">
                        <span class="wallet-stat-value">{{ $stats['success_rate']['value'] ?? 0 }}%</span>
                        <span class="wallet-stat-label">Taux de succès</span>
                    </div>
                </div>

                @if($financialSummary['has_wallet'] && $financialSummary['last_transaction'])
                <div
                    style="margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.2); display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem; opacity: 0.9;">
                    <span>Dernière activité: {{ $financialSummary['last_transaction']['date'] }}</span>
                    <span style="font-weight: 600;">
                        {{ $financialSummary['last_transaction']['type'] === 'credit' ? '+' : '-' }}
                        {{ $financialSummary['last_transaction']['formatted_amount'] }}
                    </span>
                </div>
                @endif
            </div>

            <!-- Alertes dynamiques -->
            @if(isset($alerts) && count($alerts) > 0)
            @foreach($alerts as $alert)
            <div class="alert-premium {{ $alert['type'] }}">
                <div class="alert-icon">
                    @switch($alert['icon'])
                    @case('document')
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    @break
                    @case('draft')
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    @break
                    @case('notification')
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    @break
                    @default
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    @endswitch
                </div>
                <div class="alert-content">
                    <h4>{{ $alert['title'] }}</h4>
                    <p>{{ $alert['message'] }}</p>
                    <a href="{{ $alert['action_url'] }}" class="alert-action">
                        {{ $alert['action_text'] }}
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
            @endforeach
            @endif
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Navigation Mobile Professionnelle -->
    @auth
    @if(!auth()->user()->is_admin && !auth()->user()->is_moderator)
    <nav class="mobile-nav">
        <a href="{{ route('client.dashboard') }}"
            class="mobile-nav-item {{ request()->routeIs('client.dashboard') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            <span>Accueil</span>
        </a>

        <a href="{{ route('client.wallet.show') }}"
            class="mobile-nav-item {{ request()->routeIs('client.wallet.show') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            <span>Portefeuille</span>
        </a>

        <a href="{{ route('client.requests.create') }}" class="mobile-nav-item nav-item-primary">
            <div class="nav-icon-bg">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
            </div>
            <span>Nouveau</span>
        </a>

        <a href="{{ route('client.requests.index') }}"
            class="mobile-nav-item {{ request()->routeIs('client.requests.*') && !request()->routeIs('client.requests.create') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            <span>Demandes</span>
        </a>

        <a href="{{ route('client.profile') }}"
            class="mobile-nav-item {{ request()->routeIs('client.profile') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span>Profil</span>
        </a>
    </nav>
    @endif
    @endauth

    <!-- Modal Déconnexion -->
    <div id="logout-modal" class="modal">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-icon-danger">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="32" height="32">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </div>

            <h3 class="modal-title">Déconnexion sécurisée</h3>
            <p class="modal-text">Vous allez être déconnecté de votre espace client. Voulez-vous continuer ?</p>

            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" id="btn-cancel-logout">
                    Rester connecté
                </button>
                <form action="{{ route('logout') }}" method="POST" id="logout-form" style="margin: 0; flex: 1;">
                    @csrf
                    <button type="submit" class="btn btn-danger" id="btn-confirm-logout">
                        <span class="btn-text">Me déconnecter</span>
                        <span class="btn-loader" style="display: none;">
                            <svg class="spinner" fill="none" viewBox="0 0 24 24" width="16" height="16">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity="0.25" />
                                <path stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                    d="M12 2a10 10 0 0110 10">
                                    <animateTransform attributeName="transform" type="rotate" from="0 12 12"
                                        to="360 12 12" dur="1s" repeatCount="indefinite" />
                                </path>
                            </svg>
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- PWA Prompt iOS -->
    <div id="ios-prompt" class="pwa-prompt-ios">
        <div class="pwa-prompt-content-ios">
            <button class="pwa-prompt-close" id="ios-prompt-close">&times;</button>
            <div style="text-align: center; margin-bottom: 20px;">
                <div
                    style="width: 64px; height: 64px; background: linear-gradient(135deg, var(--primary-600), var(--primary-500)); border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; color: white; font-size: 28px; font-weight: 800; margin-bottom: 16px;">
                    B</div>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--gray-900); margin-bottom: 8px;">Installer
                    BHDM</h3>
                <p style="font-size: 0.875rem; color: var(--gray-500);">Accédez rapidement à votre portefeuille</p>
            </div>
            <div style="background: var(--gray-100); border-radius: 12px; padding: 16px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                    <span
                        style="background: var(--primary-500); color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.875rem; font-weight: 600;">1</span>
                    <span style="font-size: 0.9375rem; color: var(--gray-700);">Appuyez sur
                        <strong>Partager</strong></span>
                    <svg style="margin-left: auto;" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20"
                        height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                    </svg>
                </div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span
                        style="background: var(--primary-500); color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.875rem; font-weight: 600;">2</span>
                    <span style="font-size: 0.9375rem; color: var(--gray-700);">Sélectionnez <strong>"Sur l'écran
                            d'accueil"</strong></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Variables globales
        let deferredPrompt = null;
        let isIos = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        let isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

        // Modal Déconnexion
        function openLogoutModal() {
            const modal = document.getElementById('logout-modal');
            if (!modal) return;
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeLogoutModal() {
            const modal = document.getElementById('logout-modal');
            if (!modal) return;
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }

        // PWA Prompts
        function showIosPrompt() {
            const prompt = document.getElementById('ios-prompt');
            if (prompt && !isStandalone && !localStorage.getItem('iosPromptDismissed')) {
                setTimeout(() => prompt.style.display = 'flex', 2000);
            }
        }

        function hideIosPrompt() {
            const prompt = document.getElementById('ios-prompt');
            if (prompt) {
                prompt.style.display = 'none';
                localStorage.setItem('iosPromptDismissed', 'true');
            }
        }

        function showAndroidPrompt() {
            const prompt = document.getElementById('android-prompt');
            if (prompt && deferredPrompt && !localStorage.getItem('androidPromptDismissed')) {
                setTimeout(() => prompt.style.display = 'block', 3000);
            }
        }

        function hideAndroidPrompt() {
            const prompt = document.getElementById('android-prompt');
            if (prompt) {
                prompt.style.display = 'none';
                localStorage.setItem('androidPromptDismissed', 'true');
            }
        }

        async function installPWA() {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            if (outcome === 'accepted') console.log('PWA installée');
            deferredPrompt = null;
            hideAndroidPrompt();
        }

        // Transitions
        function navigateWithTransition(url) {
            const transition = document.getElementById('page-transition');
            const pageContent = document.getElementById('page-content');
            if (transition) transition.classList.add('active');
            if (pageContent) {
                pageContent.classList.remove('loaded');
                pageContent.style.opacity = '0';
                pageContent.style.transform = 'translateX(-20px)';
            }
            setTimeout(() => window.location.href = url, 300);
        }

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            // Masquer loader
            setTimeout(() => {
                const transition = document.getElementById('page-transition');
                if (transition) transition.classList.remove('active');
            }, 300);

            // Animer contenu
            const pageContent = document.getElementById('page-content');
            if (pageContent) {
                setTimeout(() => pageContent.classList.add('loaded'), 100);
            }

            // Bouton déconnexion
            const logoutTrigger = document.getElementById('btn-logout-trigger');
            if (logoutTrigger) {
                logoutTrigger.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    openLogoutModal();
                });
            }

            // Fermeture modal
            const cancelBtn = document.getElementById('btn-cancel-logout');
            const modalOverlay = document.querySelector('.modal-overlay');
            if (cancelBtn) cancelBtn.addEventListener('click', closeLogoutModal);
            if (modalOverlay) modalOverlay.addEventListener('click', closeLogoutModal);

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeLogoutModal();
            });

            // Soumission formulaire
            const logoutForm = document.getElementById('logout-form');
            const confirmBtn = document.getElementById('btn-confirm-logout');
            if (logoutForm && confirmBtn) {
                logoutForm.addEventListener('submit', function() {
                    confirmBtn.disabled = true;
                    const btnText = confirmBtn.querySelector('.btn-text');
                    const btnLoader = confirmBtn.querySelector('.btn-loader');
                    if (btnText) btnText.style.display = 'none';
                    if (btnLoader) btnLoader.style.display = 'inline-flex';
                    const transition = document.getElementById('page-transition');
                    if (transition) transition.classList.add('active');
                });
            }

            // Navigation transitions
            document.querySelectorAll('.mobile-nav-item').forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if (href && !this.classList.contains('active')) {
                        e.preventDefault();
                        navigateWithTransition(href);
                    }
                });
            });

            // PWA Events
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                deferredPrompt = e;
                showAndroidPrompt();
            });

            if (isIos && !isStandalone) showIosPrompt();

            const iosClose = document.getElementById('ios-prompt-close');
            if (iosClose) iosClose.addEventListener('click', hideIosPrompt);

            // Offline detection
            const offlineBanner = document.getElementById('offline-banner');
            function updateOnlineStatus() {
                if (offlineBanner) {
                    navigator.onLine ? offlineBanner.classList.add('hidden') : offlineBanner.classList.remove('hidden');
                }
            }
            window.addEventListener('online', updateOnlineStatus);
            window.addEventListener('offline', updateOnlineStatus);
            updateOnlineStatus();
        });

        window.addEventListener('appinstalled', () => {
            deferredPrompt = null;
            hideIosPrompt();
            hideAndroidPrompt();
        });
    </script>

    <script src="{{ asset('js/app.js') }}"></script>
    @yield('scripts')
</body>

</html>