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
    <title>Créer un Compte</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="glass-card">
            <div class="header">
                <h1>Créer un Compte</h1>
                <p>Mot de passe fort obligatoire.</p>
            </div>

            <form id="registerForm" action="register_process.php" method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

                <div class="visually-hidden">
                    <label for="website">Website</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" maxlength="254" pattern="^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$" required>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-control" minlength="10" maxlength="128" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{10,128}" required>
                </div>

                <div class="form-group">
                    <label for="password_confirm" class="form-label">Confirmer le mot de passe</label>
                    <input type="password" id="password_confirm" name="password_confirm" class="form-control" minlength="10" maxlength="128" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{10,128}" required>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <span class="btn-text">Créer le compte</span>
                    <div class="spinner"></div>
                </button>
            </form>

            <p class="secondary-link">
                Déjà inscrit ? <a href="index.php">Se connecter</a>
            </p>

            <div id="response-message" aria-live="polite"></div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
