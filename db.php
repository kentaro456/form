<?php
declare(strict_types=1);

$db_host = 'localhost';
$db_name = 'secure_form_db';
$db_user = 'root';
$db_pass = '';

try {
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    error_log('Database Connection Error: ' . $e->getMessage());

    $currentScript = basename($_SERVER['PHP_SELF'] ?? '');
    $jsonScripts = ['process.php', 'register_process.php'];
    if (in_array($currentScript, $jsonScripts, true)) {
        http_response_code(500);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'Erreur interne du serveur.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    die('Erreur interne.');
}
