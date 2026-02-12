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
$passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

$emailRegex = '/^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/';
$passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{10,128}$/';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match($emailRegex, $email)) {
    json_response(['success' => false, 'message' => 'Email invalide.'], 400);
}

if ($password !== $passwordConfirm) {
    json_response(['success' => false, 'message' => 'Les mots de passe ne correspondent pas.'], 400);
}

if (!preg_match($passwordRegex, $password)) {
    json_response([
        'success' => false,
        'message' => 'Le mot de passe doit contenir majuscule, minuscule, chiffre et symbole (10+ caractères).',
    ], 400);
}

try {
    $existsStmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $existsStmt->execute([':email' => $email]);
    if ($existsStmt->fetch()) {
        json_response(['success' => false, 'message' => 'Ce compte existe déjà.'], 409);
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $insertStmt = $pdo->prepare(
        "INSERT INTO users (email, password_hash) VALUES (:email, :password_hash)"
    );
    $insertStmt->execute([
        ':email' => $email,
        ':password_hash' => $hash,
    ]);

    rotate_csrf_token();
    json_response([
        'success' => true,
        'message' => 'Compte créé. Vous pouvez vous connecter.',
        'redirect' => 'index.php',
    ]);
} catch (PDOException $e) {
    error_log('Register Error: ' . $e->getMessage());
    json_response(['success' => false, 'message' => 'Erreur serveur.'], 500);
}
