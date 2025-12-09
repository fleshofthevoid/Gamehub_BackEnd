<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once "config.php";

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$username = isset($data['username']) ? trim($data['username']) : '';
$password = isset($data['password']) ? trim($data['password']) : '';

if ($username === '' || $password === '') {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "empty_fields"]);
    exit;
}

try {
    // Беремо всі поля, щоб не падати, якщо немає password_hash
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(401);
        echo json_encode(["success" => false, "error" => "user_not_found"]);
        exit;
    }

    // Підтримка і password_hash, і plain-text пароля
    $stored = null;
    if (array_key_exists('password_hash', $user)) {
        $stored = $user['password_hash'];
    } elseif (array_key_exists('password', $user)) {
        $stored = $user['password'];
    }

    $ok = false;
    if ($stored !== null) {
        if (password_verify($password, $stored)) {
            $ok = true;
        } elseif ($password === $stored) {
            // якщо в БД пароль без хеша
            $ok = true;
        }
    }

    if (!$ok) {
        http_response_code(401);
        echo json_encode(["success" => false, "error" => "wrong_password"]);
        exit;
    }

    // Якщо треба сесія – можна включити
    // session_start();
    // $_SESSION['user_id'] = $user['id'];

    echo json_encode([
        "success" => true,
        "user" => [
            "id"       => $user['id'],
            "username" => $user['username']
        ]
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error"   => "server_error",
        "details" => $e->getMessage()
    ]);
}
