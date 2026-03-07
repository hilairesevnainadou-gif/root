<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation de mot de passe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            padding: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #1e3a5f;
            font-size: 24px;
            margin: 0;
        }
        .content {
            color: #333333;
            font-size: 16px;
            line-height: 1.6;
        }
        .button {
            display: inline-block;
            background: #1e3a5f;
            color: #ffffff;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 4px;
            margin: 20px 0;
            font-weight: 600;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            font-size: 14px;
            color: #64748b;
        }
        .url-fallback {
            word-break: break-all;
            color: #1e3a5f;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Réinitialisation de mot de passe</h1>
        </div>

        <div class="content">
            <p>Bonjour,</p>

            <p>Vous avez demandé la réinitialisation de votre mot de passe pour votre compte BHDM.</p>

            <p>Cliquez sur le bouton ci-dessous pour créer un nouveau mot de passe :</p>

            <center>
                <a href="{{ $resetUrl }}" class="button">Réinitialiser mon mot de passe</a>
            </center>

            <p>Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur :</p>
            <p class="url-fallback">{{ $resetUrl }}</p>

            <p>Ce lien expirera dans 60 minutes.</p>

            <p>Si vous n'avez pas fait cette demande, ignorez simplement cet email.</p>
        </div>

        <div class="footer">
            <p>Cordialement,<br>L'équipe BHDM</p>
            <p style="font-size: 12px; color: #94a3b8;">
                Cet email a été envoyé automatiquement. Merci de ne pas y répondre.
            </p>
        </div>
    </div>
</body>
</html>
