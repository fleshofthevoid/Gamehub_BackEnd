<?php
require __DIR__ . "/config.php";
header("Content-Type: application/json; charset=utf-8");

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) { $data = $_POST; }

$username = trim($data["username"] ?? "");
$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

if (!$username || !$email || !$password) {
  http_response_code(400);
  echo json_encode(["error" => "Missing fields"]);
  exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo json_encode(["error" => "Invalid email"]);
  exit;
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE username=? OR email=? LIMIT 1");
$stmt->execute([$username, $email]);
if ($stmt->fetch()) {
  http_response_code(409);
  echo json_encode(["error" => "Username or email exists"]);
  exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?,?,?)")
    ->execute([$username, $email, $hash]);
$uid = (int)$pdo->lastInsertId();

$pdo->prepare("INSERT INTO stats (user_id) VALUES (?)")->execute([$uid]);

echo json_encode(["success" => true, "user" => ["id" => $uid, "username" => $username, "email" => $email]]);
?>