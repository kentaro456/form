<?php
require_once 'security.php';
start_secure_session();
send_security_headers();
require_authenticated_user();
$csrfToken = get_csrf_token();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Privé</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="glass-card">
            <div class="header">
                <h1>Bienvenue</h1>
                <p>Session active pour <?php echo htmlspecialchars($_SESSION['user_email'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <p class="dashboard-text">Vous êtes connecté dans une zone protégée par session sécurisée.</p>
            <form action="logout.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit" class="btn-submit">
                    <span class="btn-text">Se déconnecter</span>
                </button>
            </form>
        </div>
    </div>
</body>
</html>
