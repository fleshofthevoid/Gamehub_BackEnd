<?php
require __DIR__ . "/config.php";
header("Content-Type: application/json; charset=utf-8");

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    $data = $_POST; // <-- якщо запит йде через FormData
}

$login = trim($data["username"] ?? $data["login"] ?? "");
$password = $data["password"] ?? "";

if (!$login || !$password) {
    http_response_code(400);
    echo json_encode(["error" => "Missing fields"]);
    exit;
}

$stmt = $pdo->prepare("SELECT id, username, email, password_hash FROM users WHERE username=? OR email=? LIMIT 1");
$stmt->execute([$login, $login]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user["password_hash"])) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid credentials"]);
    exit;
}

echo json_encode([
    "success" => true,
    "user" => [
        "id" => (int)$user["id"],
        "username" => $user["username"],
        "email" => $user["email"]
    ]
]);
?>
