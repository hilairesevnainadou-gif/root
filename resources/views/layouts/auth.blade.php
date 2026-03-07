<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#2563eb">
    <title>@yield('title', 'BHDM')</title>

    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
    <link rel="stylesheet" href="/css/app.css">
    <link rel="stylesheet" href="/css/mobile.css">
    <link rel="stylesheet" href="/css/pwa.css">

    @yield('styles')

    <style>
        .auth-layout {
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            padding: 1rem;
        }

        .auth-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);
            -webkit-overflow-scrolling: touch;
        }

        .auth-logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .auth-logo h1 {
            color: #2563eb;
            font-size: 1.875rem;
            font-weight: 700;
        }

        .auth-logo p {
            color: #64748b;
            font-size: 0.875rem;
        }

        /* Scrollbar styling for auth card */
        .auth-card::-webkit-scrollbar {
            width: 6px;
        }

        .auth-card::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }

        .auth-card::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="auth-layout">
        <div class="auth-card">
            <div class="auth-logo">
                <h1>BHDM</h1>
                <p>Plateforme de financement participatif</p>
            </div>

            @yield('content')
        </div>
    </div>

    <script src="/js/app.js"></script>
    @yield('scripts')
</body>
</html>
