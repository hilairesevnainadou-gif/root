<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0f172a">
    <title>Admin - @yield('title', 'BHDM')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="/css/app.css">
    <link rel="stylesheet" href="/css/admin.css">
    @yield('styles')
</head>

<body>
    <div class="admin-layout">

        <!-- Overlay mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="admin-sidebar-header">
                <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-logo">
                    <img src="/images/logo.png" alt="BHDM Logo" class="sidebar-logo-img">
                    <span class="sidebar-logo-text">BHDM Admin</span>
                </a>
            </div>

            <nav class="admin-nav">
                <div class="admin-nav-section">
                    <div class="admin-nav-title">Principal</div>

                    <a href="{{ route('admin.dashboard') }}"
                        class="admin-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </span>
                        <span class="nav-label">Dashboard</span>
                    </a>

                    <a href="{{ route('admin.requests.index') }}"
                        class="admin-nav-item {{ request()->routeIs('admin.requests.*') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </span>
                        <span class="nav-label">Demandes</span>
                    </a>

                    <a href="{{ route('admin.documents.pending') }}"
                        class="admin-nav-item {{ request()->routeIs('admin.documents.*') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </span>
                        <span class="nav-label">Documents</span>
                    </a>

                    <a href="{{ route('admin.wallets.index') }}"
                        class="admin-nav-item {{ request()->routeIs('admin.wallets.*') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-1m-4-4h.01M7 16h.01M3 12h.01M7 8h.01M3 16h.01M3 8h.01M11 12h.01M15 12h.01" />
                            </svg>
                        </span>
                        <span class="nav-label">Wallets</span>
                    </a>


                    <a href="{{ route('admin.typefinancements.documents') }}"
                        class="admin-nav-item {{ request()->routeIs('admin.typefinancements.documents*') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2
                   M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </span>
                        <span class="nav-label">Docs requis</span>
                    </a>
                </div>



                <div class="admin-nav-section">
                    <div class="admin-nav-title">Gestion</div>

                    <a href="{{ route('admin.users.index') }}"
                        class="admin-nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </span>
                        <span class="nav-label">Utilisateurs</span>
                    </a>

                    <a href="{{ route('admin.typefinancements.index') }}"
                        class="admin-nav-item {{ request()->routeIs('admin.typefinancements.*') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <span class="nav-label">Financements</span>
                    </a>

                    <a href="{{ route('admin.typedocs.index') }}"
                        class="admin-nav-item {{ request()->routeIs('admin.typedocs.*') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </span>
                        <span class="nav-label">Types Docs</span>
                    </a>
                </div>
            </nav>

            <!-- User + Logout en bas de sidebar -->
            <div class="admin-sidebar-footer">
                <div class="sidebar-user-info">
                    <div class="sidebar-avatar">{{ strtoupper(substr(auth()->user()->full_name, 0, 2)) }}</div>
                    <div class="sidebar-user-details">
                        <span class="sidebar-user-name">{{ auth()->user()->full_name }}</span>
                        <span class="sidebar-user-role">Administrateur</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="sidebar-logout-form">
                    @csrf
                    <button type="submit" class="sidebar-logout-btn" title="Déconnexion">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <div class="admin-header-left">
                    <button class="menu-toggle" id="menuToggle" aria-label="Menu">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="22" height="22">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h1 class="admin-header-title">@yield('header-title', 'Dashboard')</h1>
                </div>
                <div class="admin-header-right">
                    <span class="header-user-name">{{ auth()->user()->full_name }}</span>
                    <div class="header-avatar">{{ strtoupper(substr(auth()->user()->full_name, 0, 2)) }}</div>
                </div>
            </header>

            <div class="admin-content">
                @if(session('success'))
                <div class="alert alert-success">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ session('success') }}
                </div>
                @endif
                @if(session('error'))
                <div class="alert alert-error">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    {{ session('error') }}
                </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <script src="/js/app.js"></script>
    <script>
        // Toggle sidebar mobile
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('sidebarOverlay');

        menuToggle?.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('visible');
        });

        overlay?.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('visible');
        });
    </script>
    @yield('scripts')
</body>

</html>
