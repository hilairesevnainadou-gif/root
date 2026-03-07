<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#1e3a5f">
    <title>@yield('title', 'BHDM - Plateforme de Financement')</title>

    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
    <link rel="stylesheet" href="/css/app.css">
    <link rel="stylesheet" href="/css/mobile.css">
    <link rel="stylesheet" href="/css/pwa.css">

    @yield('styles')

    <style>
        :root {
            --primary: #1e3a5f;
            --primary-light: #2c5282;
            --secondary: #2563eb;
            --accent: #3b82f6;
            --success: #059669;
            --background: #f8fafc;
            --surface: #ffffff;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--background);
            color: var(--text-primary);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ============================================
           MODAL DE PRÉSENTATION - PREMIÈRE VISITE
           ============================================ */
        .presentation-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            overflow-y: auto;
        }

        .presentation-overlay.hidden {
            display: none;
        }

        .presentation-dialog {
            background: var(--surface);
            border-radius: 8px;
            max-width: 800px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--border);
        }

        .presentation-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            padding: 2.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .presentation-header h1 {
            font-size: 1.875rem;
            font-weight: 700;
            letter-spacing: -0.025em;
            margin-bottom: 0.5rem;
        }

        .presentation-header p {
            font-size: 1rem;
            opacity: 0.9;
            font-weight: 400;
        }

        .presentation-content {
            padding: 2.5rem;
        }

        .content-section {
            margin-bottom: 2rem;
        }

        .content-section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--border);
        }

        .content-text {
            color: var(--text-secondary);
            line-height: 1.7;
            font-size: 0.9375rem;
        }

        .content-text strong {
            color: var(--primary);
            font-weight: 600;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 1rem 0;
        }

        .feature-list li {
            position: relative;
            padding-left: 1.5rem;
            margin-bottom: 0.75rem;
            color: var(--text-secondary);
            font-size: 0.9375rem;
            line-height: 1.6;
        }

        .feature-list li::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0.5rem;
            width: 6px;
            height: 6px;
            background-color: var(--success);
            border-radius: 50%;
        }

        .acceptance-section {
            background: #f1f5f9;
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 1.5rem;
            margin: 2rem 0;
        }

        .checkbox-field {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .checkbox-field input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-top: 2px;
            accent-color: var(--primary);
            cursor: pointer;
            flex-shrink: 0;
        }

        .checkbox-field label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            line-height: 1.6;
            cursor: pointer;
            user-select: none;
        }

        .checkbox-field label strong {
            color: var(--primary);
            font-weight: 600;
        }

        .action-button {
            width: 100%;
            padding: 0.875rem 1.5rem;
            background: #94a3b8;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: not-allowed;
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .action-button.enabled {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            cursor: pointer;
            box-shadow: var(--shadow-md);
        }

        .action-button.enabled:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        /* ============================================
           INTERFACE PRINCIPALE
           ============================================ */
        .main-container {
            display: none;
            min-height: 100vh;
            flex-direction: column;
        }

        .main-container.visible {
            display: flex;
        }

        /* Header */
        .app-header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow-sm);
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }

        .brand-logo {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.125rem;
        }

        .brand-text {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
            letter-spacing: -0.025em;
        }

        .nav-menu {
            display: flex;
            gap: 2rem;
            list-style: none;
        }

        .nav-menu a {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: color 0.2s;
            position: relative;
        }

        .nav-menu a:hover {
            color: var(--primary);
        }

        .nav-menu a::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: width 0.3s;
        }

        .nav-menu a:hover::after {
            width: 100%;
        }

        /* Auth Layout */
        .auth-section {
            min-height: calc(100vh - 73px);
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            padding: 2rem;
        }

        .auth-box {
            background: var(--surface);
            border-radius: 8px;
            padding: 2.5rem;
            width: 100%;
            max-width: 480px;
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--border);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-header h2 {
            color: var(--primary);
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .auth-header p {
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        /* Info Section */
        .info-section {
            background: var(--background);
            padding: 4rem 2rem;
        }

        .info-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-header h2 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1rem;
            letter-spacing: -0.025em;
        }

        .section-header p {
            color: var(--text-secondary);
            font-size: 1.125rem;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.7;
        }

        .process-timeline {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            margin: 3rem 0;
            position: relative;
        }

        .process-timeline::before {
            content: '';
            position: absolute;
            top: 24px;
            left: 12.5%;
            right: 12.5%;
            height: 2px;
            background: var(--border);
        }

        .timeline-item {
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .timeline-number {
            width: 50px;
            height: 50px;
            background: var(--surface);
            border: 2px solid var(--primary);
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.125rem;
            font-weight: 700;
            margin: 0 auto 1rem;
        }

        .timeline-item.active .timeline-number {
            background: var(--primary);
            color: white;
        }

        .timeline-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .timeline-desc {
            font-size: 0.875rem;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-top: 3rem;
        }

        .feature-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1.5rem;
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        .feature-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 0.75rem;
        }

        .feature-desc {
            font-size: 0.875rem;
            color: var(--text-secondary);
            line-height: 1.6;
        }

        /* Footer */
        .app-footer {
            background: var(--primary);
            color: white;
            padding: 2rem;
            margin-top: auto;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
        }

        .footer-text {
            font-size: 0.875rem;
            opacity: 0.9;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .process-timeline {
                grid-template-columns: repeat(2, 1fr);
            }

            .process-timeline::before {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .presentation-content {
                padding: 1.5rem;
            }

            .presentation-header {
                padding: 1.5rem;
            }

            .presentation-header h1 {
                font-size: 1.5rem;
            }

            .nav-menu {
                display: none;
            }

            .process-timeline {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .auth-box {
                padding: 1.5rem;
            }

            .info-section {
                padding: 2rem 1rem;
            }
        }

        /* Utility */
        .fade-in {
            animation: fadeIn 0.4s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <!-- ============================================
         MODAL DE PRÉSENTATION - PREMIÈRE VISITE
         ============================================ -->
    <div class="presentation-overlay" id="presentationModal">
        <div class="presentation-dialog">
            <div class="presentation-header">
                <h1>BHDM</h1>
                <p>Plateforme de Financement Participatif</p>
            </div>

            <div class="presentation-content">
                <div class="content-section">
                    <h2 class="section-title">Présentation du Système</h2>
                    <p class="content-text">
                        <strong>BHDM</strong> (Business Help & Development Money) est une plateforme de financement participatif
                        conçue pour accompagner les entrepreneurs et porteurs de projets dans la réalisation de leurs idées innovantes.
                        Notre système met en relation les créateurs de projets avec une communauté d'investisseurs et de contributeurs.
                    </p>
                </div>

                <div class="content-section">
                    <h2 class="section-title">Notre Objectif</h2>
                    <p class="content-text">
                        Faciliter l'accès au financement entrepreneurial en créant un écosystème transparent,
                        sécurisé et accessible où chaque projet viable peut trouver les ressources nécessaires à sa concrétisation.
                    </p>
                </div>

                <div class="content-section">
                    <h2 class="section-title">Fonctionnement de la Plateforme</h2>
                    <ul class="feature-list">
                        <li>Soumission de projets avec business plan et objectifs de financement détaillés</li>
                        <li>Analyse et validation par notre comité d'experts indépendants</li>
                        <li>Mise en ligne de la campagne et ouverture des contributions</li>
                        <li>Suivi en temps réel de l'avancement des collectes de fonds</li>
                        <li>Accompagnement personnalisé tout au long du processus de financement</li>
                        <li>Sécurisation des transactions et protection des données personnelles</li>
                    </ul>
                </div>

                <div class="content-section">
                    <h2 class="section-title">Notre Engagement</h2>
                    <p class="content-text">
                        Nous nous engageons à maintenir les plus hauts standards de <strong>transparence</strong> dans la gestion des fonds,
                        à garantir la <strong>sécurité financière</strong> de toutes les transactions, et à fournir un
                        <strong>accompagnement professionnel</strong> pour maximiser les chances de succès de chaque projet.
                    </p>
                </div>

                <div class="acceptance-section">
                    <div class="checkbox-field">
                        <input type="checkbox" id="acceptTerms" name="acceptTerms">
                        <label for="acceptTerms">
                            <strong>J'ai lu et j'approuve</strong> les conditions d'utilisation de la plateforme BHDM.
                            Je comprends que ce système est dédié au financement participatif de projets entrepreneuriaux
                            et j'accepte de respecter l'ensemble des règles éthiques et légales en vigueur.
                        </label>
                    </div>
                </div>

                <button type="button" class="action-button" id="btnApprove" disabled>
                    Accéder à la Plateforme
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================
         INTERFACE PRINCIPALE
         ============================================ -->
    <div class="main-container" id="mainInterface">

        <!-- Header -->
        <header class="app-header">
            <div class="header-content">
                <a href="#" class="brand">
                    <div class="brand-logo">BH</div>
                    <span class="brand-text">BHDM</span>
                </a>

                <nav>
                    <ul class="nav-menu">
                        <li><a href="#accueil">Accueil</a></li>
                        <li><a href="#projets">Projets</a></li>
                        <li><a href="#fonctionnement">Fonctionnement</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </nav>
            </div>
        </header>

        <!-- Section Authentification -->
        <section class="auth-section" id="accueil">
            <div class="auth-box">
                <div class="auth-header">
                    <h2>Connexion à BHDM</h2>
                    <p>Plateforme de financement participatif</p>
                </div>

                @yield('content')
            </div>
        </section>

        <!-- Section Information -->
        <section class="info-section" id="fonctionnement">
            <div class="info-container">
                <div class="section-header">
                    <h2>Système de Financement Participatif</h2>
                    <p>
                        Notre plateforme connecte les porteurs de projets innovants avec les financements
                        nécessaires à leur réalisation à travers un processus transparent et sécurisé.
                    </p>
                </div>

                <div class="process-timeline">
                    <div class="timeline-item active">
                        <div class="timeline-number">1</div>
                        <h3 class="timeline-title">Soumission</h3>
                        <p class="timeline-desc">Dépôt du projet avec business plan et objectifs de financement</p>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-number">2</div>
                        <h3 class="timeline-title">Validation</h3>
                        <p class="timeline-desc">Analyse de faisabilité par notre comité d'experts</p>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-number">3</div>
                        <h3 class="timeline-title">Collecte</h3>
                        <p class="timeline-desc">Ouverture des contributions auprès de la communauté</p>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-number">4</div>
                        <h3 class="timeline-title">Réalisation</h3>
                        <p class="timeline-desc">Versement des fonds et accompagnement du projet</p>
                    </div>
                </div>

                <div class="features-grid">
                    <div class="feature-card">
                        <h4 class="feature-title">Objectif Principal</h4>
                        <p class="feature-desc">
                            Démocratiser l'accès au financement entrepreneurial par un écosystème transparent
                            et accessible aux porteurs de projets innovants.
                        </p>
                    </div>
                    <div class="feature-card">
                        <h4 class="feature-title">Sécurité Garantie</h4>
                        <p class="feature-desc">
                            Transactions sécurisées, protection des données personnelles et vérification
                            rigoureuse de chaque projet soumis sur la plateforme.
                        </p>
                    </div>
                    <div class="feature-card">
                        <h4 class="feature-title">Transparence Totale</h4>
                        <p class="feature-desc">
                            Suivi en temps réel des contributions, reporting détaillé et traçabilité complète
                            des flux financiers à chaque étape.
                        </p>
                    </div>
                    <div class="feature-card">
                        <h4 class="feature-title">Accompagnement Expert</h4>
                        <p class="feature-desc">
                            Support personnalisé et conseils stratégiques pour optimiser la réussite
                            de votre campagne de financement.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="app-footer">
            <div class="footer-content">
                <p class="footer-text">
                    &copy; 2024 BHDM - Business Help & Development Money. Tous droits réservés.
                </p>
            </div>
        </footer>

    </div>

    <script src="/js/app.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('presentationModal');
            const mainInterface = document.getElementById('mainInterface');
            const checkbox = document.getElementById('acceptTerms');
            const btn = document.getElementById('btnApprove');

            // Vérifier acceptation précédente
            if (localStorage.getItem('bhdm_accepted') === 'true') {
                modal.classList.add('hidden');
                mainInterface.classList.add('visible');
                return;
            }

            // Gérer état bouton
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    btn.disabled = false;
                    btn.classList.add('enabled');
                } else {
                    btn.disabled = true;
                    btn.classList.remove('enabled');
                }
            });

            // Gérer approbation
            btn.addEventListener('click', function() {
                if (checkbox.checked) {
                    localStorage.setItem('bhdm_accepted', 'true');
                    localStorage.setItem('bhdm_accepted_date', new Date().toISOString());

                    modal.classList.add('hidden');
                    mainInterface.classList.add('visible');
                    window.scrollTo(0, 0);
                }
            });
        });
    </script>

    @yield('scripts')
</body>
</html>
