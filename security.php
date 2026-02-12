<?php
declare(strict_types=1);

function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', $isHttps ? '1' : '0');
    ini_set('session.cookie_samesite', 'Strict');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);

    session_start();

    if (empty($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = time();
    }
}

function send_security_headers(): void
{
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), camera=(), microphone=()');
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; form-action 'self'; frame-ancestors 'none'; base-uri 'self';");
}

function get_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token(?string $submittedToken): bool
{
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    return !empty($submittedToken) && !empty($sessionToken) && hash_equals($sessionToken, $submittedToken);
}

function rotate_csrf_token(): void
{
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function require_post_method(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['success' => false, 'message' => 'Méthode non autorisée.'], 405);
    }
}

function json_response(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function get_client_ip(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    if (filter_var($ip, FILTER_VALIDATE_IP)) {
        return $ip;
    }
    return '0.0.0.0';
}

function is_valid_origin(): bool
{
    if (empty($_SERVER['HTTP_ORIGIN'])) {
        return true;
    }

    $originHost = parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_HOST);
    $originPort = parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_PORT);
    $httpHost = $_SERVER['HTTP_HOST'] ?? '';
    $serverHost = explode(':', $httpHost)[0] ?? '';
    $serverPort = isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : null;

    if (empty($originHost) || empty($serverHost)) {
        return false;
    }

    if (!hash_equals($serverHost, $originHost)) {
        return false;
    }

    if ($originPort === null || $serverPort === null) {
        return true;
    }

    return $originPort === $serverPort;
}

function require_authenticated_user(): void
{
    if (empty($_SESSION['authenticated']) || empty($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
    }
}
