<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

/* === НАЛАШТУВАННЯ БАЗИ ДАНИХ === */
$DB_HOST = '127.0.0.1';
$DB_PORT = '3307';        // <<< твій новий порт !!!
$DB_NAME = 'saper_db';
$DB_USER = 'root';
$DB_PASS = '';            // якщо пароль зʼявиться — вписуєш тут

/* === ПІДКЛЮЧЕННЯ ==== */
try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME;charset=utf8",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error'  => 'DB connection failed',
        'detail' => $e->getMessage()
    ]);
    exit;
}
?>
