<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="BHDM">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'BHDM')</title>

    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('icons/icon-72x72.png') }}">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pwa.css') }}">
    <link rel="stylesheet" href="{{ asset('css/transitions.css') }}">

    @yield('styles')
</head>
<body>
    <!-- Page Transition Overlay -->
    <div id="page-transition" class="page-transition">
        <div class="transition-spinner">
            <svg viewBox="0 0 50 50">
                <circle cx="25" cy="25" r="20" fill="none" stroke="#2563eb" stroke-width="4" stroke-linecap="round">
                    <animate attributeName="stroke-dasharray" values="1,150;90,150;90,150" dur="1.5s" repeatCount="indefinite"/>
                    <animate attributeName="stroke-dashoffset" values="0;-35;-124" dur="1.5s" repeatCount="indefinite"/>
                </circle>
            </svg>
        </div>
    </div>

    <!-- Offline Banner -->
    <div id="offline-banner" class="offline-banner hidden">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414"/>
        </svg>
        <span>Hors ligne - Mode limité</span>
    </div>

    <!-- Header Mobile -->
    <header class="mobile-header">
        <div class="mobile-header-content">
            <div class="mobile-header-brand">
                <div class="header-logo">BHDM</div>
                <span class="mobile-header-title">@yield('header-title', 'Tableau de bord')</span>
            </div>

            <div class="header-actions">
                @yield('header-action')

                @auth
                    <!-- Bouton Déconnexion -->
                    <button type="button" class="btn-logout-header" id="btn-logout-trigger" title="Se déconnecter">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error alert-dismissible">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Navigation Mobile -->
    @auth
        @if(!auth()->user()->is_admin && !auth()->user()->is_moderator)
            <nav class="mobile-nav">
                <a href="{{ route('client.dashboard') }}" class="mobile-nav-item {{ request()->routeIs('client.dashboard') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Accueil</span>
                </a>
                <a href="{{ route('client.financements.index') }}" class="mobile-nav-item {{ request()->routeIs('client.financements.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Financements</span>
                </a>
                <a href="{{ route('client.requests.create') }}" class="mobile-nav-item nav-item-primary">
                    <div class="nav-icon-bg">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <span>Demander</span>
                </a>
                <a href="{{ route('client.requests.index') }}" class="mobile-nav-item {{ request()->routeIs('client.requests.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    <span>Mes demandes</span>
                </a>
                <a href="{{ route('client.profile') }}" class="mobile-nav-item {{ request()->routeIs('client.profile') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Profil</span>
                </a>
            </nav>
        @endif
    @endauth

    <!-- Modal Déconnexion -->
    <div id="logout-modal" class="modal" style="display: none;">
        <div class="modal-overlay"></div>
        <div class="modal-content modal-small">
            <div class="modal-icon modal-icon-danger">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="32" height="32">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </div>

            <h3 class="modal-title">Se déconnecter ?</h3>
            <p class="modal-text">Vous allez être redirigé vers la page de connexion.</p>

            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" id="btn-cancel-logout">
                    Annuler
                </button>
                <form action="{{ route('logout') }}" method="POST" id="logout-form" style="margin: 0; flex: 1;">
                    @csrf
                    <button type="submit" class="btn btn-danger" id="btn-confirm-logout" style="width: 100%;">
                        <span class="btn-text">Déconnexion</span>
                        <span class="btn-loader" style="display: none;">
                            <svg class="spinner" fill="none" viewBox="0 0 24 24" width="16" height="16">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity="0.25"/>
                                <path stroke="currentColor" stroke-width="3" stroke-linecap="round" d="M12 2a10 10 0 0110 10">
                                    <animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/>
                                </path>
                            </svg>
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- PWA Prompt iOS -->
    <div id="ios-prompt" class="pwa-prompt-ios" style="display: none;">
        <div class="pwa-prompt-content-ios">
            <button class="pwa-prompt-close" id="ios-prompt-close">×</button>
            <div class="pwa-prompt-icon">
                <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #2563eb, #1d4ed8); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: bold;">B</div>
            </div>
            <h3 class="pwa-prompt-title">Installer BHDM</h3>
            <p class="pwa-prompt-subtitle">Accédez rapidement à vos financements</p>

            <div class="ios-steps">
                <div class="ios-step">
                    <span class="step-num">1</span>
                    <span>Appuyez sur <strong>Partager</strong></span>
                    <svg style="display: inline-block; vertical-align: middle; margin-left: 4px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                    </svg>
                </div>
                <div class="ios-step">
                    <span class="step-num">2</span>
                    <span>Puis <strong>"Sur l'écran d'accueil"</strong></span>
                </div>
            </div>
        </div>
    </div>

    <!-- PWA Prompt Android -->
    <div id="android-prompt" class="pwa-prompt-android" style="display: none;">
        <div class="pwa-prompt-content-android">
            <div class="pwa-prompt-info">
                <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #2563eb, #1d4ed8); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; font-weight: bold;">B</div>
                <div>
                    <div style="font-weight: 700; color: #1f2937;">Installer BHDM</div>
                    <div style="font-size: 0.875rem; color: #6b7280;">Application web progressive</div>
                </div>
            </div>
            <div class="pwa-prompt-actions-android">
                <button class="btn-pwa-dismiss" id="android-prompt-dismiss">Plus tard</button>
                <button class="btn-pwa-install" id="android-prompt-install">Installer</button>
            </div>
        </div>
    </div>

    <!-- Scripts INLINE - Définis avant @yield('scripts') -->
    <script>
        // ============================================
        // VARIABLES GLOBALES
        // ============================================
        let deferredPrompt = null;
        let isIos = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        let isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

        // ============================================
        // FONCTIONS MODAL DÉCONNEXION
        // ============================================
        function openLogoutModal() {
            console.log('Opening logout modal');
            const modal = document.getElementById('logout-modal');
            if (!modal) {
                console.error('Modal not found');
                return;
            }

            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';

            // Animation
            const content = modal.querySelector('.modal-content');
            if (content) {
                content.style.animation = 'none';
                setTimeout(() => {
                    content.style.animation = 'modalSlideUp 0.3s ease';
                }, 10);
            }
        }

        function closeLogoutModal() {
            const modal = document.getElementById('logout-modal');
            if (!modal) return;

            const content = modal.querySelector('.modal-content');
            if (content) {
                content.style.animation = 'modalSlideDown 0.3s ease';
            }

            setTimeout(() => {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }, 280);
        }

        // ============================================
        // FONCTIONS PWA PROMPTS
        // ============================================
        function showIosPrompt() {
            const prompt = document.getElementById('ios-prompt');
            if (prompt && !isStandalone && !localStorage.getItem('iosPromptDismissed')) {
                setTimeout(() => {
                    prompt.style.display = 'flex';
                }, 2000);
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
                setTimeout(() => {
                    prompt.style.display = 'block';
                }, 3000);
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
            if (!deferredPrompt) {
                alert('Installation non disponible');
                return;
            }

            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;

            if (outcome === 'accepted') {
                console.log('PWA installed');
            }

            deferredPrompt = null;
            hideAndroidPrompt();
        }

        // ============================================
        // TRANSITIONS DE PAGE
        // ============================================
        function navigateWithTransition(url, direction = 'left') {
            const transition = document.getElementById('page-transition');
            const pageContent = document.getElementById('page-content');

            if (transition) transition.classList.add('active');

            if (pageContent) {
                pageContent.style.opacity = '0';
                pageContent.style.transform = direction === 'left' ? 'translateX(-20px)' : 'translateX(20px)';
                pageContent.style.transition = 'all 0.3s ease';
            }

            setTimeout(() => {
                window.location.href = url;
            }, 300);
        }

        // ============================================
        // INITIALISATION AU CHARGEMENT
        // ============================================
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM Loaded - Initializing...');

            // 1. Masquer le loader de transition
            setTimeout(() => {
                const transition = document.getElementById('page-transition');
                if (transition) transition.classList.remove('active');
            }, 300);

            // 2. Animer l'entrée du contenu
            const pageContent = document.getElementById('page-content');
            if (pageContent) {
                pageContent.style.opacity = '0';
                pageContent.style.transform = 'translateY(20px)';

                setTimeout(() => {
                    pageContent.style.transition = 'all 0.4s ease';
                    pageContent.style.opacity = '1';
                    pageContent.style.transform = 'translateY(0)';
                }, 100);
            }

            // 3. BOUTON DÉCONNEXION - Event Listener
            const logoutTrigger = document.getElementById('btn-logout-trigger');
            if (logoutTrigger) {
                logoutTrigger.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    openLogoutModal();
                });
                console.log('Logout button listener attached');
            } else {
                console.error('Logout trigger button not found');
            }

            // 4. FERMETURE MODAL
            const cancelBtn = document.getElementById('btn-cancel-logout');
            const modalOverlay = document.querySelector('.modal-overlay');

            if (cancelBtn) {
                cancelBtn.addEventListener('click', closeLogoutModal);
            }

            if (modalOverlay) {
                modalOverlay.addEventListener('click', closeLogoutModal);
            }

            // Fermer avec Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeLogoutModal();
                }
            });

            // 5. SOUMISSION FORMULAIRE DÉCONNEXION
            const logoutForm = document.getElementById('logout-form');
            const confirmBtn = document.getElementById('btn-confirm-logout');

            if (logoutForm && confirmBtn) {
                logoutForm.addEventListener('submit', function(e) {
                    const btnText = confirmBtn.querySelector('.btn-text');
                    const btnLoader = confirmBtn.querySelector('.btn-loader');

                    confirmBtn.disabled = true;
                    if (btnText) btnText.style.display = 'none';
                    if (btnLoader) btnLoader.style.display = 'inline-flex';

                    // Afficher transition
                    const transition = document.getElementById('page-transition');
                    if (transition) transition.classList.add('active');
                });
            }

            // 6. LIENS AVEC TRANSITION
            document.querySelectorAll('.mobile-nav-item').forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if (href && !this.classList.contains('active')) {
                        e.preventDefault();
                        navigateWithTransition(href);
                    }
                });
            });

            // 7. PWA - Capturer beforeinstallprompt
            window.addEventListener('beforeinstallprompt', (e) => {
                console.log('beforeinstallprompt captured');
                e.preventDefault();
                deferredPrompt = e;
                showAndroidPrompt();
            });

            // 8. Afficher prompt iOS si applicable
            if (isIos && !isStandalone) {
                showIosPrompt();
            }

            // 9. Boutons PWA
            const iosClose = document.getElementById('ios-prompt-close');
            const androidDismiss = document.getElementById('android-prompt-dismiss');
            const androidInstall = document.getElementById('android-prompt-install');

            if (iosClose) iosClose.addEventListener('click', hideIosPrompt);
            if (androidDismiss) androidDismiss.addEventListener('click', hideAndroidPrompt);
            if (androidInstall) androidInstall.addEventListener('click', installPWA);

            // 10. Détection hors ligne
            const offlineBanner = document.getElementById('offline-banner');

            function updateOnlineStatus() {
                if (navigator.onLine) {
                    if (offlineBanner) offlineBanner.classList.add('hidden');
                } else {
                    if (offlineBanner) offlineBanner.classList.remove('hidden');
                }
            }

            window.addEventListener('online', updateOnlineStatus);
            window.addEventListener('offline', updateOnlineStatus);
            updateOnlineStatus();

            console.log('Initialization complete');
        });

        // Masquer prompts si déjà installé
        window.addEventListener('appinstalled', () => {
            console.log('PWA was installed');
            deferredPrompt = null;
            hideIosPrompt();
            hideAndroidPrompt();
        });
    </script>

    <!-- Scripts additionnels -->
    <script src="{{ asset('js/app.js') }}"></script>
    @yield('scripts')
</body>
</html>
