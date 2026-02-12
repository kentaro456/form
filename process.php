<?php
require_once 'security.php';
require_once 'db.php';

start_secure_session();
send_security_headers();
require_post_method();

if (!is_valid_origin()) {
    json_response(['success' => false, 'message' => 'Origine invalide.'], 403);
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    json_response(['success' => false, 'message' => 'Session invalide. Rechargez la page.'], 403);
}

if (!empty($_POST['website'])) {
    json_response(['success' => false, 'message' => 'Requête invalide.'], 400);
}

$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$password = (string) ($_POST['password'] ?? '');
$ipAddress = get_client_ip();

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(['success' => false, 'message' => 'Identifiants invalides.'], 401);
}

if (strlen($password) < 8 || strlen($password) > 128) {
    json_response(['success' => false, 'message' => 'Identifiants invalides.'], 401);
}

try {
    $maxAttempts = 5;

    $rateStmt = $pdo->prepare(
        "SELECT COUNT(*) AS attempts
         FROM login_attempts
         WHERE ip_address = :ip
           AND attempted_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
           AND success = 0"
    );
    $rateStmt->bindValue(':ip', $ipAddress, PDO::PARAM_STR);
    $rateStmt->execute();
    $ipAttempts = (int) $rateStmt->fetchColumn();

    $emailRateStmt = $pdo->prepare(
        "SELECT COUNT(*) AS attempts
         FROM login_attempts
         WHERE email = :email
           AND attempted_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
           AND success = 0"
    );
    $emailRateStmt->bindValue(':email', $email, PDO::PARAM_STR);
    $emailRateStmt->execute();
    $emailAttempts = (int) $emailRateStmt->fetchColumn();

    if ($ipAttempts >= $maxAttempts || $emailAttempts >= $maxAttempts) {
        json_response(['success' => false, 'message' => 'Trop de tentatives. Réessayez dans 15 minutes.'], 429);
    }

    $stmt = $pdo->prepare("SELECT id, email, password_hash FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    $isValid = $user && password_verify($password, $user['password_hash']);

    $logStmt = $pdo->prepare(
        "INSERT INTO login_attempts (email, ip_address, success)
         VALUES (:email, :ip, :success)"
    );
    $logStmt->execute([
        ':email' => $email,
        ':ip' => $ipAddress,
        ':success' => $isValid ? 1 : 0,
    ]);

    if (!$isValid) {
        usleep(random_int(200000, 500000));
        json_response(['success' => false, 'message' => 'Identifiants invalides.'], 401);
    }

    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        $rehashStmt = $pdo->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
        $rehashStmt->execute([
            ':hash' => password_hash($password, PASSWORD_DEFAULT),
            ':id' => (int) $user['id'],
        ]);
    }

    session_regenerate_id(true);
    $_SESSION['authenticated'] = true;
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['login_at'] = time();
    rotate_csrf_token();

    json_response([
        'success' => true,
        'message' => 'Connexion réussie.',
        'redirect' => 'dashboard.php',
    ]);
} catch (PDOException $e) {
    error_log('Login Error: ' . $e->getMessage());
    json_response(['success' => false, 'message' => 'Erreur serveur.'], 500);
}
