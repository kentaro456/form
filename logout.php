<?php
require_once 'security.php';
start_secure_session();
send_security_headers();
require_post_method();

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    header('Location: dashboard.php');
    exit;
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

session_destroy();
header('Location: index.php');
exit;
