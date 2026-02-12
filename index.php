<?php
require_once 'security.php';
start_secure_session();
send_security_headers();

if (!empty($_SESSION['authenticated'])) {
    header('Location: dashboard.php');
    exit;
}

$csrfToken = get_csrf_token();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Sécurisée</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="glass-card">
            <div class="header">
                <h1>Login Sécurisé</h1>
                <p>Accès réservé, protection anti brute-force active.</p>
            </div>
            <form id="loginForm" action="process.php" method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

                <div class="visually-hidden">
                    <label for="website">Website</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="vous@exemple.com" maxlength="254" required>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-control" minlength="8" maxlength="128" required>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <span class="btn-text">Se connecter</span>
                    <div class="spinner"></div>
                </button>
            </form>

            <p class="secondary-link">
                Pas encore de compte ? <a href="register.php">Créer un compte de test</a>
            </p>

            <div id="response-message" aria-live="polite"></div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
