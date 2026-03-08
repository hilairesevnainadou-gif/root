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
    <meta name="description" content="BHDM - Votre portefeuille digital sécurisé">

    <title>@yield('title', 'Espace Client - BHDM')</title>

    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('icons/icon-72x72.png') }}">

    <!-- Styles Uniques -->
    <link rel="stylesheet" href="{{ asset('css/bhdm-complete.css') }}">

    @yield('styles')
</head>

<body class="bhdm-app">
    <!-- Page Transition Overlay -->
    <div id="page-transition" class="page-transition">
        <div class="transition-spinner">
            <svg viewBox="0 0 50 50">
                <circle cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="4"
                    stroke-linecap="round">
                    <animate attributeName="stroke-dasharray" values="1,150;90,150;90,150" dur="1.5s"
                        repeatCount="indefinite" />
                    <animate attributeName="stroke-dashoffset" values="0;-35;-124" dur="1.5s"
                        repeatCount="indefinite" />
                </circle>
            </svg>
        </div>
    </div>

    <!-- Auth Transition (pour la connexion) -->
    <div id="auth-transition" class="auth-transition">
        <div class="auth-transition-content">
            <div class="auth-logo"><img src="{{ asset('images/logo.png') }}" alt="BHDM Logo" class="auth-logo-img">
            </div>
            <div class="auth-welcome">Bienvenue sur BHDM</div>
            <div class="auth-loading-bar">
                <div class="auth-loading-progress"></div>
            </div>
        </div>
    </div>

    <!-- Offline Banner -->
    <div id="offline-banner" class="offline-banner">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414" />
        </svg>
        <span>Connexion interrompue - Mode hors ligne</span>
    </div>

    <!-- Header Premium -->
    <header class="mobile-header">
        <div class="mobile-header-content">
            <div class="mobile-header-brand">
                <img src="{{ asset('images/logo.png') }}" alt="BHDM Logo" class="header-logo" width="40" height="40">
                <span class="mobile-header-title">@yield('header-title', 'Mon Espace')</span>
            </div>

            <div class="header-actions">
                @auth
                <button type="button" class="btn-logout-header" id="btn-logout-trigger" title="Se déconnecter">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span class="logout-label">Sortir</span>
                </button>
                @endauth
            </div>
        </div>
    </header>

    <!-- Contenu Principal avec animation de page -->
    <main class="main-content" id="main-content">
        <div class="page-content" id="page-content">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible alert-enter">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
                <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-error alert-dismissible alert-enter">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span>{{ session('error') }}</span>
                <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
            </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Navigation Mobile avec animations -->
    @auth
    @if(!auth()->user()->is_admin && !auth()->user()->is_moderator)
    <nav class="mobile-nav">
        <a href="{{ route('client.dashboard') }}"
            class="mobile-nav-item {{ request()->routeIs('client.dashboard') ? 'active' : '' }}"
            data-transition="slide-left">
            <div class="nav-icon-wrapper">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
            </div>
            <span>Accueil</span>
        </a>

        <a href="{{ route('client.wallet.show') }}"
            class="mobile-nav-item {{ request()->routeIs('client.wallet.show') ? 'active' : '' }}"
            data-transition="slide-up">
            <div class="nav-icon-wrapper">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
            </div>
            <span>Portefeuille</span>
        </a>

        <a href="{{ route('client.requests.create') }}" class="mobile-nav-item nav-item-primary"
            data-transition="scale-up">
            <div class="nav-icon-bg">
                <div class="nav-pulse-ring"></div>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
            </div>
            <span>Nouveau</span>
        </a>

        <a href="{{ route('client.requests.index') }}"
            class="mobile-nav-item {{ request()->routeIs('client.requests.*') && !request()->routeIs('client.requests.create') ? 'active' : '' }}"
            data-transition="slide-right">
            <div class="nav-icon-wrapper">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
            </div>
            <span>Demandes</span>
        </a>

        <a href="{{ route('client.profile') }}"
            class="mobile-nav-item {{ request()->routeIs('client.profile') ? 'active' : '' }}" data-transition="fade">
            <div class="nav-icon-wrapper">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
            <span>Profil</span>
        </a>
    </nav>
    @endif
    @endauth


    <!-- Modal Déconnexion -->
    <div id="logout-modal" class="modal-logout">
        <div class="modal-logout-backdrop" id="logout-backdrop"></div>
        <div class="modal-logout-content">
            <div class="modal-logout-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </div>

            <h3 class="modal-logout-title">Déconnexion sécurisée</h3>
            <p class="modal-logout-text">
                Vous allez être déconnecté de votre espace client BHDM. Toutes vos données sont sécurisées.
            </p>

            <div class="modal-logout-actions">
                <button type="button" class="btn btn-secondary" id="btn-cancel-logout">
                    Rester connecté
                </button>

                {{-- CORRECTION: Formulaire avec token CSRF explicite --}}
                <form action="{{ route('logout') }}" method="POST" id="logout-form"
                    style="margin: 0; flex: 1; display: flex;">
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

    <!-- iOS Install Prompt -->
    <div id="ios-prompt" class="ios-prompt">
        <div class="ios-prompt-content">
            <button class="ios-prompt-close" id="ios-prompt-close">&times;</button>
            <div class="ios-prompt-header">
                <div class="ios-app-icon">B</div>
                <h3>Installer BHDM</h3>
                <p>Ajoutez à l'écran d'accueil</p>
            </div>
            <div class="ios-prompt-steps">
                <div class="ios-step">
                    <span class="ios-step-number">1</span>
                    <span>Appuyez sur <strong>Partager</strong></span>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                    </svg>
                </div>
                <div class="ios-step">
                    <span class="ios-step-number">2</span>
                    <span>Sélectionnez <strong>"Sur l'écran d'accueil"</strong></span>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript pour les animations -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('logout-modal');
    const btnTrigger = document.getElementById('btn-logout-trigger');
    const btnCancel = document.getElementById('btn-cancel-logout');
    const btnConfirm = document.getElementById('btn-confirm-logout');
    const backdrop = document.getElementById('logout-backdrop');
    const form = document.getElementById('logout-form');
    const modalContent = modal.querySelector('.modal-logout-content');

    // Ouvrir le modal
    function openModal() {
        modal.style.display = 'flex';
        modal.offsetHeight; // Force reflow
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        btnCancel.focus();
    }

    // Fermer le modal avec animation
    function closeModal() {
        modal.classList.remove('active');
        modal.classList.add('closing');

        setTimeout(() => {
            modal.style.display = 'none';
            modal.classList.remove('closing');
            document.body.style.overflow = '';
        }, 300);
    }

    // Gestion du clic sur le bouton de déconnexion
    if (btnTrigger) {
        btnTrigger.addEventListener('click', function(e) {
            e.preventDefault();
            openModal();
        });
    }

    // Annuler la déconnexion
    if (btnCancel) {
        btnCancel.addEventListener('click', closeModal);
    }

    // Fermer en cliquant sur le backdrop
    if (backdrop) {
        backdrop.addEventListener('click', closeModal);
    }

    // Fermer avec la touche Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            closeModal();
        }
    });

    // CORRECTION: Gestion de la soumission du formulaire
    if (form) {
        form.addEventListener('submit', function(e) {
            // Empêcher la soumission multiple
            if (btnConfirm.disabled) {
                e.preventDefault();
                return;
            }

            // Vérifier que le token CSRF est présent
            const csrfToken = document.querySelector('input[name="_token"]')?.value;
            if (!csrfToken) {
                e.preventDefault();
                console.error('CSRF token manquant');
                alert('Erreur de sécurité. Veuillez recharger la page.');
                return;
            }

            // Activer l'état de chargement
            btnConfirm.disabled = true;
            btnConfirm.classList.add('loading');

            // Feedback visuel
            modalContent.style.transform = 'scale(0.98)';

            // Laisser le formulaire se soumettre normalement
            // Pas de e.preventDefault() ici !
        });
    }

    // Empêcher la fermeture si on clique sur le contenu
    modalContent.addEventListener('click', function(e) {
        e.stopPropagation();
    });
});
    </script>

    @yield('scripts')
</body>

</html>
